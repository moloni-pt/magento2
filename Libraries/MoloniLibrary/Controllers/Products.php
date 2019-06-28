<?php

namespace Invoicing\Moloni\Libraries\MoloniLibrary\Controllers;

use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;
use Magento\Sales\Api\Data\OrderInterface;

class Products
{

    public $customerId = false;
    public $alternateAddressId = false;

    /**
     * Holds the default values of a product
     * @var array
     */
    private $defaults = [
        'category_id' => '',
        'type' => '1',
        'name' => 'Artigo Exemplo',
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
    private $product = [];

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

    public function __construct(
        Moloni $moloni,
        Tools $tools
    )
    {
        $this->moloni = $moloni;
        $this->tools = $tools;
    }


    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return array
     */
    public function getProductsFromOrder(OrderInterface $order)
    {
        $orderProducts = [];

        $products = $order->getItems();
        foreach ($products as $product) {
            $parseProduct = $this->parseOrderProduct($product);
        }

        return $orderProducts;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderItemInterface $orderProduct
     * @return array
     */
    private function parseOrderProduct($orderProduct)
    {
        echo "<pre>";
        echo "Teste";

        $product = [];
        $product['name'] = $orderProduct->getName();
        $product['summary'] = $orderProduct->getDescription();
        $product['reference'] = $orderProduct->getSku();

        //$productCalcs = $this->calcProductPriceAndTaxes($orderProduct->getBasePrice());


        print_r($this->product);
        return [];
    }

    private function parseProduct()
    {


        return true;
    }

    private function handle()
    {
        return true;
    }


    /**
     * @param $product
     * @param $price
     * @return bool
     */
    private function calcProductPriceAndTaxes($price)
    {


        return true;
    }

}
