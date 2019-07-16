<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Invoicing\Moloni\Libraries\MoloniLibrary;

use Invoicing\Moloni\Api\MoloniApiRepositoryInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\RequestInterface;
use Invoicing\Moloni\Model\TokensRepository;
use Invoicing\Moloni\Model\SettingsRepository;
use Invoicing\Moloni\Libraries\MoloniLibrary\Dependencies\ApiSession;
use Invoicing\Moloni\Libraries\MoloniLibrary\Dependencies\ApiErrors;
use Magento\Framework\App\Request\DataPersistorInterface;

use Invoicing\Moloni\Libraries\MoloniLibrary\Classes\CompaniesFactory;
use Invoicing\Moloni\Libraries\MoloniLibrary\Classes\CustomersFactory;
use Invoicing\Moloni\Libraries\MoloniLibrary\Classes\ProductsFactory;
use Invoicing\Moloni\Libraries\MoloniLibrary\Classes\ProductsCategoriesFactory;
use Invoicing\Moloni\Libraries\MoloniLibrary\Classes\DocumentSetsFactory;
use Invoicing\Moloni\Libraries\MoloniLibrary\Classes\DocumentsFactory;
use Invoicing\Moloni\Libraries\MoloniLibrary\Classes\MeasurementUnitsFactory;
use Invoicing\Moloni\Libraries\MoloniLibrary\Classes\DeliveryMethodsFactory;
use Invoicing\Moloni\Libraries\MoloniLibrary\Classes\PaymentMethodsFactory;
use Invoicing\Moloni\Libraries\MoloniLibrary\Classes\ProductsTaxesFactory;
use Invoicing\Moloni\Libraries\MoloniLibrary\Classes\ProductsTaxExemptionsFactory;
use Invoicing\Moloni\Libraries\MoloniLibrary\Classes\CountriesFactory;

class Moloni implements MoloniApiRepositoryInterface
{
    const API_URL = 'https://api.moloni.pt/v1/';

    public $logs = [];
    /**
     * @var ApiErrors
     */
    public $errors;
    public $curl;
    public $tokensRepository;
    public $settingsRepository;

    public $request;
    public $dataPersistor;

    /**
     * @var ApiSession
     */
    public $session;

    private $factories = [];

    public $redirectTo = null;

    /*
     * 'Required' means its not set and must be sent to the settings page
     */
    public $settings = [
        'cae' => '',
        'debug_console' => '0',

        'document_set_id' => 'required',
        'document_type' => 'invoices',
        'document_status' => 0,
        'document_email' => 0,

        'shipping_details' => 0,
        'shipping_document' => 0,
        'delivery_departure_address' => '',
        'delivery_departure_city' => '',
        'delivery_departure_zip_code' => '',
        'delivery_departure_country' => '',

        'customer_vat' => '0',

        'default_maturity_date_id' => 'required',
        'default_measurement_unit_id' => 'required',

        'products_at_category' => 'M',
        'products_auto_create' => '0',
        'products_sync_stock' => '0',
        'products_sync_price' => '0',
        'products_tax' => '0',
        'products_tax_exemption' => '',

        'shipping_tax' => '0',
        'shipping_tax_exemption' => '',

        'orders_since' => '2019-01-01 00:00:00',
        'orders_statuses' => [],

        'cron_date' => false
    ];

    public function __construct(
        Curl $curl,
        TokensRepository $tokensRepository,
        SettingsRepository $settingsRepository,
        RequestInterface $request,
        ApiSession $session,
        ApiErrors $errors,
        DataPersistorInterface $dataPersistor,
        CompaniesFactory $companiesFactory,
        CustomersFactory $customers,
        ProductsFactory $products,
        ProductsCategoriesFactory $productsCategories,
        DocumentSetsFactory $documentSetsFactory,
        DocumentsFactory $documentsFactory,
        MeasurementUnitsFactory $measurementUnitsFactory,
        DeliveryMethodsFactory $deliveryMethods,
        PaymentMethodsFactory $paymentMethods,
        ProductsTaxesFactory $productsTaxesFactory,
        ProductsTaxExemptionsFactory $productsTaxExemptionsFactory,
        CountriesFactory $countries
    )
    {
        $this->curl = $curl;
        $this->tokensRepository = $tokensRepository;
        $this->settingsRepository = $settingsRepository;
        $this->request = $request;
        $this->session = $session;
        $this->errors = $errors;
        $this->dataPersistor = $dataPersistor;

        $this->factories = [
            'companies' => $companiesFactory,
            'customers' => $customers,
            'products' => $products,
            'productsCategories' => $productsCategories,
            'documentSets' => $documentSetsFactory,
            'documents' => $documentsFactory,
            'measurementUnits' => $measurementUnitsFactory,
            'taxes' => $productsTaxesFactory,
            'taxExemptions' => $productsTaxExemptionsFactory,
            'countries' => $countries,
            'deliveryMethods' => $deliveryMethods,
            'paymentMethods' => $paymentMethods
        ];
    }

    public function __get($name)
    {
        if (!isset($this->{$name}) && isset($this->factories[$name])) {
            $this->{$name} = $this->factories[$name]->create();
        }

        return $this->{$name};
    }

    /**
     * @return ApiSession
     */
    public function getSession()
    {
        return $this->session;
    }

    public function checkActiveSession()
    {
        $activeTokens = $this->tokensRepository->getTokens();
        if (!empty($activeTokens->getAccessToken())) {
            $setCompanyId = $this->request->getParam('company_id', false);
            if ($setCompanyId && $setCompanyId > 0) {
                $activeTokens->setCompanyId($setCompanyId)->save();
            }

            if ($this->session->isValidSession()) {
                if (empty($this->session->companyId) && $this->request->getActionName() !== 'company') {
                    $this->redirectTo = 'moloni/home/company/';
                    return false;
                } else {
                    $settings = $this->setSettings($this->session->companyId);
                    $this->dataPersistor->set('moloni_settings', $settings);
                    return true;
                }
            }
        }

        $this->redirectTo = 'moloni/home/welcome/';
        return false;
    }

    public function dropActiveSession()
    {
        $this->redirectTo = 'moloni/home/welcome/';
        $activeTokens = $this->tokensRepository->getTokens();
        $activeTokens->delete();
        return false;
    }

    /**
     * @param $authorizationCode
     * @return bool
     */
    public function checkAuthorizationCode($authorizationCode)
    {
        if ($this->session->isValidAuthorizationCode($authorizationCode)) {
            return true;
        }

        return false;
    }

    /**
     * @return bool|string
     */
    public function getAuthenticationUrl()
    {
        $tokens = $this->tokensRepository->getTokens();
        if (!empty($tokens->getDeveloperId()) && !empty($tokens->getRedirectUri())) {
            $loginUrl = self::API_URL . 'authorize/?response_type=code';
            $loginUrl .= '&client_id=' . $tokens->getDeveloperId();
            $loginUrl .= '&redirect_uri=' . urlencode($tokens->getRedirectUri());
            return $loginUrl;
        }

        return false;
    }

    public function execute($url, $body = false)
    {
        $response = false;
        $requestUrl = self::API_URL . $url;

        if ($this->session->accessToken) {
            $requestUrl .= '/?human_errors=true&access_token=' . $this->session->accessToken;
        }

        $this->curl->post($requestUrl, $body);
        $rawResponse = $this->curl->getBody();

        if (!empty($rawResponse)) {
            try {
                $response = json_decode($rawResponse, true);
            } catch (\Exception $e) {
                $response = [];
            }
        }

        $this->logs[] = [
            'url' => $requestUrl,
            'sent' => $body,
            'received' => $response
        ];

        $this->dataPersistor->set("moloni_logs", $this->logs);

        return $response;
    }

    /**
     * @param $companyId
     * @param int $storeId
     * @return array
     */
    private function setSettings($companyId, $storeId = 0)
    {
        if ($companyId) {
            $savedSettings = $this->settingsRepository->getSettingsByCompany($companyId);
            if (!$savedSettings) {
                // If there are no saved settings in the table
                foreach ($this->settings as $label => $option) {
                    $savedSettings[$label] = $option;
                    $this->settingsRepository->saveSetting($companyId, $label, $option);
                }
            } else {
                // If any setting doesn't exist add it to the database
                foreach ($this->settings as $label => $option) {
                    if (!array_key_exists($label, $savedSettings)) {
                        $savedSettings[$label] = $option;
                        $this->settingsRepository->saveSetting($companyId, $label, $option);
                    }
                }
            }

            $savedSettings['orders_statuses'] = json_decode($savedSettings['orders_statuses'], true);

            $this->settings = $savedSettings;
        }

        return $this->settings;
    }


}
