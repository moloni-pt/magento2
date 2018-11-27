<?php
/**
 * Module for Magento 2 by Moloni
 * Copyright (C) 2017  Moloni, lda
 * 
 * This file is part of Invoicing/Moloni.
 * 
 * Invoicing/Moloni is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
namespace Invoicing\Moloni\Controller\Adminhtml\Home;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\View\Result\PageFactory;
use Invoicing\Moloni\Model\TokensFactory;
use Invoicing\Moloni\Model\MoloniFactory;

class Welcome extends Action
{

    protected $_page;
    protected $_moloni;
    protected $_tokensFactory;
    protected $_coreRegistry;
    protected $_response;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
    Context $context, PageFactory $resultPageFactory, TokensFactory $tokensFactory, MoloniFactory $moloniFactory, Registry $coreRegistry, Http $response, DataPersistorInterface $dataPersistant)
    {
        $this->moloni = $moloniFactory->create();
        $this->tokensFactory = $tokensFactory->create();
        $this->_page = $resultPageFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_dataPersistor = $dataPersistant;

        parent::__construct($context);
    }

    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Invoicing_Moloni::home_welcome');
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if ($this->getRequest()->getPostValue('developer_id') && $this->getRequest()->getPostValue('secret_token')) {
            $this->handleAuthentication();
        } else if ($this->getRequest()->getParam('code')) {
            if (!$this->moloni->isAuthorized($this->getRequest()->getParam('code'))) {
                $this->_coreRegistry->register('moloni_messages', array(array('type' => 'error', 'message' => $this->moloni->errors->getError('last')['message'])));
            } else {
                $this->_redirect->redirect($this->_response, 'moloni/home/company/');
            }
        }

        $resultPage = $this->_page->create();
        return $resultPage;
    }

    private function handleAuthentication()
    {
        if ($this->tokensFactory->getTokens() == null) {
            $tokensObj = $this->tokensFactory;
        } else {
            $tokensObj = $this->tokensFactory->getTokens();
        }

        $tokensObj->setDeveloperId($this->getRequest()->getPostValue('developer_id'));
        $tokensObj->setRedirectUri($this->getRequest()->getPostValue('redirect_uri'));
        $tokensObj->setSecretToken($this->getRequest()->getPostValue('secret_token'));
        $tokensObj->save();

        $authenticationUrl = $this->moloni->getAuthenticationUrl();
        $this->_redirect($authenticationUrl);
    }
}
