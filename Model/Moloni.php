<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Invoicing\Moloni\Model;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Stdlib\DateTime;
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
    protected $curl;
    protected $tokens;
    public $activeSession;
    protected $dateTime;
    private $dependencies = array(
        "Errors" => "Errors.php",
        # "debug" => "debug.class.php",
    );

    /**
     * @param Context                             $context
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     */
    public function __construct(Curl $curl, TokensFactory $tokensFactory, DateTime $dateTime)
    {
        $this->curl = $curl;
        $this->dateTime = $dateTime;
        $this->tokens = $tokensFactory->create();

        $this->loadDependencies();
    }

    public function getAuthenticationUrl()
    {
        $activeTokens = $this->tokens->getTokens();
        if (!empty($activeTokens->getDeveloperId()) && !empty($activeTokens->getRedirectUri())) {
            return self::API_URL . 'authorize/?response_type=code&client_id=' . $activeTokens->getDeveloperId() . '&redirect_uri=' . urlencode($activeTokens->getRedirectUri());
        } else {
            return false;
        }
    }

    public function doAuthorization($code)
    {
        $activeTokens = $this->tokens->getTokens();
        if ($activeTokens && $activeTokens->getDeveloperId()) {
            $authorizationUrl = self::API_URL . 'grant/?grant_type=authorization_code&client_id=' . $activeTokens->getDeveloperId() . '&redirect_uri=' . urlencode($activeTokens->getRedirectUri()) . '&client_secret=' . $activeTokens->getSecretToken() . '&code=' . $code;
            $response = $this->execute($authorizationUrl);

            if (isset($response['error'])) {
                $this->errors->throwError(__('Erro de autenticação'), __('Ocorreu um erro durante a operação de autenticação'), $authorizationUrl, $response);
                return false;
            } else {
                $activeTokens->setAccessToken($response['access_token']);
                $activeTokens->setRefreshToken($response['refresh_token']);
                $activeTokens->setExpireDate($this->dateTime->formatDate((time() + 3000), true));
                $activeTokens->setLoginDate($this->dateTime->formatDate(true, true));

                $activeTokens->save();

                $this->activeSession = $activeTokens->toArray();
                return true;
            }
        }

        return false;
    }

    public function hasValidSession()
    {
        $activeTokens = $this->tokens->getTokens();
        if ($activeTokens && $activeTokens->getAccessToken()) {

            $currentTime = time();
            $accessTokenExpireDate = $this->dateTime->strToTime($activeTokens->getExpireDate());
            $refreshTokenExpireDate = $accessTokenExpireDate; //+ 432000; // Add 5 days until the refresh expires

            if ($currentTime > $accessTokenExpireDate) {
                if ($currentTime > $refreshTokenExpireDate) {
                    $activeTokens->delete();
                    return false;
                } else {
                    // Handle refresh
                    return true;
                }
            } else {
                $this->activeSession = $activeTokens->toArray();
                return true;
            }
        } else {
            return false;
        }
    }

    private function execute($url, $params = false)
    {
        $response = false;

        $this->curl->post($url, $params);
        $raw = $this->curl->getBody();

        if (!empty($raw)) {
            $response = json_decode($raw, true);
        }

        return $response;
    }

    private function loadDependencies()
    {
        foreach ($this->dependencies as $name => $depend) {
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
            $this->{$name} = new $className();
        }
    }
}
