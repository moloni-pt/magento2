<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Invoicing\Moloni\Model;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Stdlib\DateTime;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\Response\Http;
use Invoicing\Moloni\Model\TokensFactory;

/**
 *
 * @author nuno_
 */
class Moloni
{

    const API_URL = 'https://api.moloni.pt/v1/';
    const LIBRARY_PATH = 'MoloniLibrary';
    const MY_NAMESPACE = '\MoloniLibrary\\';

    public $errors;
    public $activeSession = false;
    public $_curl;
    public $_tokens;
    public $_dateTime;
    private $__dependencies = array(
        "Errors" => "Errors.php",
        "Session" => "Session.php",
        # "debug" => "debug.class.php",
    );

    /**
     * @param Context                             $context
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     */
    public function __construct(Curl $curl, TokensFactory $tokensFactory, DateTime $dateTime, Redirect $redirect, DataPersistorInterface $dataPersistant, RequestInterface $request)
    {
        $this->_curl = $curl;
        $this->_dateTime = $dateTime;
        $this->_tokens = $tokensFactory->create();
        $this->_redirect = $redirect;
        $this->_request = $request;
        $this->_dataPersistor = $dataPersistant;

        $this->loadDependencies();
    }
   

    public function getCompanyId()
    {
        return isset($this->_tokens->getCompanyId);
    }

    public function getAuthenticationUrl()
    {
        $activeTokens = $this->_tokens->getTokens();
        if (!empty($activeTokens->getDeveloperId()) && !empty($activeTokens->getRedirectUri())) {
            return self::API_URL . 'authorize/?response_type=code&client_id=' . $activeTokens->getDeveloperId() . '&redirect_uri=' . urlencode($activeTokens->getRedirectUri());
        } else {
            return false;
        }
    }

    public function doAuthorization($code)
    {
        $activeTokens = $this->_tokens->getTokens();
        if ($activeTokens && $activeTokens->getDeveloperId()) {
            $authorizationUrl = self::API_URL . 'grant/?grant_type=authorization_code&client_id=' . $activeTokens->getDeveloperId() . '&redirect_uri=' . urlencode($activeTokens->getRedirectUri()) . '&client_secret=' . $activeTokens->getSecretToken() . '&code=' . $code;
            $response = $this->execute($authorizationUrl);

            if (isset($response['error'])) {
                $this->errors->throwError(__('Erro de autenticação'), __('Ocorreu um erro durante a operação de autenticação'), $authorizationUrl, $response);
                return false;
            } else {
                $activeTokens->setAccessToken($response['access_token']);
                $activeTokens->setRefreshToken($response['refresh_token']);
                $activeTokens->setExpireDate($this->_dateTime->formatDate((time() + 3000), true));
                $activeTokens->setLoginDate($this->_dateTime->formatDate(true, true));

                $activeTokens->save();

                $this->activeSession = $activeTokens->toArray();
                return true;
            }
        }

        return false;
    }

    
    private function execute($url, $params = false)
    {
        $response = false;

        $this->_curl->post($url, $params);
        $raw = $this->_curl->getBody();

        if (!empty($raw)) {
            $response = json_decode($raw, true);
        }

        return $response;
    }

    private function loadDependencies()
    {
        foreach ($this->__dependencies as $name => $depend) {
            try {
                $this->load(self::LIBRARY_PATH . "/dependencies/" . $depend, strtolower($name), self::MY_NAMESPACE . $name);
            } catch (Exception $e) {
                echo 'Ups... There was a problem loading a required dependency: ', $e->getMessage(), "\n";
            }
        }
    }

    private function load($path, $name, $className)
    {
        if (!class_exists($className)) {
            require_once($path);
            $this->{$name} = new $className($this);
        }
    }
}
