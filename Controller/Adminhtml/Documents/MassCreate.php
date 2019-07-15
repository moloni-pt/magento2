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

class MassCreate extends Documents
{

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

            if (!$this->documentExists($orderId)) {
                $newDocument = $this->moloniDocumentsFactory->create();
                $newDocument->createDocumentFromOrderId($orderId);
                $newDocument->throwMessages();
            }
        }

        $this->_redirect('moloni/home/index');
        return true;
    }
}
