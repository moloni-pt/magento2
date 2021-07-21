<?php

namespace Invoicing\Moloni\Libraries\MoloniLibrary\Controllers;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory class for @see \Invoicing\Moloni\Libraries\MoloniLibrary\Controllers\Documents
 */
class DocumentsFactory
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
    public function __construct(ObjectManagerInterface $objectManager, string $instanceName = '\\Invoicing\\Moloni\\Libraries\\MoloniLibrary\\Controllers\\Documents')
    {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return Documents
     */
    public function create(array $data = []): Documents
    {
        return $this->_objectManager->create($this->_instanceName, $data);
    }
}
