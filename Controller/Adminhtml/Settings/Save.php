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
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

class Save extends Settings
{
    /**
     * Execute action based on request and return result
     *
     * @return ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        if (!$this->moloni->checkActiveSession()) {
            return $this->redirectFactory->create()->setPath($this->moloni->redirectTo);
        }

        $settingsFormData = $this->requestInterface->getParam('general');

        if (is_array($settingsFormData)) {
            $settings = [];
            $settings[] = $this->requestInterface->getParam('general');
            $settings[] = $this->requestInterface->getParam('products');
            $settings[] = $this->requestInterface->getParam('orders');
            $settings[] = $this->requestInterface->getParam('sync');
            $companyId = $this->moloni->session->companyId;

            $group = array_merge(...$settings);

            foreach ($group as $label => $value) {
                $this->moloni->settingsRepository->saveSetting($companyId, $label, $value);
            }

            $this->messageManager->addSuccessMessage(__('Alterações guardadas com sucesso'));
        } else {
            $this->messageManager->addErrorMessage(__("Houve um erro ao guardar alterações"));
        }

        return $this->redirectFactory->create()->setPath('moloni/settings/edit/invoicing/0');
    }
}
