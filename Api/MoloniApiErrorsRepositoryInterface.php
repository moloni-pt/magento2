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

interface MoloniApiErrorsRepositoryInterface
{
    public function hasError();

    /**
     * @param string $title
     * @param string $message
     * @param string $where
     * @param array|false $received
     * @param array|false $sent
     * @return false
     */
    public function throwError(string $title, string $message, string $where, $received = false, $sent = false): bool;

    /**
     * @param $order
     * @return array|false
     */
    public function getErrors($order);
}
