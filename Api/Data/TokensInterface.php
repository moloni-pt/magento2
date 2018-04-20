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

    const ID            = 'id';
    const ACCESS_TOKEN  = 'access_token';
    const REFRESH_TOKEN = 'refresh_token';
    const COMPANY_ID    = 'company_id';
    const EXPIRE_DATE   = 'expire_date';
    const LOGIN_DATE    = 'login_date';
    
    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();
    
    /**
     * Get Access Token
     *
     * @return string|null
     */
    public function getAccessToken();
    
    /**
     * Get Refresh Token
     *
     * @return string|null
     */
    public function getRefreshToken();
    
    /**
     * Get Company Id
     *
     * @return int|null
     */
    public function getCompanyId();
    
    /**
     * Get Expire Date
     *
     * @return date|null
     */
    public function getExpireDate();
    
    /**
     * Get Login Date
     *
     * @return date|null
     */
    public function getLoginDate();
    
    
    /**
     * Set Id
     *
     * @return string|null
     */
    public function setId($id);
    
    /**
     * Set Access Token
     *
     * @return string|null
     */
    public function setAccessToken($access_token);
    
    /**
     * Set Refresh Token
     *
     * @return string|null
     */
    public function setRefreshToken($refresh_token);
    
    /**
     * Get Company Id
     *
     * @return int|null
     */
    public function setCompanyId($company_id);
    
    /**
     * Get Expire Date
     *
     * @return date|null
     */
    public function setExpireDate($expire_date);
    
    /**
     * Set Login Date
     *
     * @return date|null
     */
    public function setLoginDate($login_date);
}
