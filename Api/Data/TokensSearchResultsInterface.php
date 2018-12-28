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
     * @return \Invoicing\Moloni\Api\Data\TokensInterface[]
     */
    public function getItems();

    /**
     * @param \Invoicing\Moloni\Api\Data\TokensInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
