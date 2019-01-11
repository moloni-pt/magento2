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

    private $settingsResults = false;

    public function __construct(
        ObjectResourceModel $objectResourceModel,
        SettingsFactory $objectFactory,
        CollectionFactory $collectionFactory,
        SearchResultsInterfaceFactory $searchResultsFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->objectFactory = $objectFactory;
        $this->objectResourceModel = $objectResourceModel;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param SettingsInterface $setting
     * @return int
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(SettingsInterface $setting)
    {
        $this->settingsResults = false;
        $this->objectResourceModel->save($setting);
        return $setting->getId();
    }

    public function saveSetting($companyId, $label, $value)
    {
        $obj = $this->getByCompanyLabel($companyId, $label);

        $obj->setCompanyId($companyId);
        $obj->setStoreId(0);
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

    public function newOption()
    {
        return $this->objectFactory->create();
    }

    /**
     * @param $companyId
     * @return bool|array
     */
    public function getSettingsByCompany($companyId)
    {
        if (!$this->settingsResults[$companyId]) {
            $_filter = $this->searchCriteriaBuilder->addFilter("company_id", $companyId)->create();
            $list = $this->getList($_filter);

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
     * @param $companyId
     * @param $label
     * @return \Invoicing\Moloni\Api\Data\SettingsInterface
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
