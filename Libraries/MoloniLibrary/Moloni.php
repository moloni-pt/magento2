<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Invoicing\Moloni\Libraries\MoloniLibrary;

use Invoicing\Moloni\Api\MoloniApiRepositoryInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Invoicing\Moloni\Model\TokensRepository;
use Invoicing\Moloni\Model\SettingsRepository;
use Invoicing\Moloni\Libraries\MoloniLibrary\Dependencies\ApiSession;
use Invoicing\Moloni\Libraries\MoloniLibrary\Dependencies\ApiErrors;

use /** @noinspection PhpUndefinedClassInspection */
    Invoicing\Moloni\Libraries\MoloniLibrary\Classes\CompaniesFactory;

class Moloni implements MoloniApiRepositoryInterface
{
    const API_URL = 'https://api.moloni.pt/v1/';

    public $errors;
    public $curl;
    public $tokensRepository;
    public $settingsRepository;

    public $request;
    public $dataPersistor;
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

        'customer_prefix' => '',
        'customer_vat' => '0',

        'default_maturity_date_id' => 'required',
        'default_measurement_unit_id' => 'required',

        'products_reference_prefix' => '',
        'products_at_category' => 'M',
        'products_auto_create' => '0',
        'products_sync_stock' => '0',
        'products_tax' => '0',
        'products_tax_exemption' => '',

        'shipping_tax' => '0',
        'shipping_tax_exemption' => '',

        'orders_since' => '2019-01-01 00:00:00',
        'orders_statuses' => '{}',
    ];

    public function __construct(
        Curl $curl,
        TokensRepository $tokensRepository,
        SettingsRepository $settingsRepository,
        RequestInterface $request,
        ApiSession $session,
        ApiErrors $errors,
        /** @noinspection PhpUndefinedClassInspection */
        CompaniesFactory $companiesFactory
    ) {
        $this->curl = $curl;
        $this->tokensRepository = $tokensRepository;
        $this->settingsRepository = $settingsRepository;
        $this->request = $request;
        $this->session = $session;
        $this->errors = $errors;

        $this->factories = [
            'companies' => $companiesFactory
        ];
    }

    public function __get($name)
    {
        if (!isset($this->{$name}) && isset($this->factories[$name])) {
            $this->{$name} = $this->factories[$name]->create();
        }

        return $this->{$name};
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
                    $this->setSettings($this->session->companyId);
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

    public function execute($url, $body = false, $debug = false)
    {
        $response = false;
        $requestUrl = self::API_URL . $url;

        if ($this->session->accessToken) {
            $requestUrl .= '/?human_errors=true&access_token=' . $this->session->accessToken;
        }

        $this->curl->post($requestUrl, $body);
        $rawResponse = $this->curl->getBody();

        if (!empty($rawResponse)) {
            $response = json_decode($rawResponse, true);
        }

        if ($debug) {
            $debug .= __('Url:') . $requestUrl . '<br>';
            $debug .= __('Dados Enviados: ') . '<br><pre>' . $rawResponse . '</pre><br>';
            $debug .= __('Dados Recebidos: ') . '<br><pre>' . $rawResponse . '</pre>';

            \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Psr\Log\LoggerInterface')->debug($debug);
        }

        return $response;
    }

    /**
     * @param $companyId
     * @return array
     */
    private function setSettings($companyId)
    {
        if ($companyId) {
            $savedSattings = $this->settingsRepository->getSettingsByCompany($companyId);
            if (!$savedSattings) {
                foreach ($this->settings as $label => $option) {
                    $savedSattings[$label] = $option;
                    $this->settingsRepository->saveSetting($companyId, $label, $option);
                }
            } else {
                foreach ($this->settings as $label => $option) {
                    if (!array_key_exists($label, $savedSattings)) {
                        $savedSattings[$label] = $option;
                        $this->settingsRepository->saveSetting($companyId, $label, $option);
                    }
                }
            }

            $this->settings = $savedSattings;
        }

        return $this->settings;
    }
}
