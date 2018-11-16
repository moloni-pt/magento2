<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Invoicing\Moloni\Model;

use Magento\Framework\HTTP\Client\Curl;

/**
 *
 * @author nuno_
 */
class MoloniFactory
{

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $_curl;

    /**
     * @param Context                             $context
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     */
    public function __construct(Curl $curl)
    {
        $this->_curl = $curl;
    }

    public function execute($url, $params = false)
    {
        //if the method is get
        $this->_curl->get($url);
        //if the method is post
        //response will contain the output in form of JSON string
        $response = $this->_curl->getBody();
        return $response;
    }
}
