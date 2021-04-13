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
     * @return SettingsInterface[]
     */
    public function getItems(): array;

    /**
     * @param SettingsInterface[] $items
     * @return $this
     */
    public function setItems(array $items): SettingsSearchResultsInterface;
}
