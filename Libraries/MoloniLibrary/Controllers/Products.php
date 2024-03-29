<?php

namespace Invoicing\Moloni\Libraries\MoloniLibrary\Controllers;

use Exception;
use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;
use JsonException;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\ResourceModel\Order\Tax\Item;
use Magento\Tax\Api\TaxCalculationInterface;
use Magento\Tax\Model\Config;

class Products
{

    /**
     * @var bool|int
     */
    public $productId = false;

    /**
     * @var bool|int
     */
    public $magentoId = 0;

    /**
     * Holds the default values of a product
     * @var array
     */
    private $defaults = [
        'category_id' => '',
        'type' => '1',
        'name' => 'Artigo Desconhecido',
        'summary' => '',
        'ean' => '',
        'price' => '0',
        'unit_id' => '',
        'has_stock' => '1',
        'stock' => '0',
        'minimum_stock' => '0',
        'pos_favorite' => '0',
        'at_product_category' => 'M',
    ];

    /**
     * @var Moloni
     */
    private $moloni;

    /**
     * @var Tools
     */
    private $tools;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;


    /**
     * @var TaxCalculationInterface
     */
    private $taxHelper;

    /**
     * @var bool
     */
    public $productInserted = false;

    /** @var Item */
    private $taxItem;

    /**
     * @var ProductsFactory
     */
    private $products;

    /**
     * @var OrderInterface
     */
    private $order;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    private static $TAX_CALCULATION_BASED_ON_SHIPPING = 'shipping';
    private static $TAX_CALCULATION_BASED_ON_BILLING = 'billing';
    private static $TAX_CALCULATION_BASED_ON_ORIGIN = 'origin';

    public function __construct(
        ProductRepositoryInterface $productRepository,
        CategoryCollectionFactory  $categoryCollectionFactory,
        TaxCalculationInterface    $taxHelper,
        Item                       $taxItem,
        Moloni                     $moloni,
        Tools                      $tools,
        ProductsFactory            $products,
        ScopeConfigInterface       $scopeConfig
    )
    {
        $this->productRepository = $productRepository;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->taxHelper = $taxHelper;
        $this->taxItem = $taxItem;
        $this->moloni = $moloni;
        $this->tools = $tools;
        $this->products = $products;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param OrderItemInterface $orderProduct
     * @param OrderInterface $order
     *
     * @return array
     * @throws JsonException
     */
    public function setProductFromOrder(OrderItemInterface $orderProduct, OrderInterface $order): array
    {
        $this->order = $order;

        $productId = $orderProduct->getProductId();

        $product = [];
        $product['name'] = $orderProduct->getName();

        $product['reference'] = mb_substr($orderProduct->getSku(), 0, 30);

        $product['price'] = $orderProduct->getBasePrice();
        $product['price_with_taxes'] = $orderProduct->getPriceInclTax();
        $product['qty'] = $orderProduct->getQtyOrdered();

        $discountPercent = $orderProduct->getDiscountPercent();

        $baseDiscountAmount = $orderProduct->getBaseDiscountAmount();
        if ((float)$discountPercent === (float)0 && $baseDiscountAmount > 0) {
            $discountPercent = $baseDiscountAmount * 100 / $orderProduct->getRowTotal();
        }

        if ($discountPercent > 0 && $discountPercent < 100) {
            $product['discount'] = $discountPercent;
        }

        if (!empty($orderProduct->getDescription())) {
            $product['summary'] = $orderProduct->getDescription();
        }

        /** @var $childProducts \Magento\Sales\Model\Order\Item[] */
        $childProducts = $orderProduct->getChildrenItems();

        if ($orderProduct->getProductType() === 'bundle' && !empty($childProducts) && is_array($childProducts)) {
            $product['composition_type'] = 1;
            $product['child_products'] = [];
            foreach ($childProducts as $child) {
                if ($child->getProductId() !== $productId) {
                    $product['child_products'][] = $this->products->create()->setProductFromOrder($child, $order);
                }
            }
        }

        $taxRate = $orderProduct->getTaxPercent();
        if ($taxRate > 0) {
            $product['taxes'][] = [
                'tax_id' => $this->getTaxIdFromRate($taxRate),
                'value' => $taxRate,
                'order' => 0,
                'cumulative' => true
            ];
        } elseif (!isset($product['composition_type']) || $product['composition_type'] !== 1) {
            $product['exemption_reason'] = $this->moloni->settings['products_tax_exemption'];
        }

        // Check if the product does exist
        $moloniProduct = $this->moloni->products->getByReference([
            'reference' => $product['reference'],
            'exact' => true
        ]);

        if ($moloniProduct && isset($moloniProduct[0]['product_id'])) {
            $product['product_id'] = $moloniProduct[0]['product_id'];
        } else {
            $product['product_id'] = $this->createProductFromId($productId, $orderProduct);
        }

        return $product;
    }

    /**
     * @param OrderInterface $order
     * @return array
     * @throws JsonException
     */
    public function setShippingFromOrder(OrderInterface $order): array
    {
        $this->order = $order;

        $product = [];
        $product['name'] = $this->order->getShippingDescription();
        $product['reference'] = 'Portes';
        $product['price'] = $this->order->getBaseShippingAmount();
        $product['price_with_taxes'] = $this->order->getShippingInclTax();
        $product['qty'] = 1;

        // @todo calc shipping discount percentage

        $discountPercent = $this->order->getShippingDiscountAmount();
        if ($discountPercent > 0 && $discountPercent < 100) {
            $product['discount'] = $discountPercent;
        }

        // Search for shipping tax
        $taxRate = 0;
        $orderTaxes = $this->taxItem->getTaxItemsByOrderId($this->order->getId());
        foreach ($orderTaxes as $orderTax) {
            if ($orderTax['taxable_item_type'] === 'shipping') {
                $taxRate = $orderTax['tax_percent'];
            }
        }

        if ($taxRate > 0) {
            $product['taxes'][] = [
                'tax_id' => $this->getTaxIdFromRate($taxRate),
                'value' => $taxRate,
                'order' => 0,
                'cumulative' => true
            ];
        } else {
            $product['exemption_reason'] = $this->moloni->settings['shipping_tax_exemption'];
        }

        // Check if the product does exist
        $moloniProduct = $this->moloni->products->getByReference([
            'reference' => $product['reference'],
            'exact' => true
        ]);

        if ($this->moloni->settings['shipping_tax'] > 0) {
            $this->parseProductTaxes($product, $this->moloni->settings['shipping_tax']);
        }

        if ($moloniProduct && isset($moloniProduct[0]['product_id'])) {
            $product['product_id'] = $moloniProduct[0]['product_id'];
        } else {
            $product['product_id'] = $this->createShippingFromOrder();
        }

        return $product;
    }


    /**
     * @param int $productId
     * @return int|bool
     */
    public function syncProductFromId(int $productId)
    {
        try {
            $product = $this->productRepository->getById($productId);
            $sku = mb_substr($product->getSku(), 0, 30);

            $moloniProduct = $this->moloni->products->getByReference(['reference' => $sku]);
            if (!$moloniProduct || !$moloniProduct[0]) {
                $productId = $this->createProductFromId($productId);
                if ($productId > 0) {
                    return $productId;
                }
            } else {
                return $this->syncProductFromMoloni($moloniProduct[0]);
            }
        } catch (Exception $e) {
            $this->moloni->errors->throwError($e->getMessage(), $e->getMessage(), __FUNCTION__);
        }

        return false;
    }

    /**
     * @param $moloniProduct
     * @return bool
     */
    public function syncProductFromMoloni($moloniProduct): ?bool
    {
        if (!isset($moloniProduct['reference'])) {
            return false;
        }

        try {
            $product = $this->productRepository->get($moloniProduct['reference']);

            if ($this->moloni->settings['products_sync_price']) {
                $product->setPrice($moloniProduct['price']);
            }

            if ($this->moloni->settings['products_sync_stock']) {
                try {
                    $product->getExtensionAttributes()->getStockItem()->setQty($moloniProduct['stock']);
                    $product->getExtensionAttributes()->getStockItem()->setIsInStock(true);
                } catch (Exception $exception) {
                    return false;
                }
            }

            $product->save();
            return $moloniProduct['product_id'];
        } catch (NoSuchEntityException $exception) {
            return false;
        }
    }

    /**
     * @param int|ProductInterface $productId
     * @param bool|OrderItemInterface $orderProduct
     * @return int
     */
    private function createProductFromId($productId, $orderProduct = false): int
    {
        try {
            if ($productId instanceof ProductInterface) {
                $product = $productId;
            } else {
                $product = $this->productRepository->getById($productId);
            }


            $categories = $product->getCategoryIds();
            $categoryTree = $this->getCategoryTree($categories);

            if ($orderProduct) {
                $moloniProduct['name'] = $orderProduct->getName();
                $moloniProduct['reference'] = mb_substr($orderProduct->getSku(), 0, 30);

                if ($orderProduct->getPrice() > 0) {
                    $moloniProduct['price'] = $orderProduct->getPrice();
                }

                if (!empty($orderProduct->getDescription())) {
                    $moloniProduct['summary'] = $orderProduct->getDescription();
                }
            } else {
                $moloniProduct['name'] = $product->getName();
                $moloniProduct['reference'] = mb_substr($product->getSku(), 0, 30);

                if ($product->getPrice() > 0) {
                    $moloniProduct['price'] = $product->getPrice();
                }

                if (!empty($product->getDescription())) {
                    $moloniProduct['summary'] = $product->getDescription();
                }
            }

            $productStock = $product->getExtensionAttributes()->getStockItem()->getQty();
            $moloniProduct['stock'] = $productStock;
            $moloniProduct['category_id'] = $this->createCategoryTree($categoryTree);

            if (!empty($this->moloni->settings['products_at_category'])) {
                $moloniProduct['at_product_category'] = $this->moloni->settings['products_at_category'];
            }

            if (!empty($this->moloni->settings['default_measurement_unit_id'])) {
                $moloniProduct['unit_id'] = $this->moloni->settings['default_measurement_unit_id'];
            }

            $taxClassId = $product->getTaxClassId();
            $defaultTaxRate = $this->taxHelper->getCalculatedRate($taxClassId);

            try {
                $typeInstance = $product->getTypeInstance();
                $productOptions = $typeInstance->getChildrenIds($product->getId(), false);
                if (!empty($productOptions) && is_array($productOptions)) {
                    $moloniProduct['composition_type'] = 1;
                    $moloniProduct['child_products'] = [];

                    foreach ($productOptions as $requiredChildrenIds) {
                        foreach ($requiredChildrenIds as $childrenId) {
                            $childProduct = $this->productRepository->getById($childrenId);
                            $childProductReference = mb_substr($childProduct->getSku(), 0, 30);

                            $moloniProductExists = $this->moloni->products->getByReference([
                                'reference' => $childProductReference,
                                'exact' => true
                            ]);

                            $moloniChildProductId = $moloniProductExists[0]['product_id'] ?? $this->products->create()->createProductFromId($childProduct);

                            /** @todo Validar a quantidade por defeito de cada artigo */
                            $moloniProduct['child_products'][] = [
                                'product_child_id' => $moloniChildProductId,
                                'qty' => 1,
                                'price' => $childProduct->getPrice()
                            ];
                        }
                    }
                }
            } catch (Exception $e) {
            }

            if ($this->moloni->settings['products_tax'] > 0) {
                $this->parseProductTaxes($moloniProduct, $this->moloni->settings['products_tax']);
            } elseif ($defaultTaxRate > 0) {
                $moloniProduct['taxes'][] = [
                    'tax_id' => $this->getTaxIdFromRate($defaultTaxRate),
                    'value' => $defaultTaxRate,
                    'order' => 0,
                    'cumulative' => true
                ];
            } else {
                $moloniProduct['exemption_reason'] = $this->moloni->settings['products_tax_exemption'];
            }


            $moloniProduct = array_merge($this->defaults, $moloniProduct);

            $insertedProduct = $this->moloni->products->insert($moloniProduct);

            $this->productInserted = true;

            return $insertedProduct['product_id'] ?: 0;
        } catch (Exception $e) {
            $this->moloni->errors->throwError($e->getMessage(), $e->getMessage(), __FUNCTION__);
        }

        return 0;
    }

    /**
     * @return int
     */
    private function createShippingFromOrder(): int
    {
        try {
            $moloniProduct['name'] = $this->order->getShippingDescription();
            $moloniProduct['reference'] = "Portes";
            $moloniProduct['type'] = 2;
            $moloniProduct['has_stock'] = 0;
            $moloniProduct['category_id'] = $this->createCategoryTree([['name' => 'Portes']]);
            $moloniProduct['price'] = $this->order->getBaseShippingAmount();
            $moloniProduct['price_with_taxes'] = $this->order->getShippingInclTax();

            if (!empty($this->moloni->settings['default_measurement_unit_id'])) {
                $moloniProduct['unit_id'] = $this->moloni->settings['default_measurement_unit_id'];
            }

            // Search for shipping tax
            $taxRate = 0;
            $orderTaxes = $this->taxItem->getTaxItemsByOrderId($this->order->getId());
            foreach ($orderTaxes as $orderTax) {
                if ($orderTax['taxable_item_type'] === 'shipping') {
                    $taxRate = $orderTax['tax_percent'];
                }
            }

            if ($taxRate > 0) {
                $moloniProduct['taxes'][] = [
                    'tax_id' => $this->getTaxIdFromRate($taxRate),
                    'value' => $taxRate,
                    'order' => 0,
                    'cumulative' => true
                ];
            } else {
                $moloniProduct['exemption_reason'] = $this->moloni->settings['shipping_tax_exemption'];
            }

            if ($this->moloni->settings['shipping_tax'] > 0) {
                $this->parseProductTaxes($moloniProduct, $this->moloni->settings['shipping_tax']);
            }

            $moloniProduct = array_merge($this->defaults, $moloniProduct);
            $insertedProduct = $this->moloni->products->insert($moloniProduct);

            if (isset($insertedProduct['product_id'])) {
                $this->productInserted = true;
                return $insertedProduct['product_id'];
            }
        } catch (Exception $e) {
            $this->moloni->errors->throwError($e->getMessage(), $e->getMessage(), __FUNCTION__);
        }

        return 0;
    }

    /**
     * @param $categoryTree
     * @param int $parentId
     * @return bool|int
     * @throws JsonException
     */
    private function createCategoryTree($categoryTree, int $parentId = 0)
    {
        if (!empty($categoryTree) && is_array($categoryTree)) {
            $categoryId = false;

            foreach ($categoryTree as $category) {
                $moloniCategories = $this->moloni->productsCategories->getAll(['parent_id' => $parentId]);
                if ($moloniCategories && is_array($moloniCategories)) {
                    foreach ($moloniCategories as $moloniCategory) {
                        if (strcasecmp($moloniCategory['name'], $category['name']) === 0) {
                            $categoryId = $moloniCategory['category_id'];
                            break;
                        }
                    }
                }

                if (!$categoryId) {
                    $categoryInsert = $this->moloni->productsCategories->insert([
                        'parent_id' => $parentId,
                        'name' => $category['name']
                    ]);

                    if ($categoryInsert) {
                        $categoryId = $categoryInsert['category_id'];
                    }
                }

                if (isset($category['child'])) {
                    $categoryId = $this->createCategoryTree($category['child'], $categoryId);
                }
                break;
            }

            return $categoryId;
        }

        return $this->createCategoryTree([['name' => 'Loja Online']]);
    }

    /**
     * @param $categories
     * @return array
     */
    private function getCategoryTree($categories): array
    {

        try {
            $matchingNamesCollection = $this->categoryCollectionFactory->create();

            $matchingNamesCollection->addAttributeToSelect('path')
                ->addAttributeToFilter('entity_id', ['neq' => CategoryModel::TREE_ROOT_ID])
                ->addAttributeToFilter('entity_id', ['in' => $categories]);

            $shownCategoriesIds = [];

            /** @var CategoryModel $category */
            foreach ($matchingNamesCollection as $category) {
                foreach (explode('/', $category->getPath()) as $parentId) {
                    $shownCategoriesIds[$parentId] = 1;
                }
            }

            $collection = $this->categoryCollectionFactory->create();

            $collection->addAttributeToFilter('entity_id', ['in' => array_keys($shownCategoriesIds)])
                ->addAttributeToSelect(['name', 'is_active', 'parent_id']);

            $categoryById = [
                CategoryModel::TREE_ROOT_ID => [
                    'value' => CategoryModel::TREE_ROOT_ID
                ],
            ];

            foreach ($collection as $category) {
                foreach ([$category->getId(), $category->getParentId()] as $categoryId) {
                    if (!isset($categoryById[$categoryId])) {
                        $categoryById[$categoryId] = ['value' => $categoryId];
                    }
                }

                $categoryById[$category->getId()]['name'] = $category->getName();
                $categoryById[$category->getParentId()]['child'][] = &$categoryById[$category->getId()];
            }

            $tree = $categoryById[CategoryModel::TREE_ROOT_ID]['child'];
        } catch (Exception $e) {
            return [["name" => 'Magento']];
        }

        return $tree;
    }

    /**
     * @param float $taxRate
     * @param bool $break
     * @return int
     * @throws JsonException
     */
    private function getTaxIdFromRate(float $taxRate, bool $break = false): int
    {
        $taxId = 0;
        $taxDefaultId = 0;
        $taxes = $this->moloni->taxes->getAll();

        $taxFiscalZone = 'PT';
        $taxesBasedOn = $this->scopeConfig->getValue(
            Config::CONFIG_XML_PATH_BASED_ON
        );

        if ($this->order instanceof OrderInterface) {
            switch ($taxesBasedOn) {
                case self::$TAX_CALCULATION_BASED_ON_SHIPPING:
                    $shippingAddress = $this->order->getShippingAddress();
                    if ($shippingAddress) {
                        $countryCode = $shippingAddress->getCountryId();
                        $country = $this->tools->getContryByISO($countryCode);
                    }
                    break;

                case self::$TAX_CALCULATION_BASED_ON_BILLING:
                    $billingAddress = $this->order->getBillingAddress();
                    if ($billingAddress) {
                        $countryCode = $billingAddress->getCountryId();
                        $country = $this->tools->getContryByISO($countryCode);
                    }
                    break;


                case self::$TAX_CALCULATION_BASED_ON_ORIGIN:
                default:
                    $company = $this->moloni->companies->getOne();
                    $country = $this->tools->getCountryById((int)$company['country_id']);
                    break;
            }
        } else {
            $company = $this->moloni->companies->getOne();
            $country = $this->tools->getCountryById((int)$company['country_id']);
        }

        if (isset($country) && $country) {
            $taxFiscalZone = strtoupper($country['iso_3166_1']);
        }

        if ($taxes && is_array($taxes)) {
            foreach ($taxes as $tax) {
                if ((int)$tax['active_by_default'] === 1) {
                    $taxDefaultId = $tax['tax_id'];
                }

                if ((float)$tax['value'] === (float)$taxRate && $tax['fiscal_zone'] === $taxFiscalZone) {
                    $taxId = $tax['tax_id'];

                    if ($tax['name'] === 'IVA Normal') {
                        return $taxId;
                    }
                }
            }
        }

        if ((int)$taxId === 0) {
            $taxId = $taxDefaultId;
        }

        if ((int)$taxId === 0 && !$break) {
            $taxId = $this->getTaxIdFromRate(23, true);
        }

        return $taxId;
    }

    /**
     * @param array $moloniProduct
     * @param int $taxId
     * @throws JsonException
     */
    private function parseProductTaxes(array &$moloniProduct, int $taxId = 0): void
    {
        if (!empty($moloniProduct) && $taxId > 0) {
            $price = (isset($moloniProduct['price_with_taxes']) && (float)$moloniProduct['price_with_taxes'] > 0)
                ? $moloniProduct['price_with_taxes'] : $moloniProduct['price'];

            $tax = 0;
            $moloniTaxes = $this->moloni->taxes->getAll();
            if (is_array($moloniTaxes)) {
                foreach ($moloniTaxes as $moloniTax) {
                    if ($moloniTax['tax_id'] == $taxId) {
                        $tax = $moloniTax;
                    }
                }
            }

            // Percentage
            if ($tax && (int)$tax['type'] === 1) {
                $tax['order'] = 0;
                $tax['cumulative'] = 0;
                if (!empty($tax['exemption_reason'])) {
                    unset($moloniProduct['taxes']);
                    $moloniProduct['exemption_reason'] = $tax['exemption_reason'];
                    $moloniProduct['price'] = $price;
                } else {
                    unset($moloniProduct['exemption_reason']);
                    $moloniProduct['price'] = $price * 100 / (100 + $tax['value']);
                    $moloniProduct['taxes'][0] = $tax;
                }
            }
        }
    }
}
