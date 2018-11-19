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

    protected $activeTokens;
    protected $curl;
    protected $dateTime;
    protected $tokensFactory;

    /**
     * @param Context                             $context
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     */
    public function __construct(Curl $curl, TokensFactory $tokensFactory, DateTime $dateTime)
    {
        $this->curl = $curl;
        $this->dateTime = $dateTime;
        $this->tokensFactory = $tokensFactory;
    }

    public function execute($url, $params = false)
    {
        //if the method is get
        $this->curl->get($url);
        //if the method is post
        //response will contain the output in form of JSON string
        $response = $this->curl->getBody();
        return $response;
    }

    public function hasValidSession()
    {
        $activeTokens = $this->tokensFactory->create()->getTokens();
        if ($activeTokens) {

            $currentTime = time();
            $accessTokenExpireDate = $this->dateTime->strToTime($activeTokens->getExpireDate());
            $refreshTokenExpireDate = $accessTokenExpireDate; //+ 432000; // Add 5 days until the refresh expires

            if ($currentTime > $accessTokenExpireDate) {
                if ($currentTime > $refreshTokenExpireDate) {
                    return false;
                } else {
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
