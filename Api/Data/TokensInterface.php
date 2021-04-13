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

/**
 * @method setDeveloperId(mixed $getParam)
 * @method setRedirectUri(mixed $getParam)
 * @method setSecretToken(mixed $getParam)
 * @method getAccessToken()
 * @method setCompanyId(mixed $getParam)
 * @method setStoreId(mixed $getParam)
 * @method setLabel(mixed $getParam)
 * @method setValue(mixed $getParam)
 * @method save()
 * @method delete()
 * @method getDeveloperId()
 * @method getRedirectUri()
 * @method getSecretToken()
 */
interface TokensInterface
{

    public const ID = 'id';
    public const DEVELOPER_ID = 'developer_id';
    public const REDIRECT_URI = 'redirect_uri';
    public const ACCESS_TOKEN = 'access_token';
    public const SECRET_TOKEN = 'secret_token';
    public const REFRESH_TOKEN = 'refresh_token';
    public const COMPANY_ID = 'company_id';
    public const EXPIRE_DATE = 'expire_date';
    public const LOGIN_DATE = 'login_date';
}
