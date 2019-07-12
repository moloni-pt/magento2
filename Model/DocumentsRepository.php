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

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsFactory;

use Invoicing\Moloni\Api\DocumentsRepositoryInterface;
use Invoicing\Moloni\Model\ResourceModel\Documents as ObjectResourceModel;
use Invoicing\Moloni\Model\ResourceModel\Documents\CollectionFactory;
use Invoicing\Moloni\Api\Data\DocumentsInterface;
use Psr\Log\LoggerInterface;

class DocumentsRepository implements DocumentsRepositoryInterface
{
    public $objectFactory;
    public $objectResourceModel;
    public $collectionFactory;
    public $searchResultsFactory;
    public $searchCriteriaBuilder;
    public $logger;

    /**
     * @var SortOrder SortOrder
     */
    private $sortOrder;

    /**
     * @var SortOrderBuilder ortOrderBuilder
     */
    private $sortOrderBuilder;

    public function __construct(
        ObjectResourceModel $objectResourceModel,
        DocumentsFactory $objectFactory,
        CollectionFactory $collectionFactory,
        SearchResultsFactory $searchResultsFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrder $sortOrder,
        SortOrderBuilder $sortOrderBuilder,
        LoggerInterface $logger
    )
    {
        $this->objectFactory = $objectFactory;
        $this->objectResourceModel = $objectResourceModel;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrder = $sortOrder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->logger = $logger;
    }

    /**
     * @return Documents
     */
    public function create()
    {
        return $this->objectFactory->create();
    }

    /**
     * @param DocumentsInterface $document
     * @return DocumentsInterface
     */
    public function save(DocumentsInterface $document)
    {
        $this->documentsResults = false;
        try {
            $this->objectResourceModel->save($document);
        } catch (AlreadyExistsException $e) {
            $this->logger->critical($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
        return $document->getId();
    }

    /**
     * @param $documentId
     * @return \Invoicing\Moloni\Api\Data\DocumentsInterface int
     * @throws NoSuchEntityException
     */
    public function getById($documentId)
    {
        $object = $this->objectFactory->create();
        $this->objectResourceModel->load($object, $documentId);

        if (!$object->getId()) {
            throw new NoSuchEntityException(__('Moloni Document Not Found'));
        }

        return $object;
    }

    /**
     * @param $orderId
     * @return DocumentsInterface[]|\Magento\Framework\Api\AbstractExtensibleObject[]
     */
    public function getByOrderId($orderId)
    {
        $sortOrder = $this->sortOrderBuilder
            ->setField("document_id")
            ->setDirection('DESC')
            ->create();

        $filter = $this->searchCriteriaBuilder
            ->addFilter("order_id", $orderId)
            ->addSortOrder($sortOrder)
            ->create();

        return $this->getList($filter)->getItems();
    }

    /**
     * @param DocumentsInterface $option
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(DocumentsInterface $option)
    {
        try {
            $this->objectResourceModel->delete($option);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function deleteById($id)
    {
        try {
            return $this->delete($this->getById($id));
        } catch (NoSuchEntityException $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        /**
         * @var $searchResults \Magento\Framework\Api\SearchResults
         */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $collection = $this->collectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $fields[] = $filter->getField();
                $conditions[] = [$condition => $filter->getValue()];
            }
            if ($fields) {
                $collection->addFieldToFilter($fields, $conditions);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $objects = [];
        foreach ($collection as $objectModel) {
            $objects[] = $objectModel;
        }
        $searchResults->setItems($objects);
        return $searchResults;
    }
}
