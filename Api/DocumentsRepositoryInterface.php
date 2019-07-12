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

use Magento\Framework\Api\SearchCriteriaInterface;
use Invoicing\Moloni\Api\Data\DocumentsInterface;

interface DocumentsRepositoryInterface
{
    /**
     * @api
     * @param DocumentsInterface $model
     * @return \Invoicing\Moloni\Api\Data\DocumentsInterface
     */
    public function save(DocumentsInterface $model);

    /**
     * @api
     * @param DocumentsInterface $model
     * @return \Invoicing\Moloni\Api\Data\DocumentsInterface
     */
    public function delete(DocumentsInterface $model);

    /**
     * @api
     * @param \Invoicing\Moloni\Api\Data\DocumentsInterface $id
     * @return void
     */
    public function deleteById($id);

    /**
     * @api
     * @param int $id
     * @return \Invoicing\Moloni\Api\Data\DocumentsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * @api
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Invoicing\Moloni\Api\Data\DocumentsSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria);

}
