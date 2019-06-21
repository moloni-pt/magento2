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

namespace Invoicing\Moloni\Api\Data;

interface DocumentsInterface
{

    const ID = 'document_id';
    const COMPANY_ID = 'company_id';
    const STORE_ID = 'store_id';
    const ORDER_ID = 'order_id';
    const ORDER_TOTAL = 'order_total';
    const INVOICE_ID = 'invoice_id';
    const INVOICE_TOTAL = 'invoice_total';
    const INVOICE_STATUS = 'invoice_status';
    const INVOICE_TYPE = 'invoice_type';
    const METADATA = 'metadata';

}
