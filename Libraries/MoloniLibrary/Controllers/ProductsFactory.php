<?php

namespace Invoicing\Moloni\Libraries\MoloniLibrary\Controllers;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory class for @see \Invoicing\Moloni\Libraries\MoloniLibrary\Controllers\Products
 */
class ProductsFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected $_instanceName = null;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $instanceName = '\\Invoicing\\Moloni\\Libraries\\MoloniLibrary\\Controllers\\Products'
    )
    {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return Products
     */
    public function create(array $data = [])
    {
        return $this->_objectManager->create($this->_instanceName, $data);
    }
}
