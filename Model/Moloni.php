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
    public $curl;
    public $tokens;
    public $dateTime;
    private $dependencies = [
        "Errors" => "Errors.php",
        "Session" => "Session.php"
    ];

    public function __construct(
        Curl $curl,
        TokensFactory $tokensFactory,
        DateTime $dateTime,
        Redirect $redirect,
        DataPersistorInterface $dataPersistant,
        RequestInterface $request
    ) {
        $this->curl = $curl;
        $this->dateTime = $dateTime;
        $this->tokens = $tokensFactory->create();
        $this->redirect = $redirect;
        $this->request = $request;
        $this->dataPersistor = $dataPersistant;

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
            $this->loadClass("moloni/classes/" . $name . ".class.php", $name, $this->namespace . $name);
        }
        return $this->{$name};
    }

    public function getCompanyId()
    {
        return $this->tokens->getCompanyId();
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
        $requestUrl = self::API_URL . $url;

        if ($this->activeSession) {
            $requestUrl .= '/?human_errors=true&access_token=' . $this->activeSession['access_token'];
        }

        // Do curl request and get the response
        $this->curl->post($requestUrl, $params);
        $rawResponse = $this->curl->getBody();

        if (!empty($raw)) {
            $response = json_decode($rawResponse, true);
        }

        if ($debug) {
            $debug .= __('Url:') . $requestUrl . "<br>";
            $debug .= __("Dados Enviados: ") . "<br><pre>" . $rawResponse . "</pre><br>";
            $debug .= __("Dados Recebidos: ") . "<br><pre>" . $rawResponse. "</pre>";

            \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Psr\Log\LoggerInterface')->debug($debug);
        }

        return $response;
    }

    private function loadDependencies()
    {
        foreach ($this->dependencies as $name => $depend) {
            $this->loadClass(
                self::LIBRARY_PATH . "/dependencies/" . $depend,
                self::MY_NAMESPACE . $name,
                strtolower($name)
            );
        }
    }

    private function loadClass($path, $className, $name)
    {
        if (!class_exists($className)) {
            require_once $path;
            $this->{$name} = new $className($this);
        }
    }
}
