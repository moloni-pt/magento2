<?php
/**
 * Module for Magento 2 by Moloni
 * Copyright (C) 2017  Moloni, lda.
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
use Magento\Framework\App\Response\Http;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;
use Magento\Framework\App\Request\DataPersistorInterface;

class Company extends Action
{
    private $page;
    private $moloni;
    private $dataPersistor;
    private $response;

    /**
     * Constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Moloni $moloni
     * @param Http $response
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Moloni $moloni,
        Http $response,
        DataPersistorInterface $dataPersistor
    )
    {
        $this->moloni = $moloni;
        $this->page = $resultPageFactory;
        $this->response = $response;
        $this->dataPersistor = $dataPersistor;

        parent::__construct($context);
    }

    /**
     * @return bool|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if (!$this->moloni->checkActiveSession()) {
            $this->_redirect($this->moloni->redirectTo);
            return false;
        }

        $companies = $this->moloni->companies->getAll();
        if (!$companies) {
            $this->moloni->dropActiveSession();
            $errorMessage = [['type' => 'error', 'message' => $this->moloni->errors->getErrors('last')['message']]];
            $this->dataPersistor->set('moloni_messages', $errorMessage);
            $this->_redirect($this->moloni->redirectTo);
            return false;
        }

        $this->dataPersistor->set('moloni_companies', $companies);

        $resultPage = $this->page->create();
        return $resultPage;
    }
}
