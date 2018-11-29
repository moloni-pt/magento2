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

use Magento\Framework\Model\AbstractModel;

class Tokens extends AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{

    const CACHE_TAG = 'moloni_tokens';

    protected $_cacheTag = 'moloni_tokens';
    protected $_eventPrefix = 'moloni_tokens';
    protected $tokensRow;

    protected function _construct(array $data = [])
    {
        $this->_init(ResourceModel\Tokens::class);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getTokens()
    {
        if ($this->tokensRow == null) {
            $collection = $this->getCollection();

            if ($collection->getSize()) {
                $this->tokensRow = $collection->getFirstItem();
            }
        }

        return $this->tokensRow;
    }
}
