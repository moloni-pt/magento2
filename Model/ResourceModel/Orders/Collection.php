<?php

namespace Invoicing\Moloni\Model\ResourceModel\Orders;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Psr\Log\LoggerInterface as Logger;


class Collection extends SearchResult
{
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'sales_order',
        $resourceModel = \Magento\Sales\Model\ResourceModel\Order::class
    )
    {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    protected function _initSelect()
    {
        parent::_initSelect();

        $query = $this->getSelect();

        $query->joinLeft(
            ['moloni' => $this->getTable('moloni_documents')],
            'moloni.order_id = main_table.entity_id',
            []
        );

        $query->joinLeft(
            ['grid' => $this->getTable('sales_order_grid')],
            'grid.entity_id = main_table.entity_id',
            ['billing_name', 'billing_address']
        );

        $query->joinLeft(
            ['address' => $this->getTable('sales_order_address')],
            'address.parent_id = main_table.entity_id AND address_type = "billing"',
            ['vat_id']
        );

        $query->where('moloni.order_id IS NULL');

        $tableDescription = $this->getConnection()->describeTable($this->getMainTable());
        foreach ($tableDescription as $columnInfo) {
            $this->addFilterToMap($columnInfo['COLUMN_NAME'], 'main_table.' . $columnInfo['COLUMN_NAME']);
        }

        return $this;
    }
}