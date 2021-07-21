<?php

namespace Invoicing\Moloni\Block\Adminhtml\Buttons\Sales;

use Invoicing\Moloni\Api\Data\DocumentsInterface;
use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni as MoloniLibrary;
use Invoicing\Moloni\Model\DocumentsRepository;
use Magento\Framework\Api\AbstractExtensibleObject;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\UrlInterface;
use Magento\Sales\Block\Adminhtml\Order\View as OrderView;

class Document
{
    /** @var UrlInterface */
    protected $urlBuilder;

    /** @var AuthorizationInterface */
    protected $authorization;

    /**
     * @var DocumentsRepository
     */
    private $documentsRepository;
    /**
     * @var MoloniLibrary
     */
    protected $moloni;

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
     * @return bool|DocumentsInterface[]|AbstractExtensibleObject[]
     */
    private function hasMoloniLog(int $orderId)
    {
        $hasDocument = $this->documentsRepository->getByOrderId($orderId);
        if (!$hasDocument) {
            return false;
        }
        return $hasDocument;
    }
}
