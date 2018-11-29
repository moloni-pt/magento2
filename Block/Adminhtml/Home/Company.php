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
namespace Invoicing\Moloni\Block\Adminhtml\Home;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\App\Request\DataPersistorInterface;
use Invoicing\Moloni\Model\MoloniFactory;

class Company extends Template
{

    protected $messages = false;
    protected $_coreRegistry;
    protected $_tokens;

    public function __construct(Context $context, Registry $coreRegistry, DataPersistorInterface $dataPersistant, MoloniFactory $moloniFactory)
    {
        $this->_coreRegistry = $coreRegistry;
        $this->_dataPersistor = $dataPersistant;

        parent::__construct($context);
    }

    public function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Moloni - Select your company'));
        return parent::_prepareLayout();
    }

    public function getMessages()
    {
        $persistingMessages = $this->_coreRegistry->registry('moloni_messages');
        if (!empty($persistingMessages)) {
            $this->_coreRegistry->registry('moloni_messages');
            $this->messages = $persistingMessages;
        }

        return $this->messages;
    }
}
