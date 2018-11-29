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
    private $__dependencies = [
        "Errors" => "Errors.php",
        "Session" => "Session.php"
    ];

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

    public function __get($name)
    {
        $fileName = self::LIBRARY_PATH . "/classes/" . ucfirst($name) . "Class.php";
        $className = self::MY_NAMESPACE . ucfirst($name);
        if (!method_exists($this, $name)) {
            $this->load($fileName, $name, $className);
        }

        return $this->{$name};
    }

    public function __call($name, $documentType)
    {
        $this->documentType = empty($documentType[0]) ? "documents" : $documentType[0];

        if (!isset($this->{$name}) && !isset($this->dependencies[$name])) {
            $this->load("moloni/classes/" . $name . ".class.php", $name, $this->namespace . $name);
        }
        return $this->{$name};
    }

    public function getCompanyId()
    {
        return isset($this->_tokens->getCompanyId);
    }

    public function isAuthorized($code)
    {
        if (!empty($code)) {
            return $this->session->doAuthorization($code);
        } else {
            return $this->errors->throwError(__('Erro de autenticação'), __('Code is not defined'), __FUNCTION__);
        }
    }

    public function hasValidSession()
    {
        $this->activeSession = $this->session->validateSession();
        return $this->activeSession ? true : false;
    }

    public function getAuthenticationUrl()
    {
        return $this->session->formAuthenticationUrl();
    }

    public function execute($url, $params = false, $debug = false)
    {
        $response = false;
        $requestUrl = self::API_URL . $url . ($this->activeSession ? '/?human_errors=true&access_token=' . $this->activeSession['access_token'] : '');
        $this->_curl->post($requestUrl, $params);
        $raw = $this->_curl->getBody();

        if (!empty($raw)) {
            $response = json_decode($raw, true);
        }
        
        if ($debug) {
            echo "Url: " . $requestUrl . "<br>";
            echo "Dados Enviados: " . "<br><pre>" . print_r($params, true) . "</pre><br>";
            echo "Dados Recebidos: " . "<br><pre>" . print_r($response, true) . "</pre>";
            exit;
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
