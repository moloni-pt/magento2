<?php
/**
 * Created by PhpStorm.
 * User: Nuno
 * Date: 30/07/2019
 * Time: 16:20
 */

namespace Invoicing\Moloni\Ui\DataProvider;

use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Ui\DataProvider\AddFieldToCollectionInterface;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;

class OrderDocumentsProvider extends DataProvider
{
    /**
     * @var AddFieldToCollectionInterface[]
     */
    protected array $addFieldStrategies;

    /**
     * @var AddFilterToCollectionInterface[]
     */
    protected array $addFilterStrategies;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var OrderRepositoryInterface
     */
    protected OrderRepositoryInterface $orderRepository;

    /**
     * @var Moloni
     */
    private Moloni $moloni;

    /**
     * OrderDocumentsProvider constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param ReportingInterface $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param Moloni $moloni
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        OrderRepositoryInterface $orderRepository,
        Moloni $moloni,
        array $meta = [],
        array $data = []
    )
    {
        $this->request = $request;
        $this->moloni = $moloni;
        $this->orderRepository = $orderRepository;
        $this->data = $data;
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );
    }

    /**
     * @return OrderInterface
     */
    public function getOrder(): OrderInterface
    {
        $orderId = $this->request->getParam("order_id");
        return $this->orderRepository->get($orderId);
    }

    /***
     * @return array
     * @throws \JsonException
     */
    public function getData(): array
    {

        $documentsList = [];
        $incrementId = $this->getOrder()->getIncrementId();
        if (!empty($incrementId)) {
            if ($this->moloni->checkActiveSession()) {
                $moloniDocuments = $this->moloni->documents->setDocumentType('documents');
                $documentsListSearch = $moloniDocuments->getAll(['your_reference' => $incrementId]);

                if (is_array($documentsListSearch)) {
                    $documentsList = $documentsListSearch;
                    foreach ($documentsList as &$document) {
                        $currentDocumentType = $moloniDocuments->setDocumentType($document['document_type_id']);
                        $document['document_type_name'] = $currentDocumentType->documentTypeName;
                        $document['document_set'] =
                            $currentDocumentType->documentTypeName . ' ' .
                            $document['document_set_name'] . '/' . $document['number'];

                        $document['document_date'] = date("Y-m-d", strtotime($document['date']));
                        $document['net_value'] .= '€';
                        $document['download_url'] = '';

                        if ((int)$document['status'] === 1) {
                            $document['status_name'] = __("Fechado");
                            $document['view_url'] = $currentDocumentType->getViewUrl($document['document_id']);
                            $documentDownloadUrl = $currentDocumentType->getDownloadUrl(
                                ['document_id' => $document['document_id']]
                            );

                            if ($documentDownloadUrl) {
                                $document['download_url'] = $documentDownloadUrl;
                            }
                        } elseif ((int)$document['status'] === 0) {
                            $document['status_name'] = __("Rascunho");
                            $document['view_url'] = $currentDocumentType->getEditUrl($document['document_id']);
                        } else {
                            $document['status_name'] = __("Anulado");
                            $document['view_url'] = $currentDocumentType->getViewUrl($document['document_id']);
                        }
                    }
                }
            }
        }

        return [
            'totalRecords' => count($documentsList),
            'items' => $documentsList,
        ];
    }

    public function setLimit($offset, $size)
    {
    }

    public function addOrder($field, $direction)
    {
    }

    public function addFilter(Filter $filter)
    {
    }
}
