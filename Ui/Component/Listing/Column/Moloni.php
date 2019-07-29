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
            return $this->getMoloniMoreActionsObject($moloniLog);
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
                'label' => __('Gerar'),
                'target' => '_BLANK'
            ],
        ];
    }

    /**
     * @param \Invoicing\Moloni\Api\Data\DocumentsInterface[]|\
     * Magento\Framework\Api\AbstractExtensibleObject[] $moloniLog
     * @return array
     */
    private function getMoloniMoreActionsObject($moloniLog)
    {
        $options = [];
        if (!$this->moloni->checkActiveSession()) {
            return $options;
        }

        $documentId = $moloniLog[0]->getInvoiceId();
        $moloniDocument = $this->moloni->documents->getOne(['document_id' => $documentId]);
        if ($moloniDocument) {
            $documentTypeId = $moloniDocument['document_type']['document_type_id'];
            if ($moloniDocument['status'] == 1) {
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
            } elseif ($moloniDocument['status'] == 0) {
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
            } else {
                $options = [
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
        }

        return $options;
    }
}
