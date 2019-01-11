<?php
/**
 * Created by PhpStorm.
 * User: nuno_
 * Date: 11/12/2018
 * Time: 13:56
 */

namespace Invoicing\Moloni\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface SettingsSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return \Invoicing\Moloni\Api\Data\SettingsInterface[]
     */
    public function getItems();

    /**
     * @param \Invoicing\Moloni\Api\Data\SettingsInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
