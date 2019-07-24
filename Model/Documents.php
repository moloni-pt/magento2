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
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;

class Documents extends AbstractModel implements
    IdentityInterface,
    DocumentsInterface
{

    const CACHE_TAG = 'moloni_documents';

    public $cacheTag = 'moloni_documents';
    public $eventPrefix = 'moloni_documents';

    /**
     * Initialize resource model
     *
     * @return void
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
     *
     * @return string|null
     */
    public function getInvoiceDate()
    {
        return $this->getData(self::INVOICE_DATE);
    }

    /**
     * @inheritdoc
     *
     * @param int $companyId
     * @return $this
     */
    public function setCompanyId(int $companyId)
    {
        return $this->setData(self::COMPANY_ID, $companyId);
    }

    /**
     * @inheritdoc
     *
     * @param int $orderId
     * @return $this
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @inheritdoc
     *
     * @param float $orderTotal
     * @return $this
     */
    public function setOrderTotal($orderTotal)
    {
        return $this->setData(self::ORDER_TOTAL, $orderTotal);
    }

    /**
     * @inheritdoc
     *
     * @param int $invoiceId
     * @return $this
     */
    public function setInvoiceId($invoiceId)
    {
        return $this->setData(self::INVOICE_ID, $invoiceId);
    }

    /**
     * @inheritdoc
     *
     * @param float $invoiceTotal
     * @return $this
     */
    public function setInvoiceTotal($invoiceTotal)
    {
        return $this->setData(self::INVOICE_TOTAL, $invoiceTotal);
    }

    /**
     * @inheritdoc
     *
     * @param int $invoiceStatus
     * @return $this
     */
    public function setInvoiceStatus($invoiceStatus)
    {
        return $this->setData(self::INVOICE_STATUS, $invoiceStatus);
    }

    /**
     * @inheritdoc
     *
     * @param string $invoiceStatus
     * @return $this
     */
    public function setInvoiceDate($invoiceDate)
    {
        return $this->setData(self::INVOICE_DATE, $invoiceDate);
    }

    /**
     * @inheritdoc
     *
     * @param string
     * @return $this
     */
    public function setMetadata($metadata)
    {
        return $this->setData(self::METADATA, $metadata);
    }

    /**
     * @inheritdoc
     *
     * @param string $invoiceType
     * @return $this
     */
    public function setInvoiceType($invoiceType)
    {
        return $this->setData(self::INVOICE_TYPE, $invoiceType);
    }
}
