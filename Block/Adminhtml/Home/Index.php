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

use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;
use Invoicing\Moloni\Model\DocumentsRepository;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Index extends Template
{
    private Moloni $moloni;

    /**
     * @var DocumentsRepository
     */
    private DocumentsRepository $documents;

    public function __construct(
        Context $context,
        DocumentsRepository $documents,
        Moloni $moloni
    )
    {
        $this->moloni = $moloni;
        $this->documents = $documents;
        parent::__construct($context);
    }
}
