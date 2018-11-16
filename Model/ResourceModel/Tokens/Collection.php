<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Invoicing\Moloni\Model\ResourceModel\Tokens;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'invoicing_moloni_tokens_collection';
    protected $_eventObject = 'tokens_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Invoicing\Moloni\Model\Tokens', 'Invoicing\Moloni\Model\ResourceModel\Tokens');
    }

}
