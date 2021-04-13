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
use Invoicing\Moloni\Api\Data\SettingsInterface;
use Invoicing\Moloni\Api\Data\TokensInterface;
use Invoicing\Moloni\Api\SettingsRepositoryInterface;
use Invoicing\Moloni\Model\ResourceModel\Settings as ObjectResourceModel;
use Invoicing\Moloni\Model\ResourceModel\Settings\CollectionFactory;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Psr\Log\LoggerInterface;

class SettingsRepository implements SettingsRepositoryInterface
{
    public SettingsFactory $objectFactory;
    public ObjectResourceModel $objectResourceModel;
    public CollectionFactory $collectionFactory;
    public SearchResultsInterfaceFactory $searchResultsFactory;
    public SearchCriteriaBuilder $searchCriteriaBuilder;
    public LoggerInterface $logger;

    private array $settingsResults = [];

    /**
     * SettingsRepository constructor.
     * @param ObjectResourceModel $objectResourceModel
     * @param SettingsFactory $objectFactory
     * @param CollectionFactory $collectionFactory
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        ObjectResourceModel $objectResourceModel,
        SettingsFactory $objectFactory,
        CollectionFactory $collectionFactory,
        SearchResultsInterfaceFactory $searchResultsFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        LoggerInterface $logger
    )
    {
        $this->objectFactory = $objectFactory;
        $this->objectResourceModel = $objectResourceModel;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function save($model): int
    {
        $this->settingsResults = [];
        try {
            $this->objectResourceModel->save($model);
        } catch (AlreadyExistsException | Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        return $model->getId();
    }

    /**
     * @param $companyId int
     * @param $label string
     * @param $value int|string|array
     *
     * @return SettingsInterface|TokensInterface|Settings|ExtensibleDataInterface
     */
    public function saveSetting(int $companyId, string $label, $value)
    {
        $obj = $this->getByCompanyLabel($companyId, $label);

        if (is_array($value)) {
            $value = json_encode($value);
        }

        $obj->setCompanyId($companyId);
        $obj->setStoreId(0);
        $obj->setLabel($label);
        $obj->setValue(($value === 'required') ? '' : $value);


        $this->save($obj);
        return $obj;
    }

    /**
     * @inheritdoc
     */
    public function getById($id): AbstractModel
    {
        $object = $this->objectFactory->create();
        $this->objectResourceModel->load($object, $id);

        if (!$object || !$object->getId()) {
            throw new NoSuchEntityException(__('Setting not found'));
        }

        return $object;
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

    /**
     * @return mixed
     */
    public function newOption()
    {
        return $this->objectFactory->create();
    }

    /**
     * @param $companyId int
     *
     * @return bool|array
     */
    public function getSettingsByCompany(int $companyId)
    {
        if (is_array($this->settingsResults)) {
            if (!isset($this->settingsResults[$companyId])) {
                $filter = $this->searchCriteriaBuilder->addFilter("company_id", $companyId)->create();
                $list = $this->getList($filter);

                if ($list->getTotalCount() > 0) {
                    foreach ($list->getItems() as $option) {
                        $this->settingsResults[$companyId][$option['label']] = $option['value'];
                    }

                    return $this->settingsResults[$companyId];
                }

                return false;
            }

            return $this->settingsResults[$companyId];
        }

        return false;
    }

    /**
     * @param $companyId
     * @param $label
     * @return SettingsInterface
     * @throws NoSuchEntityException
     */
    public function getByCompanyLabel($companyId, $label)
    {
        $_filter = $this->searchCriteriaBuilder
            ->addFilter("company_id", $companyId)
            ->addFilter("label", $label)
            ->setPageSize("1")
            ->create();

        $list = $this->getList($_filter);

        if ($list->getTotalCount() > 0) {
            $id = (int)$list->getItems()[0]['option_id'];
            return $this->getById($id);
        }

        return $this->objectFactory->create();
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $criteria): SearchResultsInterface
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $collection = $this->objectFactory->create()->getCollection();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }

        $searchResults->setTotalCount($collection->getSize());
        $sortOrdersData = $criteria->getSortOrders();
        if ($sortOrdersData) {
            foreach ($sortOrdersData as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }

        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $searchResults->setItems($collection->getData());

        return $searchResults;
    }
}
