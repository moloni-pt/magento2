<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Invoicing\Moloni\Libraries\MoloniLibrary;

use Invoicing\Moloni\Api\MoloniApiRepositoryInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\RequestInterface;
use Invoicing\Moloni\Model\TokensRepository;
use Invoicing\Moloni\Libraries\MoloniLibrary\Dependencies\ApiSession;
use Invoicing\Moloni\Libraries\MoloniLibrary\Dependencies\ApiErrors;

class Moloni implements MoloniApiRepositoryInterface
{
    const API_URL = 'https://api.moloni.pt/v1/';

    public $errors;
    public $activeSession = false;
    private $curl;
    public $tokensRepository;

    private $redirect;
    public $request;
    public $dataPersistor;
    public $session;

    public $redirectTo = null;

    public function __construct(
        Curl $curl,
        TokensRepository $tokensRepository,
        Redirect $redirect,
        RequestInterface $request,
        ApiSession $session,
        ApiErrors $errors
    ) {
        $this->curl = $curl;
        $this->tokensRepository = $tokensRepository;
        $this->redirect = $redirect;
        $this->request = $request;
        $this->session = $session;
        $this->errors = $errors;
    }

    public function __invoke($method, $action, $params = false)
    {
        echo "Hello";
        exit;
        return $this->execute($method . '/' . $action, $params);
    }

    public function checkActiveSession()
    {
        $activeTokens = $this->tokensRepository->getTokens();
        if (!empty($activeTokens->getAccessToken())) {
            if ($this->session->isValidSession()) {
                if (empty($this->session->companyId) && $this->request->getActionName() !== 'company') {
                    $this->redirectTo = 'moloni/home/company/';
                    return false;
                } else {
                    return true;
                }
            }
        }

        $this->redirectTo = 'moloni/home/welcome/';
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

        if ($this->activeSession) {
            $requestUrl .= '/?human_errors=true&access_token=' . $this->activeSession['access_token'];
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
}
