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

namespace Invoicing\Moloni\Model;

use Invoicing\Moloni\Api\Data\DocumentsInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class Documents extends AbstractModel implements
    IdentityInterface,
    DocumentsInterface
{

    const CACHE_TAG = 'moloni_documents';

    public string $cacheTag = 'moloni_documents';
    public string $eventPrefix = 'moloni_documents';

    /**
     * Initialize resource model
     *
     * @return void
     * @noinspection MagicMethodsValidityInspection
     */
    public function _construct()
    {
        $this->_init(ResourceModel\Settings::class);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @inheritdoc
     */
    public function getInvoiceDate(): ?string
    {
        return $this->getData(self::INVOICE_DATE);
    }

    /**
     * @inheritdoc
     */
    public function setCompanyId(int $companyId): AbstractModel
    {
        return $this->setData(self::COMPANY_ID, $companyId);
    }

    /**
     * @inheritdoc
     */
    public function setOrderId($orderId): AbstractModel
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @inheritdoc
     */
    public function setOrderTotal($orderTotal): AbstractModel
    {
        return $this->setData(self::ORDER_TOTAL, $orderTotal);
    }

    /**
     * @inheritdoc
     */
    public function setInvoiceId($invoiceId): AbstractModel
    {
        return $this->setData(self::INVOICE_ID, $invoiceId);
    }

    /**
     * @inheritdoc
     */
    public function setInvoiceTotal($invoiceTotal): AbstractModel
    {
        return $this->setData(self::INVOICE_TOTAL, $invoiceTotal);
    }

    /**
     * @inheritdoc
     */
    public function setInvoiceStatus($invoiceStatus): AbstractModel
    {
        return $this->setData(self::INVOICE_STATUS, $invoiceStatus);
    }

    /**
     * @inheritdoc
     */
    public function setInvoiceDate($invoiceDate): AbstractModel
    {
        return $this->setData(self::INVOICE_DATE, $invoiceDate);
    }

    /**
     * @inheritdoc
     */
    public function setMetadata($metadata): AbstractModel
    {
        return $this->setData(self::METADATA, $metadata);
    }

    /**
     * @inheritdoc
     */
    public function setInvoiceType($invoiceType): AbstractModel
    {
        return $this->setData(self::INVOICE_TYPE, $invoiceType);
    }
}
