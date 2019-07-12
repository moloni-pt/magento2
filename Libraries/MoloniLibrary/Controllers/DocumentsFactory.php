<?php
namespace Invoicing\Moloni\Libraries\MoloniLibrary\Controllers;

/**
 * Factory class for @see \Invoicing\Moloni\Libraries\MoloniLibrary\Controllers\Documents
 */
class DocumentsFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
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
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, $instanceName = '\\Invoicing\\Moloni\\Libraries\\MoloniLibrary\\Controllers\\Documents')
    {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Invoicing\Moloni\Libraries\MoloniLibrary\Controllers\Documents
     */
    public function create(array $data = [])
    {
        return $this->_objectManager->create($this->_instanceName, $data);
    }
}
