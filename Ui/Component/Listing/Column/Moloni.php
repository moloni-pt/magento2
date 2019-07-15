<?php

namespace Invoicing\Moloni\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use Invoicing\Moloni\Model\DocumentsRepository;


class Moloni extends Column
{
    protected $orderRepository;
    protected $searchCriteria;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var DocumentsRepository
     */
    private $documentsRepository;

    /**
     * @var Moloni
     */
    private $moloni;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $criteria,
        UrlInterface $urlBuilder,
        DocumentsRepository $documentsRepository,
        Moloni $moloni,
        array $components = [],
        array $data = []
    )
    {
        $this->documentsRepository = $documentsRepository;
        $this->orderRepository = $orderRepository;
        $this->searchCriteria = $criteria;
        $this->urlBuilder = $urlBuilder;
        $this->moloni = $moloni;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['entity_id'])) {
                    $item[$this->getData('name')] = $this->getOptions($item['entity_id']);
                }
            }
        }

        return $dataSource;
    }

    private function getOptions($orderId)
    {
        $moloniLog = $this->hasMoloniLog($orderId);
        if (!$moloniLog) {
            return $this->getMoloniCreateObject($orderId);
        } else {
            return $this->getMoloniMoreActionsObject($moloniLog);
        }
    }

    private function hasMoloniLog($orderId)
    {
        $hasDocument = $this->documentsRepository->getByOrderId($orderId);
        if (!$hasDocument) {
            return false;
        }
        return $hasDocument;
    }

    private function getMoloniCreateObject($orderId)
    {
        return [
            'create' => [
                'href' => $this->urlBuilder->getUrl("moloni/documents/create", ['order_id' => $orderId]),
                'label' => __('Gerar')
            ],
        ];
    }

    private function getMoloniMoreActionsObject($moloniDocumentId)
    {

    }
}
