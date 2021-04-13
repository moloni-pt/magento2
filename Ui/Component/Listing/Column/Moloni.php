<?php

namespace Invoicing\Moloni\Ui\Component\Listing\Column;

use Invoicing\Moloni\Api\Data\DocumentsInterface;
use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni as MoloniLibrary;
use Invoicing\Moloni\Model\DocumentsRepository;
use Magento\Framework\Api\AbstractExtensibleObject;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Moloni extends Column
{
    protected $searchCriteria;

    /**
     * @var UrlInterface
     */
    private UrlInterface $urlBuilder;

    /**
     * @var DocumentsRepository
     */
    private DocumentsRepository $documentsRepository;

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

    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['entity_id'])) {
                    try {
                        $item[$this->getData('name')] = $this->getOptions($item['entity_id']);
                    } catch (\JsonException $e) {
                    }
                }
            }
        }

        return $dataSource;
    }

    /**
     * @param int $orderId
     * @return array
     * @throws \JsonException
     */
    private function getOptions(int $orderId): array
    {
        $moloniLog = $this->hasMoloniLog($orderId);
        if (!$moloniLog) {
            return $this->getMoloniCreateObject($orderId);
        }

        return $this->getMoloniMoreActionsObject($moloniLog, $orderId);
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

    /**
     * @param int $orderId
     * @return array
     */
    private function getMoloniCreateObject(int $orderId): array
    {
        return [
            'create' => [
                'href' => $this->urlBuilder->getUrl("moloni/documents/create", ['order_id' => $orderId]),
                'label' => __('Gerar'),
                'target' => '_BLANK'
            ],
        ];
    }

    /**
     * @param $moloniLog
     * @param $orderId
     * @return array
     * @throws \JsonException
     */
    private function getMoloniMoreActionsObject($moloniLog, $orderId): array
    {
        $options = [];
        if (!$this->moloni->checkActiveSession()) {
            return $options;
        }

        $documentId = $moloniLog[0]->getInvoiceId();
        $moloniDocument = $this->moloni->documents->getOne(['document_id' => $documentId]);
        if ($moloniDocument) {
            $documentTypeId = $moloniDocument['document_type']['document_type_id'];
            if ((int)$moloniDocument['status'] === 1) {
                $moloniDocument = $this->moloni->documents->setDocumentType($documentTypeId);

                $options = [
                    'download' => [
                        'href' => $moloniDocument->getDownloadUrl(['document_id' => $documentId]),
                        'label' => __('Descarregar documento'),
                        'target' => '_BLANK'
                    ],
                    'view' => [
                        'href' => $moloniDocument->getViewUrl($documentId),
                        'label' => __('Ver documento'),
                        'target' => '_BLANK'
                    ],
                    'create' => [
                        'href' => $this->urlBuilder->getUrl(
                            "moloni/documents/create",
                            ['order_id' => $moloniLog[0]->getOrderId(), 'force' => 1]
                        ),
                        'label' => __('Gerar novamente'),
                        'target' => '_BLANK'
                    ],
                ];
            } elseif ((int)$moloniDocument['status'] === 0) {
                $moloniDocumentEditUrl = $this->moloni->documents->setDocumentType($documentTypeId)
                    ->getEditUrl($documentId);

                $options = [
                    'edit' => [
                        'href' => $moloniDocumentEditUrl,
                        'label' => __('Editar documento'),
                        'target' => '_BLANK'
                    ],
                    'create' => [
                        'href' => $this->urlBuilder->getUrl(
                            "moloni/documents/create",
                            ['order_id' => $moloniLog[0]->getOrderId(), 'force' => 1]
                        ),
                        'label' => __('Gerar novamente'),
                        'target' => '_BLANK'
                    ],
                ];
            }
        } else {
            return [
                'create' => [
                    'href' => $this->urlBuilder->getUrl("moloni/documents/create", ['order_id' => $orderId]),
                    'label' => __('Gerar'),
                    'target' => '_BLANK'
                ],
            ];
        }

        return $options;
    }
}
