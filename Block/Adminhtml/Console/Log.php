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

namespace Invoicing\Moloni\Block\Adminhtml\Console;

use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Log extends Template
{
    private $dataPersistor;
    private $moloni;

    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        Moloni $moloni
    )
    {
        $this->dataPersistor = $dataPersistor;
        $this->moloni = $moloni;
        parent::__construct($context);
    }

    public function getLogs()
    {

        if (!empty($this->moloni->logs) && is_array($this->moloni->logs)) {
            return $this->moloni->logs;
        }

        // Search in data persistor
        $persistorLog = $this->dataPersistor->get('moloni_logs');
        if (!empty($persistorLog)) {
            $this->dataPersistor->set('moloni_logs', []);
            return $persistorLog;
        }

        return [];
    }
}
