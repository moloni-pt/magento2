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

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{

    private $tables = array(
        "moloni_tokens",
       // "moloni_settings",
       // "moloni_documents",
    );
    private $installer;

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->installer = $setup;
        $this->installer->startSetup();

        foreach ($this->tables as $table) {
            if (!$this->installer->tableExists($table)) {
                $this->{"setTable".str_replace(' ', '', ucwords(str_replace('-_', ' ', $table)))}();
                $this->{"setIndex".str_replace(' ', '', ucwords(str_replace('-_', ' ', $table)))}();
            }
        }
        
        $this->installer->endSetup();
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
                'developer_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [], 'Developer Id'
            )->addColumn(
                'redirect_uri', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [], 'Redirect Uri'
            )->addColumn(
                'secret_token', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [], 'Moloni Client Secret'
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

    private function setIndexMoloniTokens()
    {
        $this->installer->getConnection()->addIndex(
            $this->installer->getTable('moloni_tokens'), $setup->getIdxName(
                $this->installer->getTable('moloni_tokens'), ['access_token', 'refresh_token'], \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
            ), ['access_token', 'refresh_token'], \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
        );
    }
}
