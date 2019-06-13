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

namespace Invoicing\Moloni\Block\Adminhtml\Settings;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Index extends \Magento\Config\Block\System\Config\Edit
{
    private $moloniSettings = [];
    private $dataPersistor;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        DataPersistorInterface $dataPersistor
    )
    {
        $this->dataPersistor = $dataPersistor;

        parent::__construct($context);
    }

    public function _prepareLayout()
    {
        $this->getToolbar()->addChild(
            "save_button",
            'Magento\Backend\Block\Widget\Button',
            [
                'id' => 'save',
                'label' => __('Guardar Alterações'),
                'class' => 'save primary',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'save',
                            'target' => '#config-edit-form'
                        ]
                    ],
                ]
            ]
        );

        $block = $this->getLayout()
            ->createBlock('Invoicing\Moloni\Block\Adminhtml\Settings\Form');

        $this->setChild('form', $block);
    }

    public function getSetting($label)
    {
        if (isset($this->moloniSettings[$label])) {
            return $this->moloniSettings[$label];
        }

        return false;
    }

}
