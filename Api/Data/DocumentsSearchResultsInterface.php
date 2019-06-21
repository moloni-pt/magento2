<?php
/**
 * Created by PhpStorm.
 * User: nuno_
 * Date: 11/12/2018
 * Time: 13:56
 */

namespace Invoicing\Moloni\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface DocumentsSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return \Invoicing\Moloni\Api\Data\DocumentsInterface[]
     */
    public function getItems();

    /**
     * @param \Invoicing\Moloni\Api\Data\DocumentsInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
