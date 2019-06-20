<?php

namespace Invoicing\Moloni\Ui\Settings\Options;

use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;

class OrdersStatuses implements OptionSourceInterface
{
    private $moloni;
    private $statusCollectionFactory;

    /**
     * DocumentSets constructor.
     * @param CollectionFactory $statusCollectionFactory
     * @param Moloni $moloni
     */
    public function __construct(
        CollectionFactory $statusCollectionFactory,
        Moloni $moloni
    ) {
        $this->statusCollectionFactory = $statusCollectionFactory;
        $this->moloni = $moloni;
    }

    /**
     * Retrieve options array.
     * @return array
     */
    public function toOptionArray()
    {
        $result = $this->getStatusOptions();
        return $result;
    }

    public function getStatusOptions()
    {
        $options = $this->statusCollectionFactory->create()->toOptionArray();
        return $options;
    }
}
