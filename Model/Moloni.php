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

    protected $curl;
    protected $tokens;
    protected $activeTokens;
    protected $dateTime;
    // Error handling
    public $hasError = false;
    public $errorMessages = array();

    /**
     * @param Context                             $context
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     */
    public function __construct(Curl $curl, TokensFactory $tokensFactory, DateTime $dateTime)
    {
        $this->curl = $curl;
        $this->dateTime = $dateTime;
        $this->tokens = $tokensFactory->create();
    }

    public function execute($url, $params = false)
    {
        //if the method is get
        $this->curl->post($url, $params);
        $raw = $this->curl->getBody();
        
        if(!empty($raw)){
            $response = json_decode($raw, true);
        }
        
        //if the method is post
        //response will contain the output in form of JSON string
        
        print_r($response);
        exit;
        
        return $response;
    }

    public function getAuthenticationUrl($developerId, $callbackUrl)
    {
        $activeTokens = $this->tokens->getTokens();
        if (!empty($developerId) && !empty($callbackUrl)) {
            return self::API_URL . 'authorize/?response_type=code&client_id=' . $developerId . '&redirect_uri=' . urlencode($callbackUrl);
        } else {
            return false;
        }
    }

    public function getAuthorizationUrl($code)
    {
        $activeTokens = $this->tokens->getTokens();
        if ($activeTokens && $activeTokens->getDeveloperId()) {
            return self::API_URL . 'grant/?grant_type=authorization_code&client_id=' . $activeTokens->getDeveloperId() . '&redirect_uri=' . urlencode($activeTokens->getRedirectUri()) . '&client_secret=' . $activeTokens->getSecretToken() . '&code=' . $code;
        }
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
                $this->activeTokens = $activeTokens->toArray();
            }
        } else {
            return false;
        }
    }

}
