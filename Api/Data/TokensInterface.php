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

namespace Invoicing\Moloni\Api\Data;

interface TokensInterface
{

    const ID = 'id';
    const DEVELOPER_ID = 'developer_id';
    const REDIRECT_URI = 'redirect_uri';
    const ACCESS_TOKEN = 'access_token';
    const SECRET_TOKEN = 'secret_token';
    const REFRESH_TOKEN = 'refresh_token';
    const COMPANY_ID = 'company_id';
    const EXPIRE_DATE = 'expire_date';
    const LOGIN_DATE = 'login_date';

    /**
     * @return mixed
     */
    public function getId();

    /**
     * @param $id
     * @return $this
     */
    public function setId($id);

    /**
     * @return mixed
     */
    public function getCompanyId();

    /**
     * @param $id string
     * @return mixed
     */
    public function setCompanyId($id);

    /**
     * @return string[]
     */
    public function getTokens();

}
