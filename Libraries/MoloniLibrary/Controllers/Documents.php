<?php
/* Moloni
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Invoicing\Moloni\Libraries\MoloniLibrary\Controllers;

use \Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class Documents
{

    private $moloni;
    private $orderRepository;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface Order interface
     */
    private $order = [];

    /**
     * @var array
     * Document array to be inserted
     */
    private $document = [];

    /**
     * @var array
     * Array from companies/getOne endpoint
     */
    private $company = [];

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public $date;
    public $expirationDate;

    /**
     * @var CurrencyFactory
     */
    private $currencyFactory;


    /**
     * Companies constructor.
     * @param Moloni $moloni
     * @param StoreManagerInterface $storeManager
     * @param OrderRepositoryInterface $orderRepository
     * @param CurrencyFactory $currencyFactory
     */
    public function __construct(
        Moloni $moloni,
        StoreManagerInterface $storeManager,
        OrderRepositoryInterface $orderRepository,
        CurrencyFactory $currencyFactory
    )
    {
        $this->moloni = $moloni;
        $this->storeManager = $storeManager;
        $this->orderRepository = $orderRepository;
        $this->currencyFactory = $currencyFactory;
    }

    /**
     * This function sets $this->order with an object of the order
     * Then createDocument() is called to create the document
     * @param int $orderId
     * Id of a Magento order
     * @return array
     * Return array with [valid => 1] or with errors
     */
    public function createDocumentFromOrderId($orderId)
    {
        $this->order = $this->orderRepository->get($orderId);
        $this->createDocument();

        echo "<pre>";
        print_r($this->document);
        print_r($this->company);
        exit;
        return [];
    }

    /**
     * Create a document based on $this->order
     */
    private function createDocument()
    {
        $this->company = $this->moloni->companies->getOne();

        $this->document['date'] = gmdate('Y-m-d');
        $this->document['expiration_date'] = gmdate('Y-m-d');
        $this->document['document_set_id'] = $this->moloni->settings['document_set_id'];
        $this->document['your_reference'] = $this->order->getIncrementId();

        $this->parseCurrency();

        if ($this->moloni->settings['shipping_details']) {
            $this->parseShippingDetails();
        }


    }

    private function parseCurrency()
    {
        $orderCurrencyCode = $this->order->getOrderCurrencyCode();

        // @todo decide what to do when a company is not portuguese
        if ($this->company['country_id'] == 1 && $orderCurrencyCode !== 'EUR') {
            $rate = $this->currencyFactory->create()->load($orderCurrencyCode)->getAnyRate("EUR");
            $this->document['exchange_currency_id'] = 1; // EUR
            $this->document['exchange_rate'] = $rate;
        }
    }

    private function parseShippingDetails()
    {
        $shippingDescription = $this->order->getShippingDescription();
        if (!empty($shippingDescription)) {
            // @todo Take care of the shipping_method_id
        }

        $this->document['delivery_datetime'] = gmdate('Y-m-d H:i:s');
    }
}
