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

namespace Invoicing\Moloni\Controller\Adminhtml\Documents;

use Invoicing\Moloni\Controller\Adminhtml\Documents;
use Magento\Backend\App\Action\Context;
use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;
use Invoicing\Moloni\Libraries\MoloniLibrary\Controllers\Documents as MoloniDocuments;
use Magento\Framework\View\Result\PageFactory;

class Create extends Documents
{

    protected $moloniDocuments;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Moloni $moloni,
        MoloniDocuments $moloniDocuments
    )
    {
        parent::__construct($context, $resultPageFactory, $moloni);

        $this->moloniDocuments = $moloniDocuments;
    }

    public function execute()
    {
        $page = $this->initAction();
        if (!$this->moloni->checkActiveSession()) {
            $this->_redirect($this->moloni->redirectTo);
            return false;
        }

        $orderId = $this->getRequest()->getParam('order_id');

        if (!$orderId) {
            $this->messageManager->addErrorMessage(__("Encomenda não encontrada."));
            $this->_redirect('*/home/index');
            return false;
        }

        $document = $this->moloniDocuments->createDocumentFromOrderId($orderId);

        if (!$document) {
            $errorMessage = $this->moloni->errors->getErrors('first');
            $this->messageManager->addErrorMessage($errorMessage['title']);
            $this->_redirect('*/home/index');
            return false;
        }

        return $page;
    }
}
