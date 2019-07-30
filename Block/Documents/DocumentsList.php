<?php

namespace Invoicing\Moloni\Block\Documents;

use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\OrderRepositoryInterface;

class DocumentsList extends \Magento\Framework\View\Element\Template
{

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var Moloni
     */
    protected $moloni;

    /**
     * DocumentsList constructor.
     * @param Context $context
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Context $context,
        Moloni $moloni,
        OrderRepositoryInterface $orderRepository
    )
    {
        $this->moloni = $moloni;
        $this->orderRepository = $orderRepository;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrder()
    {
        $orderId = $this->getRequest()->getParam("order_id");
        return $this->orderRepository->get($orderId);
    }

    public function getOrderDocuments()
    {
        $documentsList = [];
        $incrementId = $this->getOrder()->getIncrementId();
        if (!empty($incrementId)) {
            if ($this->moloni->checkActiveSession()) {
                $moloniDocuments = $this->moloni->documents->setDocumentType('documents');
                $documentsList = $moloniDocuments->getAll(['status' => 1, 'your_reference' => $incrementId]);

                foreach ($documentsList as &$document) {
                    if ($document['status'] == 1) {
                        $currentDocumentType = $moloniDocuments->setDocumentType($document['document_type_id']);
                        $documentDownloadUrl = $currentDocumentType->getDownloadUrl(['document_id' => $document['document_id']]);
                        $document['document_type_name'] = $currentDocumentType->documentTypeName;
                        
                        if ($documentDownloadUrl) {
                            $document['download_url'] = $documentDownloadUrl;
                        }
                    }
                }
            }

        }

        return $documentsList;
    }
}
