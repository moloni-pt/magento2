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

namespace Invoicing\Moloni\Controller\Adminhtml\Settings;

use Invoicing\Moloni\Controller\Adminhtml\Settings;

class Save extends Settings
{
    public function execute()
    {
        if (!$this->moloni->checkActiveSession()) {
            $this->_redirect($this->moloni->redirectTo);
            return false;
        }

        $page = $this->initAction();
        $settingsFormData = $this->getRequest()->getParam('general');
        if (is_array($settingsFormData)) {
            $settings = [];
            $settings = array_merge($settings, $this->getRequest()->getParam('general'));
            $settings = array_merge($settings, $this->getRequest()->getParam('products'));
            $settings = array_merge($settings, $this->getRequest()->getParam('orders'));
            $settings = array_merge($settings, $this->getRequest()->getParam('sync'));
            $companyId = $this->moloni->session->companyId;

            foreach ($settings as $label => $value) {
                $this->moloni->settingsRepository->saveSetting($companyId, $label, $value);
            }

            $this->messageManager->addSuccessMessage(__('Alterações guardadas com sucesso'));
        } else {
            $this->messageManager->addErrorMessage(__("Houve um erro ao guardar alterações"));
        }

        $this->_redirect('*/*/edit/invoicing/0');

        return $page;
    }
}
