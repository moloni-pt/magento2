<?php

namespace Invoicing\Moloni\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use Invoicing\Moloni\Model\DocumentsRepository;
use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni as MoloniLibrary;

class Moloni extends Column
{
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
        UrlInterface $urlBuilder,
        DocumentsRepository $documentsRepository,
        MoloniLibrary $moloni,
        array $components = [],
        array $data = []
    )
    {
        $this->documentsRepository = $documentsRepository;
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

    /**
     * @param int $orderId
     * @return array
     */
    private function getOptions($orderId)
    {
        $moloniLog = $this->hasMoloniLog($orderId);
        if (!$moloniLog) {
            return $this->getMoloniCreateObject($orderId);
        } else {
            return $this->getMoloniMoreActionsObject();
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

    /**
     * @param int $orderId
     * @return array
     */
    private function getMoloniCreateObject($orderId)
    {
        return [
            'create' => [
                'href' => $this->urlBuilder->getUrl("moloni/documents/create", ['order_id' => $orderId]),
                'label' => __('Gerar')
            ],
        ];
    }

    /**
     * @return array
     */
    private function getMoloniMoreActionsObject()
    {
        return [];
    }
}
