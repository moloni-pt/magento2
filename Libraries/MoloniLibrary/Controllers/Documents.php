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
use Magento\Framework\Message\ManagerInterface;

class Documents
{

    /**
     * @var Moloni
     * Moloni library with tokens and settings
     */
    private $moloni;

    /**
     * @var Tools
     * Tools for validation and creating valid info
     */
    private $tools;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface Order interface
     */
    public $order = [];

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

    /**
     * @var CurrencyFactory
     */
    private $currencyFactory;

    /**
     * @var Customers
     */
    private $customers;

    /**
     * @var Products
     */
    private $products;

    /**
     * @var ManagerInterface
     */
    private $messageManager;


    /**
     * Companies constructor.
     * @param Moloni $moloni
     * @param Tools $tools
     * @param Customers $customers
     * @param ProductsFactory $products
     * @param StoreManagerInterface $storeManager
     * @param OrderRepositoryInterface $orderRepository
     * @param CurrencyFactory $currencyFactory
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Moloni $moloni,
        Tools $tools,
        Customers $customers,
        ProductsFactory $products,
        StoreManagerInterface $storeManager,
        OrderRepositoryInterface $orderRepository,
        CurrencyFactory $currencyFactory,
        ManagerInterface $messageManager
    )
    {
        $this->moloni = $moloni;
        $this->tools = $tools;
        $this->customers = $customers;
        $this->products = $products;
        $this->storeManager = $storeManager;
        $this->orderRepository = $orderRepository;
        $this->currencyFactory = $currencyFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * This function sets $this->order with an object of the order
     * Then createDocument() is called to create the document
     * @param int $orderId
     * Id of a Magento order
     * @return array|boolean
     * Return array with [valid => 1] or with errors
     */
    public function createDocumentFromOrderId($orderId)
    {
        $this->order = $this->orderRepository->get($orderId);
        $this->parseDocument();

        if ($this->moloni->settings['shipping_document'] == 1) {
            $shippingDocument = $this->createShippingDocument();
            if (!$shippingDocument) {
                return false;
            }
        }

        $insertDraft = $this->moloni->documents->setDocumentType()->insert($this->document);
        if (!$insertDraft) {
            return false;
        }

        $validDocument = $this->validateDocument($insertDraft['document_id'], $this->order);
        if (!$validDocument) {
            $this->moloni->errors->throwError(
                __($this->order->getIncrementId() . " - Documento inserido mas os totais não batem certo"),
                __($this->order->getIncrementId() . " - Documento inserido mas os totais não batem certo."),
                __CLASS__ . "/" . __FUNCTION__
            );
            return false;
        }

        if ($this->moloni->settings['document_status'] == 1 && $validDocument) {
            $validDocument = $this->moloni->documents->update([
                'document_id' => $insertDraft['document_id'],
                'status' => 1
            ]);
        }

        return $validDocument;
    }

    /**
     * @return bool|array
     */
    private function createShippingDocument()
    {
        // Add delivery datetime because its required
        $this->document['delivery_datetime'] = gmdate('Y-m-d H:i:s');
        $shippingDocument = $this->moloni->documents->setDocumentType('billsOfLading')->insert($this->document);
        if (!$shippingDocument) {
            return false;
        }

        $validDocument = $this->validateDocument($shippingDocument['document_id'], $this->order);
        if (!$validDocument) {
            $this->moloni->errors->throwError(
                __($this->order->getIncrementId() . " - Documento de transporte inserido mas os totais não batem certo"),
                __($this->order->getIncrementId() . " - Documento de transporte inserido mas os totais não batem certo."),
                __CLASS__ . "/" . __FUNCTION__
            );
            return false;
        }

        $closeDocument = $this->moloni->documents->update([
            'document_id' => $shippingDocument['document_id'],
            'status' => 1
        ]);

        if (!$closeDocument) {
            return false;
        }

        $this->document['associated_documents'][] = [
            "associated_id" => $validDocument['document_id'],
            "value" => $validDocument['net_value']
        ];

        return true;
    }

    /**
     * Populates $this->>document based on $this->order
     * @return bool
     */
    private function parseDocument()
    {
        $this->company = $this->moloni->companies->getOne();

        $customer = $this->customers->setCustomerFromOrder($this->order);
        $this->document['customer_id'] = $customer->customerId;

        $this->document['date'] = gmdate('Y-m-d');
        $this->document['expiration_date'] = gmdate('Y-m-d');
        $this->document['document_set_id'] = $this->moloni->settings['document_set_id'];
        $this->document['your_reference'] = $this->order->getIncrementId();

        $this->parseProducts();

        $this->parseCurrency();
        $this->parsePaymentMethods();

        if ($this->moloni->settings['shipping_details']) {
            $this->parseShippingDetails();
        }

        return true;
    }

    private function parseProducts()
    {
        $products = $this->order->getItems();
        if (is_array($products)) {
            foreach ($products as $key => $product) {
                if ($product->getParentItem()) {
                    continue;
                }

                // Skip the child products of an oder
                $documentProduct = $this->products->create()->setProductFromOrder($product);
                if ($documentProduct && is_array($documentProduct)) {
                    $this->document['products'][] = $documentProduct;
                }

            }
        }

        if ($this->order->getShippingAmount() > 0) {
            $this->document['products'][] = $this->products->create()->setShippingFromOrder($this->order);
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

    /**
     * Set the document Shipping details
     */
    private function parseShippingDetails()
    {
        $shippingDescription = $this->order->getShippingDescription();
        if (!empty($shippingDescription)) {
            $deliveryMethodId = $this->handleDeliveryMethod($shippingDescription);
            if ($deliveryMethodId) {
                $this->document['delivery_method_id'] = $deliveryMethodId;
            }
        }

        $this->document['delivery_datetime'] = gmdate('Y-m-d H:i:s');

        $this->parseShippingDepartureAddress();
        $this->parseShippingDestinationAddress();
    }

    private function parsePaymentMethods()
    {
        $orderPayment = $this->order->getPayment();
        if ($orderPayment && (float)$orderPayment->getAmountPaid() > 0) {
            $paymentName = $orderPayment->getMethodInstance()->getTitle();
            $paymentMethodId = $this->handlePaymentMethod($paymentName);

            if ($paymentMethodId) {
                $this->document['payments'][] = [
                    'payment_method_id' => $paymentMethodId,
                    'value' => $orderPayment->getAmountPaid(),
                    'date' => gmdate('Y-m-d H:i:s')
                ];
            }
        }
    }


    private function parseShippingDepartureAddress()
    {
        if (isset($this->moloni->settings['delivery_departure_address']) &&
            !empty($this->moloni->settings['delivery_departure_address'])) {
            $this->document['delivery_departure_address'] = $this->moloni->settings['delivery_departure_address'];
        } else {
            $this->document['delivery_departure_address'] = $this->company['address'];
        }

        if (isset($this->moloni->settings['delivery_departure_city']) &&
            !empty($this->moloni->settings['delivery_departure_city'])) {
            $this->document['delivery_departure_city'] = $this->moloni->settings['delivery_departure_city'];
        } else {
            $this->document['delivery_departure_city'] = $this->company['city'];
        }

        if (isset($this->moloni->settings['delivery_departure_zip_code']) &&
            !empty($this->moloni->settings['delivery_departure_zip_code'])) {
            $this->document['delivery_departure_zip_code'] = $this->moloni->settings['delivery_departure_zip_code'];
        } else {
            $this->document['delivery_departure_zip_code'] = $this->company['zip_code'];
        }

        if (isset($this->moloni->settings['delivery_departure_country']) &&
            !empty($this->moloni->settings['delivery_departure_country'])) {
            $this->document['delivery_departure_country'] = $this->moloni->settings['delivery_departure_country'];
        } else {
            $this->document['delivery_departure_country'] = $this->company['country_id'];
        }

        // If the delivery departure country is Portugal check if the vat is valid
        if ($this->document['delivery_departure_country'] == 1) {
            $checkZipCode = $this->tools->zipCheck($this->document['delivery_departure_zip_code']);
            $this->document['delivery_departure_zip_code'] = $checkZipCode;
        }

        return true;
    }

    private function parseShippingDestinationAddress()
    {
        $shippingAddress = $this->order->getShippingAddress();
        if ($shippingAddress) {
            $street = $shippingAddress->getStreet();
            if ($street && is_array($street)) {
                $this->document['delivery_destination_address'] = implode(' ', $street);
            }

            $this->document['delivery_destination_city'] = $shippingAddress->getCity();
            $this->document['delivery_destination_zip_code'] = $shippingAddress->getPostcode();

            $countryCode = $shippingAddress->getCountryId();
            $countryId = $this->tools->getCountryIdByISO($countryCode);
            $this->document['delivery_destination_country'] = $countryId;

            if ($this->document['delivery_destination_country'] == 1 &&
                !empty($this->document['delivery_destination_zip_code'])) {
                $checkZipCode = $this->tools->zipCheck($this->document['delivery_destination_zip_code']);
                $this->document['delivery_destination_zip_code'] = $checkZipCode;
            }

        }

        return true;
    }

    /**
     * Returns the delivery_method_id based on a name
     * If the delivery method does not exist create it
     * @param string $name
     * @return bool|int
     */
    private function handleDeliveryMethod($name)
    {
        $deliveryMethodId = false;

        if (empty($name)) {
            return false;
        }

        $deliveryMethods = $this->moloni->deliveryMethods->getAll();
        if (!empty($deliveryMethods) && is_array($deliveryMethods)) {
            foreach ($deliveryMethods as $deliveryMethod) {
                if (mb_strtolower($name) == mb_strtolower($deliveryMethod['name'])) {
                    $deliveryMethodId = $deliveryMethod['delivery_method_id'];
                    break;
                }
            }
        }

        // If the delivery method does not exist try to insert
        if (!$deliveryMethodId) {
            $insert = $this->moloni->deliveryMethods->insert(['name' => $name]);
            if (isset($insert['delivery_method_id'])) {
                $deliveryMethodId = $insert['delivery_method_id'];
            }
        }

        return $deliveryMethodId;
    }

    /**
     * @param $name
     * @return bool|integer
     */
    private function handlePaymentMethod($name)
    {
        $paymentMethodId = false;

        if (empty($name)) {
            return false;
        }

        $paymentMethods = $this->moloni->paymentMethods->getAll();
        if (!empty($paymentMethods) && is_array($paymentMethods)) {
            foreach ($paymentMethods as $paymentMethod) {
                if (mb_strtolower($name) == mb_strtolower($paymentMethod['name'])) {
                    $paymentMethodId = $paymentMethod['payment_method_id'];
                    break;
                }
            }
        }

        // If the payment method does not exist try to insert
        if (!$paymentMethodId) {
            $insert = $this->moloni->paymentMethods->insert(['name' => $name]);
            if (isset($insert['payment_method_id'])) {
                $paymentMethodId = $insert['payment_method_id'];
            }
        }

        return $paymentMethodId;
    }

    /**
     * @param int $documentId
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return bool|array
     */
    private function validateDocument($documentId, $order)
    {
        $moloniDocument = $this->moloni->documents->getOne(["document_id" => $documentId]);

        if (!isset($moloniDocument['net_value'])) {
            return false;
        }

        $moloniDocumentTotal = $moloniDocument['net_value'];
        $magentoOrderTotal = $order->getGrandTotal();

        // If the difference is less than two cents (due to rounding values)
        return (abs($magentoOrderTotal - $moloniDocumentTotal) < 0.02) ? $moloniDocument : false;
    }

}
