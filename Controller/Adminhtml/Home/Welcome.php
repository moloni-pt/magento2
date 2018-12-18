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
use Magento\Framework\View\Result\PageFactory;
use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;
use Invoicing\Moloni\Model\TokensRepository;

class Welcome extends Action
{

    private $moloni;
    private $pageFactory;
    private $tokensRepository;
    private $coreRegistry;

    /**
     * Welcome constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param TokensRepository $tokensRepository
     * @param Moloni $Moloni
     * @param Registry $coreRegistry
     */

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Moloni $Moloni,
        TokensRepository $tokensRepository,
        Registry $coreRegistry
    ) {
        $this->pageFactory = $resultPageFactory;
        $this->moloni = $Moloni;
        $this->tokensRepository = $tokensRepository;
        $this->coreRegistry = $coreRegistry;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     * @throws \Exception
     */
    public function execute()
    {
        if ($this->getRequest()->getPostValue("developer_id") && $this->getRequest()->getPostValue('secret_token')) {
            $this->handleAuthentication();
        } elseif ($this->getRequest()->getParam("code")) {
            if (!$this->moloni->checkAuthorizationCode($this->getRequest()->getParam('code'))) {
                $this->coreRegistry->register(
                    "moloni_messages",
                    [['type' => 'error', 'message' => $this->moloni->errors->getErrors('last')['message']]]
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
        $tokens = $this->tokensRepository->getTokens();

        $tokens->setDeveloperId($this->getRequest()->getPostValue('developer_id'));
        $tokens->setRedirectUri($this->getRequest()->getPostValue('redirect_uri'));
        $tokens->setSecretToken($this->getRequest()->getPostValue('secret_token'));
        $tokens->save();

        $authenticationUrl = $this->moloni->getAuthenticationUrl();
        if ($authenticationUrl) {
            $this->_redirect($authenticationUrl);
        } else {
            $this->coreRegistry->register(
                "moloni_messages",
                [['type' => 'error', 'message' => __('Error while saving data...')]]
            );
        }
    }
}
