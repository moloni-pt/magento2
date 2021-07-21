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

use Invoicing\Moloni\Controller\Adminhtml\Home;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

class Company extends Home
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

        $companies = $this->moloni->companies->getAll();
        if (!$companies) {
            $this->moloni->dropActiveSession();
            $this->messageManager->addErrorMessage($this->moloni->errors->getErrors('last')['message']);
            return $this->redirectFactory->create()->setPath($this->moloni->redirectTo);
        }

        $this->dataPersistor->set('moloni_companies', $companies);
        return $this->initAction();
    }
}
