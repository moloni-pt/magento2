<?php
/* Moloni
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Invoicing\Moloni\Libraries\MoloniLibrary\Controllers;

use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;
use Invoicing\Moloni\Model\DocumentsRepository;
use JsonException;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class Documents
{

    /**
     * @var Moloni
     * Moloni library with tokens and settings
     */
    private Moloni $moloni;

    /**
     * @var Tools
     * Tools for validation and creating valid info
     */
    private Tools $tools;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var OrderInterface Order interface
     */
    public $order = [];

    /**
     * @var array
     * Document array to be inserted
     */
    private array $document = [];

    /**
     * @var array
     * Array from companies/getOne endpoint
     */
    private array $company = [];

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var CurrencyFactory
     */
    private CurrencyFactory $currencyFactory;

    /**
     * @var Customers
     */
    private Customers $customers;

    /**
     * @var Products
     */
    private $products;

    /**
     * @var ManagerInterface
     */
    private ManagerInterface $messageManager;


    /**
     * @var DocumentsRepository
     */
    private DocumentsRepository $documentsRepository;

    /**
     * @var array[]
     */
    private array $messages = [];

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
     * @param DocumentsRepository $documentsRepository
     */
    public function __construct(
        Moloni $moloni,
        Tools $tools,
        Customers $customers,
        ProductsFactory $products,
        StoreManagerInterface $storeManager,
        OrderRepositoryInterface $orderRepository,
        CurrencyFactory $currencyFactory,
        ManagerInterface $messageManager,
        DocumentsRepository $documentsRepository
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
        $this->documentsRepository = $documentsRepository;
    }

    public function getMessages()
    {
        return !empty($this->messages) ? $this->messages : false;
    }

    /**
     * @param string $message
     */
    public function addWarning(string $message): void
    {
        $this->messages['warning'][] = $message;
    }

    /**
     * @param string $message
     */
    public function addError(string $message): void
    {
        $this->messages['error'][] = $message;
    }

    /**
     * @param string $message
     * @param array $values
     */
    public function addComplexSuccess(string $message, $values = []): void
    {
        $this->messages['complex_success'][] = array_merge(['message' => $message], $values);
    }

    /**
     * @param string $message
     * @param array $values
     */
    public function addComplexWarning(string $message, $values = []): void
    {
        $this->messages['complex_warning'][] = array_merge(['message' => $message], $values);
    }

    /**
     * This function sets $this->order with an object of the order
     * Then createDocument() is called to create the document
     * @param int $orderId
     * Id of a Magento order
     * @return array|boolean
     * Return array with [valid => 1] or with errors
     *
     * @throws JsonException
     */
    public function createDocumentFromOrderId(int $orderId)
    {
        $this->order = $this->orderRepository->get($orderId);
        $this->parseDocument();

        if ((int)$this->moloni->settings['shipping_document'] === 1) {
            $shippingDocument = $this->createShippingDocument();
            if (!$shippingDocument) {
                $message = $this->moloni->errors->getErrors('first')['title'];
                $message .= ' - ' . $this->moloni->errors->getErrors('first')['message'];
                $this->addError($message);
                return false;
            }
        }

        $result = $this->moloni->documents->setDocumentType()->insert($this->document);
        if (!isset($result['document_id'])) {
            $message = $this->moloni->errors->getErrors('first')['title'];
            $message .= ' - ' . $this->moloni->errors->getErrors('first')['message'];
            $this->addError($message);
            return false;
        }

        $hasValidTotals = $this->validateDocumentTotals($result['document_id'], $this->order);

        if (!$hasValidTotals) {
            $this->addComplexWarning(
                "Documento inserido como rascunho mas os totais não batem certo",
                [
                    'url' => $this->moloni->documents->getEditUrl($result['document_id']),
                    'button_text' => 'Editar documento'
                ]
            );
        }

        if ((int)$this->moloni->settings['document_status'] === 1 && $hasValidTotals) {
            $update = ['document_id' => $result['document_id'], 'status' => 1];

            if ($this->moloni->settings['document_email'] && !empty($this->order->getCustomerEmail())) {
                $update['send_email'][] = [
                    'email' => $this->order->getCustomerEmail(),
                    'name' => $this->order->getCustomerFirstname() . ' ' . $this->order->getCustomerLastname(),
                    'message' => ''
                ];
            }

            $result = $this->moloni->documents->update($update);


            $this->addComplexSuccess(
                "Documento emitido com sucesso",
                [
                    'url' => $this->moloni->documents->getViewUrl($result['document_id']),
                    'download_url' => $this->moloni->documents->getDownloadUrl(
                        ['document_id' => $result['document_id']]
                    )
                ]
            );
        }

        $this->setDocumentHasCreated($result['document_id'], $orderId);
        return $result;
    }

    /**
     * @return bool
     * @throws JsonException
     */
    private function createShippingDocument(): bool
    {
        // Add delivery datetime because its required
        $this->document['delivery_datetime'] = gmdate('Y-m-d H:i:s');
        $shippingDocumentInserted = $this->moloni->documents->setDocumentType('billsOfLading')->insert($this->document);
        if (!$shippingDocumentInserted) {
            return false;
        }

        $validDocument = $this->validateDocumentTotals($shippingDocumentInserted['document_id'], $this->order);
        if (!$validDocument) {
            $this->addComplexWarning(
                "Documento de transporte inserido como rascunho mas os totais não batem certo",
                [
                    'url' => $this->moloni->documents->getEditUrl($shippingDocumentInserted['document_id']),
                    'button_text' => 'Editar documento'
                ]
            );
            return true;
        }

        $closeDocument = $this->moloni->documents->update([
            'document_id' => $shippingDocumentInserted['document_id'],
            'status' => 1
        ]);

        if (!isset($closeDocument['document_id'])) {
            return false;
        }

        $moloniDocument = $this->moloni->documents->getOne(["document_id" => $closeDocument['document_id']]);

        $this->document['associated_documents'][] = [
            "associated_id" => $moloniDocument['document_id'],
            "value" => $moloniDocument['net_value']
        ];

        return true;
    }

    /**
     * Populates $this->>document based on $this->order
     * @return bool
     * @throws JsonException
     */
    private function parseDocument(): bool
    {
        $this->company = $this->moloni->companies->getOne();

        $customer = $this->customers->setCustomerFromOrder($this->order);
        $this->document['customer_id'] = $customer->customerId;

        $this->document['date'] = gmdate('Y-m-d');
        $this->document['expiration_date'] = gmdate('Y-m-d');
        $this->document['document_set_id'] = $this->moloni->settings['document_set_id'];
        $this->document['your_reference'] = $this->order->getIncrementId();
        $this->document['plugin_id'] = 19;

        $this->parseProducts();

        $this->parseCurrency();
        $this->parsePaymentMethods();

        if ($this->moloni->settings['shipping_details']) {
            $this->parseShippingDetails();
        }

        return true;
    }

    private function parseProducts(): void
    {
        $products = $this->order->getItems();
        if (is_array($products)) {
            foreach ($products as $key => $product) {
                if ($product->isDeleted() || $product->getParentItem()) {
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

    private function parseCurrency(): void
    {
        $orderCurrencyCode = $this->order->getOrderCurrencyCode();

        // @todo decide what to do when a company is not portuguese
        if ((int)$this->company['country_id'] === 1 && $orderCurrencyCode !== 'EUR') {
            $rate = $this->currencyFactory->create()->load($orderCurrencyCode)->getAnyRate("EUR");
            $this->document['exchange_currency_id'] = 1; // EUR
            $this->document['exchange_rate'] = $rate;
        }
    }

    /**
     * Set the document Shipping details
     */
    private function parseShippingDetails(): void
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

    private function parsePaymentMethods(): void
    {
        $orderPayment = $this->order->getPayment();
        if ($orderPayment && (float)$orderPayment->getAmountPaid() > 0) {
            $paymentName = $orderPayment->getMethodInstance()->getTitle();
            try {
                $paymentMethodId = $this->handlePaymentMethod($paymentName);
                if ($paymentMethodId) {
                    $this->document['payments'][] = [
                        'payment_method_id' => $paymentMethodId,
                        'value' => $orderPayment->getAmountPaid(),
                        'date' => gmdate('Y-m-d H:i:s')
                    ];
                }
            } catch (JsonException $e) {
            }
        }
    }


    private function parseShippingDepartureAddress(): bool
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
        if ((int)$this->document['delivery_departure_country'] === 1) {
            $checkZipCode = $this->tools->zipCheck($this->document['delivery_departure_zip_code']);
            $this->document['delivery_departure_zip_code'] = $checkZipCode;
        }

        return true;
    }

    /**
     * @return bool
     */
    private function parseShippingDestinationAddress(): bool
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

            if ((int)$this->document['delivery_destination_country'] === 1 &&
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
     * @throws JsonException
     */
    private function handleDeliveryMethod(string $name)
    {
        $deliveryMethodId = false;

        if (empty($name)) {
            return false;
        }

        $deliveryMethods = $this->moloni->deliveryMethods->getAll();
        if (!empty($deliveryMethods) && is_array($deliveryMethods)) {
            foreach ($deliveryMethods as $deliveryMethod) {
                if (mb_strtolower($name) === mb_strtolower($deliveryMethod['name'])) {
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
     * @throws JsonException
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
                if (mb_strtolower($name) === mb_strtolower($paymentMethod['name'])) {
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
     * @param int $orderId
     *
     * @throws JsonException
     */
    private function setDocumentHasCreated(int $documentId, int $orderId): void
    {
        $insertedDocument = $this->moloni->documents->getOne(['document_id' => $documentId]);

        $newDocument = $this->documentsRepository->create();
        $newDocument->setOrderId($orderId);
        $newDocument->setOrderTotal($this->order->getGrandTotal());

        $newDocument->setInvoiceId($insertedDocument['document_id']);
        $newDocument->setInvoiceTotal($insertedDocument['net_value']);
        $newDocument->setInvoiceStatus($insertedDocument['status']);
        $newDocument->setInvoiceDate(date('Y-m-d H:s:i'));
        $newDocument->setInvoiceType($insertedDocument['document_type']['saft_code']);
        $newDocument->setCompanyid($this->moloni->getSession()->companyId);
        $newDocument->setMetadata(json_encode($this->document, JSON_THROW_ON_ERROR));

        $this->documentsRepository->save($newDocument);
    }

    /**
     * @param int $documentId
     * @param OrderInterface $order
     * @return bool
     */
    private function validateDocumentTotals(int $documentId, OrderInterface $order): bool
    {
        $moloniDocument = $this->moloni->documents->getOne(["document_id" => $documentId]);

        if (!isset($moloniDocument['net_value'])) {
            return false;
        }

        $moloniDocumentTotal = $moloniDocument['net_value'];
        $magentoOrderTotal = $order->getGrandTotal();

        // If the difference is less than two cents (due to rounding values)
        return (abs($magentoOrderTotal - $moloniDocumentTotal) < 0.02);
    }

    /**
     * @return void
     */
    public function throwMessages(): void
    {
        if (!empty($this->messages)) {
            foreach ($this->messages as $type => $list) {
                foreach ($list as $message) {
                    switch ($type) {
                        case 'complex_success':
                            $this->messageManager->addComplexSuccessMessage('createDocumentSuccessMessage', $message);
                            break;
                        case 'complex_warning':
                            $this->messageManager->addComplexWarningMessage('createDocumentSuccessMessage', $message);
                            break;
                        case 'warning':
                            $this->messageManager->addWarningMessage($message);
                            break;
                        case 'error':
                            $this->messageManager->addErrorMessage($message);
                            break;
                    }
                }
            }
        }
    }
}
