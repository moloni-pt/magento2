<?php

namespace Invoicing\Moloni\Model\ResourceModel\OrdersDocuments;

use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Magento\Sales\Model\ResourceModel\Order;
use Psr\Log\LoggerInterface as Logger;

class Collection extends SearchResult
{
    /**
     * @var Moloni $Moloni
     */
    private Moloni $moloni;

    public function __construct(
        Moloni $moloni,
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'sales_order',
        $resourceModel = Order::class
    ) {
        $this->moloni = $moloni;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    protected function _initSelect()
    {
        parent::_initSelect();
        $this->moloni->checkActiveSession();

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

        if ($this->moloni->settings['orders_since']) {
            $query->where(
                'grid.created_at > ?',
                date("Y-m-d H:i:s", strtotime($this->moloni->settings['orders_since']))
            );
        }

        if (is_array($this->moloni->settings['orders_statuses']) &&
            !empty($this->moloni->settings['orders_statuses'])) {
            $query->where('grid.status IN (?)', $this->moloni->settings['orders_statuses']);
        }

        $tableDescription = $this->getConnection()->describeTable($this->getMainTable());
        foreach ($tableDescription as $columnInfo) {
            $this->addFilterToMap($columnInfo['COLUMN_NAME'], 'main_table.' . $columnInfo['COLUMN_NAME']);
        }

        if ($this->moloni->settings['orders_since']) {
            $sinceDate = date("Y-m-d h:i:s", strtotime($this->moloni->settings['orders_since']));
            $this->addFieldToFilter('grid.created_at', ['gteq' => $sinceDate]);
        }

        if (is_array($this->moloni->settings['orders_statuses']) &&
            !empty($this->moloni->settings['orders_statuses'])) {
            $this->addFieldToFilter('grid.status', ['in' => $this->moloni->settings['orders_statuses']]); // Not working
        }

        return $this;
    }

    public function getTotalCount(): int
    {
        return $this->getSize();
    }
}
