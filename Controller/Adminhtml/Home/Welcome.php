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

use Invoicing\Moloni\Model\TokensRepository;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;
use Invoicing\Moloni\Model\MoloniFactory;

class Welcome extends Action
{

    private $logger;
    private $coreRegistry;
    private $moloni;
    private $tokens;
    private $moloniFactory;
    private $tokensRepository;
    private $pageFactory;

    /**
     * Welcome constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param LoggerInterface $logger
     * @param TokensRepository $tokensRepository
     * @param MoloniFactory $moloniFactory
     * @param Registry $coreRegistry
     */

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        LoggerInterface $logger,
        TokensRepository $tokensRepository,
        MoloniFactory $moloniFactory,
        Registry $coreRegistry
    )
    {
        $this->logger = $logger;
        $this->coreRegistry = $coreRegistry;
        $this->pageFactory = $resultPageFactory;
        $this->moloniFactory = $moloniFactory;
        $this->tokensRepository = $tokensRepository;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     * @throws \Exception
     */
    public function execute()
    {
        echo '<pre>';
        echo "Teste";
        exit;

        $tokensObj = $this->tokensFactory->getId();
       // print_r($tokensObj->getFirstItem()->toArray());
        exit;
        if ($this->getRequest()->getPostValue("developer_id") && $this->getRequest()->getPostValue('secret_token')) {
            $this->handleAuthentication();
        } elseif ($this->getRequest()->getParam("code")) {
            if (!$this->moloni->isAuthorized($this->getRequest()->getParam('code'))) {
                $this->coreRegistry->register(
                    "moloni_messages",
                    [['type' => 'error', 'message' => $this->moloni->errors->getError('last')['message']]]
                );
            } else {
                $this->_redirect->redirect($this->_response, 'moloni/home/company/');
            }
        }

        $resultPage = $this->pageFactory->create();
        return $resultPage;
    }

    /**
     * @throws \Exception
     */
    private function handleAuthentication()
    {
        try {
            $tokensObj = $this->tokensFactory->getCollection();

            $tokensObj->setDeveloperId($this->getRequest()->getPostValue('developer_id'));
            $tokensObj->setRedirectUri($this->getRequest()->getPostValue('redirect_uri'));
            $tokensObj->setSecretToken($this->getRequest()->getPostValue('secret_token'));
            $tokensObj->save();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
        exit;
        $authenticationUrl = $this->moloni->getAuthenticationUrl();
        $this->_redirect($authenticationUrl);
    }
}
