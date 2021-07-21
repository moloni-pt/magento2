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

use Exception;
use Invoicing\Moloni\Api\Data\TokensInterface;
use Invoicing\Moloni\Api\TokensRepositoryInterface;
use Invoicing\Moloni\Model\ResourceModel\Tokens as ObjectResourceModel;
use Invoicing\Moloni\Model\ResourceModel\Tokens\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;


/**
 * Class TokensRepository
 * @package Invoicing\Moloni\Model
 *
 */
class TokensRepository implements TokensRepositoryInterface
{
    public $objectFactory;
    public $objectResourceModel;
    public $collectionFactory;
    public $searchResultsFactory;
    public $searchCriteriaBuilder;

    /**
     * @var mixed
     */
    private $tokensRow;

    public function __construct(
        ObjectResourceModel $objectResourceModel,
        TokensFactory $objectFactory,
        CollectionFactory $collectionFactory,
        SearchResultsInterfaceFactory $searchResultsFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder
    )
    {
        $this->objectFactory = $objectFactory;
        $this->objectResourceModel = $objectResourceModel;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param TokensInterface|AbstractModel $model
     * @return int
     * @throws AlreadyExistsException
     */
    public function save($model): int
    {
        $this->objectResourceModel->save($model);
        return $model->getId();
    }

    /**
     * @inheritdoc
     */
    public function getById($id): AbstractModel
    {
        $tokens = $this->objectFactory->create();
        $this->objectResourceModel->load($tokens, $id);

        if (!$tokens->getId()) {
            throw new NoSuchEntityException(__('Tokens do not exist'));
        }

        return $tokens;
    }

    /**
     * @inheritdoc
     * @throws CouldNotDeleteException
     */
    public function delete($model): bool
    {
        try {
            $this->objectResourceModel->delete($model);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @inheritdoc
     * @throws CouldNotDeleteException
     */
    public function deleteById($id): bool
    {
        try {
            return $this->delete($this->getById($id));
        } catch (NoSuchEntityException $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
    }

    public function getTokens(): Tokens
    {

        if (empty($this->tokensRow)) {
            $_filter = $this->searchCriteriaBuilder
                ->setPageSize("1")
                ->create();
            $list = $this->getList($_filter);

            if ($list->getTotalCount() > 0) {
                $this->tokensRow = $list->getItems()[0];
            } else {
                $this->tokensRow = $this->objectFactory->create();
            }
        }

        return $this->tokensRow;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $criteria): SearchResultsInterface
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $collection = $this->collectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ?: 'eq';
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
