<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Invoicing\Moloni\Model\ResourceModel\Settings;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'option_id';
    protected $_eventPrefix = 'moloni_settings_collection';
    protected $_eventObject = 'settings_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Invoicing\Moloni\Model\Settings', 'Invoicing\Moloni\Model\ResourceModel\Settings');
    }
}
