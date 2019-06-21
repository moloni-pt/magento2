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

        $this->getSelect()
            ->columns('moloni_documents.*')
            ->joinLeft(
                ['moloni_documents' => $this->getTable('moloni_documents')],
                'moloni_documents.order_id = ' . 'main_table.entity_id',
                []
            )->where('moloni_documents.order_id IS NULL');
    }
}