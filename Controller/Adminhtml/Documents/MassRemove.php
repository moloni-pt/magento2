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

use Exception;
use Invoicing\Moloni\Controller\Adminhtml\Documents;
use Invoicing\Moloni\Libraries\MoloniLibrary\Controllers\DocumentsFactory as MoloniDocumentsFactory;
use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;
use Invoicing\Moloni\Model\DocumentsRepository;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\PageFactory;


class MassRemove extends Documents
{
    private array $data;

    /**
     * Documents constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Moloni $moloni
     * @param MoloniDocumentsFactory $moloniDocumentsFactory
     * @param DocumentsRepository $documentsRepository
     * @param UrlInterface $urlBuilder
     * @param RedirectFactory $redirectFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Moloni $moloni,
        MoloniDocumentsFactory $moloniDocumentsFactory,
        DocumentsRepository $documentsRepository,
        UrlInterface $urlBuilder,
        RedirectFactory $redirectFactory,
        array $data = []
    )
    {
        /** @noinspection UnusedConstructorDependenciesInspection */
        $this->data = $data;

        parent::__construct(
            $context,
            $resultPageFactory,
            $moloni,
            $moloniDocumentsFactory,
            $documentsRepository,
            $urlBuilder,
            $redirectFactory
        );
    }

    /**
     * Execute action based on request and return result
     *
     * @return ResultInterface|ResponseInterface
     *
     * @throws \JsonException
     */
    public function execute()
    {
        if (!$this->moloni->checkActiveSession()) {
            return $this->redirectFactory->create()->setPath($this->moloni->redirectTo);
        }

        $selectedOrders = $this->request->getParam('selected');

        if (!is_array($selectedOrders) || empty($selectedOrders)) {
            $this->messageManager->addErrorMessage(__("Não foram seleccionadas encomendas"));
            return $this->redirectFactory->create()->setPath('moloni/home/index');
        }

        foreach ($selectedOrders as $orderId) {
            try {
                $newDocument = $this->documentsRepository->create();
                $newDocument->setCompanyid($this->moloni->getSession()->companyId);
                $newDocument->setOrderId($orderId);
                $newDocument->setOrderTotal(0);
                $newDocument->setInvoiceId(0);
                $newDocument->setInvoiceTotal(0);
                $newDocument->setInvoiceStatus(-1);
                $newDocument->setInvoiceDate(date('Y-m-d H:s:i'));
                $newDocument->setInvoiceType('Anulada');
                $this->documentsRepository->save($newDocument);

                $this->messageManager->addSuccessMessage(__("O documento %1 não irá ser gerado no Moloni.", $orderId));

            } catch (Exception $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
            }
        }

        return $this->redirectFactory->create()->setPath('moloni/home/index');
    }
}
