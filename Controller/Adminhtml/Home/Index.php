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

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Invoicing\Moloni\Model\TokensFactory;
use Invoicing\Moloni\Model\MoloniFactory;

class Index extends Action
{

    protected $_page;
    protected $_moloni;
    protected $_tokensFactory;
    protected $_coreRegistry;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(Context $context, PageFactory $resultPageFactory, TokensFactory $tokensFactory, MoloniFactory $moloniFactory, Registry $coreRegistry)
    {
        $this->_moloni = $moloniFactory;
        $this->_page = $resultPageFactory;
        $this->_tokensFactory = $tokensFactory;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);

        $this->_init();
    }

    public function _init()
    {
        $dbTokens = $this->_tokensFactory->create()->getTokens();
        if (!$dbTokens && !isset($_GET['code'])) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $redirect = $objectManager->get('\Magento\Framework\App\Response\Http');
            $redirect->setRedirect('https://api.moloni.pt/v1/authorize/?response_type=code&client_id=devmagento2&redirect_uri=http://retron.warz/magento2/admin/moloni/home/');
        }
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {   
        $url = 'https://api.moloni.pt/v1/grant/?grant_type=authorization_code&client_id=devmagento2&redirect_uri=http://retron.warz/magento2/admin/moloni/home/&client_secret=b349e4a794515326c808092d63c1af451ac96777&code='.$_GET['code'];
        $teste = $this->_moloni->execute($url);
        echo $url;

        $this->_coreRegistry->register('firstResult', $teste);
        return $this->_page->create();
    }
}
