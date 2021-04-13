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

use Magento\Framework\Model\AbstractModel;

interface DocumentsInterface
{

    public const ID = 'document_id';
    public const COMPANY_ID = 'company_id';
    public const STORE_ID = 'store_id';
    public const ORDER_ID = 'order_id';
    public const ORDER_TOTAL = 'order_total';
    public const INVOICE_ID = 'invoice_id';
    public const INVOICE_TOTAL = 'invoice_total';
    public const INVOICE_STATUS = 'invoice_status';
    public const INVOICE_TYPE = 'invoice_type';
    public const INVOICE_DATE = 'invoice_date';
    public const METADATA = 'metadata';

    /**
     * @param int $companyId
     * @return AbstractModel
     */
    public function setCompanyId(int $companyId): AbstractModel;

    /**
     *
     * @param int $orderId
     */
    public function setOrderId(int $orderId);

    /**
     *
     * @param float $orderTotal
     */
    public function setOrderTotal(float $orderTotal);

    /**
     *
     * @param int $invoiceId
     */
    public function setInvoiceId(int $invoiceId);

    /**
     *
     * @param float $invoiceTotal
     */
    public function setInvoiceTotal(float $invoiceTotal);

    /**
     *
     * @param int $invoiceStatus
     */
    public function setInvoiceStatus(int $invoiceStatus);

    /**
     *
     * @param string $invoiceDate
     */
    public function setInvoiceDate(string $invoiceDate);

    /**
     *
     * @param string in JSON $metadata
     */
    public function setMetadata($metadata);

    /**
     * @return string|null
     */
    public function getInvoiceDate(): ?string;
}
