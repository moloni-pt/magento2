<?php /** @noinspection PhpCSValidationInspection */

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
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Response\Http;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Invoicing\Moloni\Model\TokensFactory;
use Invoicing\Moloni\Model\MoloniFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

class Index extends Action
{

    private $page;
    private $moloni;
    private $moloniFactory;
    private $dataPersistor;
    private $coreRegistry;
    private $response;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param MoloniFactory $moloniFactory
     * @param Registry $coreRegistry
     * @param Http $response
     * @param DataPersistorInterface $dataPersistant
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        MoloniFactory $moloniFactory,
        Registry $coreRegistry,
        Http $response,
        DataPersistorInterface $dataPersistant
    ) {

        $this->moloniFactory = $moloniFactory;
        $this->page = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;
        $this->response = $response;
        $this->dataPersistor = $dataPersistant;

        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->moloni = $this->moloniFactory->create();

        if (!$this->moloni->hasValidSession()) {
            $this->_redirect->redirect($this->response, $this->moloni->redirectTo);
        }

        $resultPage = $this->page->create();
        return $resultPage;
    }
}
