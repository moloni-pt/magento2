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
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;

use Invoicing\Moloni\Api\SettingsRepositoryInterface;
use Invoicing\Moloni\Model\ResourceModel\Settings as ObjectResourceModel;
use Invoicing\Moloni\Model\ResourceModel\Settings\CollectionFactory;
use Invoicing\Moloni\Api\Data\SettingsInterface;

class SettingsRepository implements SettingsRepositoryInterface
{
    public $objectFactory;
    public $objectResourceModel;
    public $collectionFactory;
    public $searchResultsFactory;
    public $searchCriteriaBuilder;
    public $logger;

    private $settingsResults = false;

    /**
     * SettingsRepository constructor.
     * @param ObjectResourceModel $objectResourceModel
     * @param SettingsFactory $objectFactory
     * @param CollectionFactory $collectionFactory
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        ObjectResourceModel $objectResourceModel,
        SettingsFactory $objectFactory,
        CollectionFactory $collectionFactory,
        SearchResultsInterfaceFactory $searchResultsFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->objectFactory = $objectFactory;
        $this->objectResourceModel = $objectResourceModel;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
    }

    /**
     * @param SettingsInterface $setting
     * @return int
     */
    public function save(SettingsInterface $setting)
    {
        $this->settingsResults = false;
        try {
            $this->objectResourceModel->save($setting);
        } catch (AlreadyExistsException $e) {
            $this->logger->critical($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
        return $setting->getId();
    }

    /**
     * @param $storeId
     * @param $companyId
     * @param $label
     * @param $value
     * @return SettingsInterface|\Magento\Framework\Model\AbstractModel
     */
    public function saveSetting($storeId, $companyId, $label, $value)
    {
        $obj = $this->getByCompanyLabel($storeId, $companyId, $label);

        $obj->setCompanyId($companyId);
        $obj->setStoreId($storeId);
        $obj->setLabel($label);
        $obj->setValue(($value == 'required') ? '' : $value);

        $this->save($obj);

        return $obj;
    }

    /**
     * @param $optionId
     * @return \Invoicing\Moloni\Api\Data\SettingsInterface int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($optionId)
    {
        $object = $this->objectFactory->create();
        $this->objectResourceModel->load($object, $optionId);

        if (!$object->getId()) {
            throw new NoSuchEntityException(__('Setting not found'));
        }

        return $object;
    }

    /**
     * @param SettingsInterface $option
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(SettingsInterface $option)
    {
        try {
            $this->objectResourceModel->delete($option);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @param $id
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
     * @return mixed
     */
    public function newOption()
    {
        return $this->objectFactory->create();
    }

    /**
     * @param $storeId
     * @param $companyId
     * @return bool|array
     */
    public function getSettingsByCompany($storeId, $companyId)
    {
        if (!$this->settingsResults[$companyId]) {
            $filter = $this->searchCriteriaBuilder
                ->addFilter("company_id", $companyId)
                ->addFilter('store_id', $storeId)
                ->create();
            $list = $this->getList($filter);

            if ($list->getTotalCount() > 0) {
                foreach ($list->getItems() as $option) {
                    $this->settingsResults[$companyId][$option['label']] = $option['value'];
                }
            } else {
                return false;
            }
        }

        return $this->settingsResults[$companyId];
    }

    /**
     * @param $storeId
     * @param $companyId
     * @param $label
     * @return \Invoicing\Moloni\Api\Data\SettingsInterface|\Magento\Framework\Model\AbstractModel
     */
    public function getByCompanyLabel($storeId, $companyId, $label)
    {
        $_filter = $this->searchCriteriaBuilder
            ->addFilter('store_id', $storeId)
            ->addFilter("company_id", $companyId)
            ->addFilter("label", $label)
            ->setPageSize("1")
            ->create();

        $list = $this->getList($_filter);

        if ($list->getTotalCount() > 0) {
            return $list->getItems()[0];
        } else {
            return $this->objectFactory->create();
        }
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
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
