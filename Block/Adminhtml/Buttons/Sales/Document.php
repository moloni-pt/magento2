<?php

namespace Invoicing\Moloni\Block\Adminhtml\Buttons\Sales;

use Magento\Sales\Block\Adminhtml\Order\View as OrderView;
use Magento\Framework\UrlInterface;
use Magento\Framework\AuthorizationInterface;
use Invoicing\Moloni\Model\DocumentsRepository;
use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni as MoloniLibrary;

class Document
{
    /** @var \Magento\Framework\UrlInterface */
    protected $urlBuilder;

    /** @var \Magento\Framework\AuthorizationInterface */
    protected $authorization;

    /**
     * @var DocumentsRepository
     */
    private $documentsRepository;

    public function __construct(
        UrlInterface $urlBuilder,
        AuthorizationInterface $authorization,
        DocumentsRepository $documentsRepository,
        MoloniLibrary $moloni

    )
    {
        $this->documentsRepository = $documentsRepository;
        $this->urlBuilder = $urlBuilder;
        $this->moloni = $moloni;

        $this->authorization = $authorization;
    }

    public function beforeSetLayout(OrderView $view)
    {

        $orderId = $view->getOrderId();
        if (!$this->hasMoloniLog($orderId)) {
            $url = $this->urlBuilder->getUrl('moloni/documents/create', ['order_id' => $orderId]);

            $view->addButton(
                'moloni_create_document',
                [
                    'label' => __('Gerar documento Moloni'),
                    'sort_order' => 2,
                    'on_click' => 'window.open( \'' . $url . '\')',
                ]
            );
        }
    }

    /**
     * @param int $orderId
     * @return bool|\Invoicing\Moloni\Api\Data\DocumentsInterface[]|\Magento\Framework\Api\AbstractExtensibleObject[]
     */
    private function hasMoloniLog($orderId)
    {
        $hasDocument = $this->documentsRepository->getByOrderId($orderId);
        if (!$hasDocument) {
            return false;
        }
        return $hasDocument;
    }
}