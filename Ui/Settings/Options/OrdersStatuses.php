<?php

namespace Invoicing\Moloni\Ui\Settings\Options;

use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;

class OrdersStatuses implements OptionSourceInterface
{
    private Moloni $moloni;
    private CollectionFactory $statusCollectionFactory;

    /**
     * DocumentSets constructor.
     * @param CollectionFactory $statusCollectionFactory
     * @param Moloni $moloni
     */
    public function __construct(
        CollectionFactory $statusCollectionFactory,
        Moloni $moloni
    )
    {
        $this->statusCollectionFactory = $statusCollectionFactory;
        $this->moloni = $moloni;
    }

    /**
     * Retrieve options array.
     * @return array
     */
    public function toOptionArray(): array
    {
        return $this->getStatusOptions();
    }

    public function getStatusOptions(): array
    {
        return $this->statusCollectionFactory->create()->toOptionArray();
    }
}
