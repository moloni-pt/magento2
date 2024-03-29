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
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

class Remove extends Documents
{
    /**
     * Execute action based on request and return result
     *
     * @return ResultInterface|ResponseInterface
     */
    public function execute()
    {
        if (!$this->moloni->checkActiveSession()) {
            return $this->redirectFactory->create()->setPath($this->moloni->redirectTo);
        }

        $orderId = $this->request->getParam('order_id');

        if (!$orderId) {
            $this->messageManager->addErrorMessage(__("Encomenda não encontrada."));
            return $this->redirectFactory->create()->setPath('moloni/home/index');
        }

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
        } catch (Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            return $this->redirectFactory->create()->setPath('moloni/home/index');
        }

        $this->messageManager->addSuccessMessage(__("O documento não irá ser gerado no Moloni."));
        return $this->redirectFactory->create()->setPath('moloni/home/index');
    }
}
