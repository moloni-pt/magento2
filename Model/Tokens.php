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

use Invoicing\Moloni\Api\Data\TokensInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * @method  setDeveloperId(mixed $getParam)
 * @method  setRedirectUri(mixed $getParam)
 * @method  setSecretToken(mixed $getParam)
 * @method  getAccessToken()
 * @method  setCompanyId(mixed $getParam)
 * @method  setStoreId(mixed $getParam)
 * @method  setLabel(mixed $getParam)
 * @method  setValue(mixed $getParam)
 * @method  getDeveloperId()
 * @method  getRedirectUri()
 * @method  getSecretToken()
 */
class Tokens extends AbstractModel implements
    IdentityInterface,
    TokensInterface
{

    public const CACHE_TAG = 'moloni_tokens';

    public string $cacheTag = 'moloni_tokens';
    public string $eventPrefix = 'moloni_tokens';
    public array $tokensRow = [];

    /** @noinspection MagicMethodsValidityInspection */
    public function _construct()
    {
        $this->_init(ResourceModel\Tokens::class);
    }

    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
