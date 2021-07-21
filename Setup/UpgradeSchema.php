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

namespace Invoicing\Moloni\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\SetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * @var SchemaSetupInterface|SetupInterface
     */
    private $installer;

    private $tables = [
        "moloni_tokens",
        "moloni_settings",
        "moloni_documents"
    ];

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        if (version_compare($context->getVersion(), "1.3.1", "<")) {
            $this->installer = $setup;
            $this->installer->startSetup();

            foreach ($this->tables as $table) {
                if (!$this->installer->tableExists($table)) {
                    $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $table)));
                    $this->{"setTable" . $className}();
                    $this->{"setIndex" . $className}();
                }
            }

            $this->installer->endSetup();
        }
    }

    private function setTableMoloniTokens(): Table
    {
        $this->installer->getConnection()->createTable(
            $table = $this->installer->getConnection()->newTable(
                $this->installer->getTable('moloni_tokens')
            )->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true,
                    'unsigned' => true,
                ],
                'Tokens combination id'
            )->addColumn(
                'developer_id',
                Table::TYPE_TEXT,
                255,
                [],
                'Developer Id'
            )->addColumn(
                'redirect_uri',
                Table::TYPE_TEXT,
                255,
                [],
                'Redirect Uri'
            )->addColumn(
                'secret_token',
                Table::TYPE_TEXT,
                255,
                [],
                'Moloni Client Secret'
            )->addColumn(
                'access_token',
                Table::TYPE_TEXT,
                255,
                [],
                'Access Token'
            )->addColumn(
                'refresh_token',
                Table::TYPE_TEXT,
                255,
                [],
                'Refresh Token'
            )->addColumn(
                'company_id',
                Table::TYPE_INTEGER,
                10,
                [],
                'Company Id'
            )->addColumn(
                'login_date',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Login date'
            )->addColumn(
                'expire_date',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Expire date'
            )->setComment('Used to store moloni session tokens - clean this table for a token reset')
        );

        return $table;
    }

    private function setTableMoloniSettings(): Table
    {
        $this->installer->getConnection()->createTable(
            $table = $this->installer->getConnection()->newTable(
                $this->installer->getTable('moloni_settings')
            )->addColumn(
                'option_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true,
                    'unsigned' => true,
                ],
                'Tokens combination id'
            )->addColumn(
                'company_id',
                Table::TYPE_INTEGER,
                10,
                [],
                'Company Id'
            )->addColumn(
                'store_id',
                Table::TYPE_INTEGER,
                10,
                [],
                'Store Id'
            )->addColumn(
                'label',
                Table::TYPE_TEXT,
                255,
                [],
                'Label'
            )->addColumn(
                'value',
                Table::TYPE_TEXT,
                255,
                [],
                'Value'
            )->setComment('Used to store all moloni required settings')
        );

        return $table;
    }

    private function setTableMoloniDocuments(): Table
    {
        $this->installer->getConnection()->createTable(
            $table = $this->installer->getConnection()->newTable(
                $this->installer->getTable('moloni_documents')
            )->addColumn(
                'document_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true,
                    'unsigned' => true,
                ],
                'Document Id'
            )->addColumn(
                'company_id',
                Table::TYPE_INTEGER,
                10,
                [],
                'Company Id'
            )->addColumn(
                'store_id',
                Table::TYPE_INTEGER,
                10,
                [],
                'Store Id'
            )->addColumn(
                'order_id',
                Table::TYPE_INTEGER,
                10,
                [],
                'Order Id'
            )->addColumn(
                'order_total',
                Table::TYPE_TEXT,
                25,
                [],
                'Total'
            )->addColumn(
                'invoice_id',
                Table::TYPE_INTEGER,
                10,
                [],
                'Invoice Id'
            )->addColumn(
                'invoice_total',
                Table::TYPE_TEXT,
                25,
                [],
                'Invoice total'
            )->addColumn(
                'invoice_status',
                Table::TYPE_INTEGER,
                1,
                [],
                'Invoice Status'
            )->addColumn(
                'invoice_date',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Invoice date'
            )->addColumn(
                'invoice_type',
                Table::TYPE_TEXT,
                25,
                [],
                'Invoice type'
            )->addColumn(
                'metadata',
                Table::TYPE_TEXT,
                '2M',
                [],
                'All json sent to moloni'
            )->setComment('Used to store moloni all the data from inserted documents')
        );

        return $table;
    }


    private function setIndexMoloniTokens()
    {
        $this->installer->getConnection()->addIndex(
            $this->installer->getTable('moloni_tokens'),
            $this->installer->getIdxName(
                $this->installer->getTable('moloni_tokens'),
                ['access_token', 'refresh_token'],
                AdapterInterface::INDEX_TYPE_FULLTEXT
            ),
            ['access_token', 'refresh_token'],
            AdapterInterface::INDEX_TYPE_FULLTEXT
        );
    }

    private function setIndexMoloniDocuments()
    {
        $this->installer->getConnection()->addIndex(
            $this->installer->getTable('moloni_documents'),
            $this->installer->getIdxName(
                $this->installer->getTable('moloni_documents'),
                [
                    'document_id', 'company_id', 'store_id', 'order_id',
                    'order_total', 'invoice_id', 'invoice_total',
                    'invoice_status', 'invoice_date', 'invoice_type', 'metadata'
                ],
                AdapterInterface::INDEX_TYPE_FULLTEXT
            ),
            [
                'document_id', 'company_id', 'store_id', 'order_id',
                'order_total', 'invoice_id', 'invoice_total', 'invoice_status',
                'invoice_date', 'invoice_type', 'metadata'
            ],
            AdapterInterface::INDEX_TYPE_FULLTEXT
        );
    }

    private function setIndexMoloniSettings()
    {
        $this->installer->getConnection()->addIndex(
            $this->installer->getTable('moloni_settings'),
            $this->installer->getIdxName(
                $this->installer->getTable('moloni_settings'),
                ['option_id', 'company_id', 'store_id', 'label', 'value'],
                AdapterInterface::INDEX_TYPE_FULLTEXT
            ),
            ['option_id', 'company_id', 'store_id', 'label', 'value'],
            AdapterInterface::INDEX_TYPE_FULLTEXT
        );
    }
}
