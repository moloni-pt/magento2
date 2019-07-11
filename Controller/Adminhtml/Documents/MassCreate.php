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
use Invoicing\Moloni\Libraries\MoloniLibrary\Controllers\DocumentsFactory as MoloniDocuments;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;


class MassCreate extends Documents
{

    protected $moloniDocuments;
    /**
     * @var Filter
     */
    private $filter;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Filter $filter,
        Moloni $moloni,
        MoloniDocuments $moloniDocuments
    )
    {
        parent::__construct($context, $resultPageFactory, $moloni);

        $this->filter = $filter;
        $this->moloniDocuments = $moloniDocuments;
    }

    public function execute()
    {
        if (!$this->moloni->checkActiveSession()) {
            $this->_redirect($this->moloni->redirectTo);
            return false;
        }

        $selectedOrders = $this->getRequest()->getParam('selected');
        if (!is_array($selectedOrders) || empty($selectedOrders)) {
            $this->messageManager->addErrorMessage(__("Não foram seleccionadas encomendas"));
            $this->_redirect('moloni/home/index');
            return false;
        }

        foreach ($selectedOrders as $orderId) {
            $this->moloni->errors->clearErrors();

            /**
             * @var $documentFactory \Invoicing\Moloni\Libraries\MoloniLibrary\Controllers\Documents
             */
            $documentFactory = $this->moloniDocuments->create();
            $document = $documentFactory->createDocumentFromOrderId($orderId);

            if (!$document) {
                $errorMessage = $this->moloni->errors->getErrors('first');
                $this->messageManager->addErrorMessage($errorMessage['title']);
            } else {
                $this->messageManager->addComplexSuccessMessage(
                    'createDocumentSuccessMessage',
                    [
                        'order_number' => $documentFactory->order->getIncrementId(),
                        'document_name' => $this->moloni->documents->documentTypeName,
                        'document_url' => $this->moloni->documents->getViewUrl($document['document_id'])
                    ]
                );
            }
        }

        $this->_redirect('moloni/home/index');
        return true;
    }
}
