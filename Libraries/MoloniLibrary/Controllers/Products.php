<?php

namespace Invoicing\Moloni\Libraries\MoloniLibrary\Controllers;

use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use \Magento\Catalog\Block\Adminhtml\Category\Tree;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Tax\Api\TaxCalculationInterface;

class Products
{

    public $productId = false;
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
     * Holds the values to be inserted/update
     * @var array
     */
    private $orderProduct = [];

    /**
     * Hold an existing Moloni Product
     * @var array
     */
    private $moloniProduct = [];

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

    public function __construct(
        ProductRepositoryInterface $productRepository,
        CategoryCollectionFactory $categoryCollectionFactory,
        TaxCalculationInterface $taxHelper,
        Moloni $moloni,
        Tools $tools
    )
    {
        $this->productRepository = $productRepository;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->taxHelper = $taxHelper;
        $this->moloni = $moloni;
        $this->tools = $tools;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderItemInterface $orderProduct
     * @return array
     */
    public function setProductFromOrder($orderProduct)
    {
        $productId = $orderProduct->getProductId();

        $product = [];
        $product['name'] = $orderProduct->getName();
        $product['reference'] = $orderProduct->getSku();
        $product['price'] = $orderProduct->getBasePrice();
        $product['price_with_taxes'] = $orderProduct->getPriceInclTax();
        $product['qty'] = $orderProduct->getQtyOrdered();

        if ($orderProduct->getDiscountPercent() > 0 && $orderProduct->getDiscountPercent() < 100) {
            $product['discount'] = $orderProduct->getDiscountPercent();
        }

        if (!empty($orderProduct->getDescription())) {
            $product['summary'] = $orderProduct->getDescription();
        }


        /** @var $childProducts \Magento\Sales\Model\Order\Item[] */
        $childProducts = $orderProduct->getChildrenItems();
        if ($childProducts && is_array($childProducts)) {
            foreach ($childProducts as $childProduct) {
                $product['name'] = $childProduct->getName();
                $product['reference'] = $childProduct->getSku();
                $productId = $childProduct->getProductId();
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
        } else {
            $product['exemption_reason'] = $this->moloni->settings['products_tax_exemption'];
        }

        // Check if the product does exist
        $moloniProduct = $this->moloni->products->getByReference([
            'reference' => $orderProduct->getSku(),
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
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return array
     */
    public function setShippingFromOrder($order)
    {

        $product = [];
        $product['name'] = $order->getShippingDescription();
        $product['reference'] = 'Portes';
        $product['price'] = $order->getBaseShippingAmount();
        $product['price_with_taxes'] = $order->getShippingInclTax();
        $product['qty'] = 1;

        // @todo calc shipping discount percentage
        if ($order->getShippingDiscountAmount() > 0 && $order->getShippingDiscountAmount() < 100) {
            $product['discount'] = $order->getShippingDiscountAmount();
        }

        $taxRate = 0;
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

        if ($moloniProduct && isset($moloniProduct[0]['product_id'])) {
            $product['product_id'] = $moloniProduct[0]['product_id'];
        } else {
            $product['product_id'] = $this->createShipping($order);
        }

        return $product;
    }

    /**
     * @param $productId
     * @return int
     */
    private function createProductFromId($productId, $orderProduct = false)
    {
        try {
            $product = $this->productRepository->getById($productId);

            $categories = $product->getCategoryIds();
            $categoryTree = $this->getCategoryTree($categories);


            if ($orderProduct) {
                $moloniProduct['name'] = $orderProduct->getName();
                $moloniProduct['reference'] = $orderProduct->getSku();

                if ($orderProduct->getPrice() > 0) {
                    $moloniProduct['price'] = $orderProduct->getPrice();
                }

                if (!empty($orderProduct->getDescription())) {
                    $moloniProduct['summary'] = $orderProduct->getDescription();
                }
            } else {
                $moloniProduct['name'] = $product->getName();
                $moloniProduct['reference'] = $product->getSku();

                if ($product->getPrice() > 0) {
                    $moloniProduct['price'] = $product->getPrice();
                }

                if (!empty($product->getDescription())) {
                    $moloniProduct['summary'] = $product->getDescription();
                }
            }

            $moloniProduct['stock'] = $product->getExtensionAttributes()->getStockItem()->getQty();
            $moloniProduct['category_id'] = $this->createCategoryTree($categoryTree);


            if (!empty($this->moloni->settings['products_at_category'])) {
                $moloniProduct['at_product_category'] = $this->moloni->settings['products_at_category'];
            }

            if (!empty($this->moloni->settings['default_measurement_unit_id'])) {
                $moloniProduct['unit_id'] = $this->moloni->settings['default_measurement_unit_id'];
            }

            $taxClassId = $product->getTaxClassId();
            $defaultTaxRate = $this->taxHelper->getCalculatedRate($taxClassId);

            if ($defaultTaxRate > 0) {
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

            return $insertedProduct['product_id'];

        } catch (\Exception $e) {
            $this->moloni->errors->throwError($e->getCode(), $e->getMessage(), __FUNCTION__);
        }

        return 0;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return int
     */
    private function createShipping($order)
    {
        try {
            $moloniProduct['name'] = $order->getShippingDescription();
            $moloniProduct['reference'] = "Portes";
            $moloniProduct['type'] = 2;
            $moloniProduct['has_stock'] = 0;
            $moloniProduct['category_id'] = $this->createCategoryTree([['name' => 'Portes']]);
            $moloniProduct['price'] = $order->getBaseShippingAmount();

            if (!empty($this->moloni->settings['default_measurement_unit_id'])) {
                $moloniProduct['unit_id'] = $this->moloni->settings['default_measurement_unit_id'];
            }

            $defaultTaxRate = 23;

            if ($defaultTaxRate > 0) {
                $moloniProduct['taxes'][] = [
                    'tax_id' => $this->getTaxIdFromRate($defaultTaxRate),
                    'value' => $defaultTaxRate,
                    'order' => 0,
                    'cumulative' => true
                ];
            } else {
                $moloniProduct['exemption_reason'] = $this->moloni->settings['shipping_tax_exemption'];
            }

            $moloniProduct = array_merge($this->defaults, $moloniProduct);
            $insertedProduct = $this->moloni->products->insert($moloniProduct);
            return $insertedProduct['product_id'];

        } catch (\Exception $e) {
            $this->moloni->errors->throwError($e->getCode(), $e->getMessage(), __FUNCTION__);
        }

        return 0;
    }

    private function createCategoryTree($categoryTree, $parentId = 0)
    {
        if (!empty($categoryTree) && is_array($categoryTree)) {
            $categoryId = false;

            foreach ($categoryTree as $category) {
                $moloniCategories = $this->moloni->productsCategories->getAll(['parent_id' => $parentId]);
                if ($moloniCategories && is_array($moloniCategories)) {
                    foreach ($moloniCategories as $moloniCategory) {
                        if (strcasecmp($moloniCategory['name'], $category['name']) == 0) {
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
    private function getCategoryTree($categories)
    {

        try {
            /* @var $matchingNamesCollection \Magento\Catalog\Model\ResourceModel\Category\Collection */
            $matchingNamesCollection = $this->categoryCollectionFactory->create();

            $matchingNamesCollection->addAttributeToSelect('path')
                ->addAttributeToFilter('entity_id', ['neq' => CategoryModel::TREE_ROOT_ID])
                ->addAttributeToFilter('entity_id', ['in' => $categories]);

            $shownCategoriesIds = [];

            /** @var \Magento\Catalog\Model\Category $category */
            foreach ($matchingNamesCollection as $category) {
                foreach (explode('/', $category->getPath()) as $parentId) {
                    $shownCategoriesIds[$parentId] = 1;
                }
            }

            /* @var $collection \Magento\Catalog\Model\ResourceModel\Category\Collection */
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
        } catch (\Exception $e) {
            return [["name" => 'Magento']];
        }

        return $tree;
    }

    /**
     * @param float $taxRate
     * @param bool $break
     * @return int
     */
    private function getTaxIdFromRate($taxRate, $break = false)
    {
        $taxId = 0;
        $taxes = $this->moloni->taxes->getAll();
        if ($taxes && is_array($taxes)) {
            foreach ($taxes as $tax) {
                if ($tax['value'] == $taxRate) {
                    $taxId = $tax['tax_id'];
                    if ($tax['name'] == 'IVA Normal') {
                        return $taxId;
                    }
                }
            }
        }

        if ($taxId == 0 && !$break) {
            $taxId = $this->getTaxIdFromRate(23, true);
        }

        return $taxId;
    }

}