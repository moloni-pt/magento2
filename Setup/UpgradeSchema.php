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

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    private $installer;
    private $tables = array(
        "moloni_tokens",
        "moloni_settings",
        "moloni_documents"
    );

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), "1.3.0", "<")) {
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
    
    private function setTableMoloniTokens()
    {
        $this->installer->getConnection()->createTable(
            $table = $this->installer->getConnection()->newTable(
                $this->installer->getTable('moloni_tokens')
            )->addColumn(
                'id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [
                'identity' => true,
                'nullable' => false,
                'primary' => true,
                'unsigned' => true,
                ], 'Tokens combination id'
            )->addColumn(
                'access_token', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [], 'Access Token'
            )->addColumn(
                'refresh_token', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [], 'Refresh Token'
            )->addColumn(
                'company_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, [], 'Company Id'
            )->addColumn(
                'login_date', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT], 'Login date'
            )->addColumn(
                'expire_date', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT], 'Expire date'
            )->setComment('Used to store moloni session tokens - clean this table for a token reset')
        );

        return $table;
    }   
    
    private function setTableMoloniSettings()
    {
        $this->installer->getConnection()->createTable(
            $table = $this->installer->getConnection()->newTable(
                $this->installer->getTable('moloni_settings')
            )->addColumn(
                'option_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [
                'identity' => true,
                'nullable' => false,
                'primary' => true,
                'unsigned' => true,
                ], 'Tokens combination id'
            )->addColumn(
                'company_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, [], 'Company Id'
            )->addColumn(
                'store_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, [], 'Store Id'
            )->addColumn(
                'label', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [], 'Label'
            )->addColumn(
                'value', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [], 'Value'
            )->setComment('Used to store all moloni required settings')
        );

        return $table;
    }   
    
    private function setTableMoloniDocuments()
    {
        $this->installer->getConnection()->createTable(
            $table = $this->installer->getConnection()->newTable(
                $this->installer->getTable('moloni_documents')
            )->addColumn(
                'document_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [
                'identity' => true,
                'nullable' => false,
                'primary' => true,
                'unsigned' => true,
                ], 'Document Id'
            )->addColumn(
                'company_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, [], 'Company Id'
            )->addColumn(
                'store_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, [], 'Store Id'
            )->addColumn(
                'order_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, [], 'Order Id'
            )->addColumn(
                'order_total', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 25, [], 'Total'
            )->addColumn(
                'invoice_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, [], 'Invoice Id'
            )->addColumn(
                'invoice_total', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 25, [], 'Invoice total'
            )->addColumn(
                'invoice_status', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 1, [], 'Invoice Status'
            )->addColumn(
                'invoice_date', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT], 'Invoice date'
            )->addColumn(
                'invoice_type', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 25, [], 'Invoice type'
            )->addColumn(
                'metadata', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '2M', [], 'All json sent to moloni'
            )->setComment('Used to store moloni all the data from inserted documents')
        );

        return $table;
    }
    
    
    private function setIndexMoloniTokens()
    {
        $this->installer->getConnection()->addIndex(
            $this->installer->getTable('moloni_tokens'), $this->installer->getIdxName(
                $this->installer->getTable('moloni_tokens'), ['access_token', 'refresh_token'], \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
            ), ['access_token', 'refresh_token'], \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
        );
    }

    private function setIndexMoloniDocuments()
    {
        /*$this->installer->getConnection()->addIndex(
            $this->installer->getTable('moloni_documents'), $this->installer->getIdxName(
                $this->installer->getTable('moloni_documents'), ['document_id', 'company_id', 'store_id', 'order_id', 'order_total', 'invoice_id', 'invoice_total', 'invoice_status', 'invoice_date', 'invoice_type', 'metadata'], \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
            ), ['document_id', 'company_id', 'store_id', 'order_id', 'order_total', 'invoice_id', 'invoice_total', 'invoice_status', 'invoice_date', 'invoice_type', 'metadata'], \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
        );*/
    }
    
    private function setIndexMoloniSettings()
    {
        /*$this->installer->getConnection()->addIndex(
            $this->installer->getTable('moloni_settings'), $this->installer->getIdxName(
                $this->installer->getTable('moloni_settings'), ['option_id', 'company_id', 'store_id', 'label', 'value'], \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
            ), ['option_id', 'company_id', 'store_id', 'label', 'value'], \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
        );*/
    }
}
