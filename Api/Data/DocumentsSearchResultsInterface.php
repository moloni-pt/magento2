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
     * @return DocumentsInterface[]
     */
    public function getItems(): array;

    /**
     * @param DocumentsInterface[] $items
     * @return $this
     */
    public function setItems(array $items): DocumentsSearchResultsInterface;
}
