<?php
/**
 * Created by PhpStorm.
 * User: nuno_
 * Date: 11/12/2018
 * Time: 13:56
 */

namespace Invoicing\Moloni\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface TokensSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return TokensInterface[]
     */
    public function getItems(): array;

    /**
     * @param TokensInterface[] $items
     * @return $this
     */
    public function setItems(array $items): TokensSearchResultsInterface;
}
