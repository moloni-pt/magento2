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

namespace Invoicing\Moloni\Api;

use Invoicing\Moloni\Api\Data\DocumentsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;

interface DocumentsRepositoryInterface
{
    /**
     * @param AbstractModel $model
     * @return int
     * @api
     */
    public function save(AbstractModel $model): int;

    /**
     * @param AbstractModel $model
     * @return bool
     * @api
     */
    public function delete(AbstractModel $model): bool;

    /**
     * @param int $id
     * @return bool
     * @api
     */
    public function deleteById(int $id): bool;

    /**
     * @param int $id
     * @return DocumentsInterface
     * @throws NoSuchEntityException
     * @api
     */
    public function getById(int $id): DocumentsInterface;

    /**
     * @param SearchCriteriaInterface $criteria
     * @return SearchResults
     * @api
     */
    public function getList(SearchCriteriaInterface $criteria): SearchResults;
}
