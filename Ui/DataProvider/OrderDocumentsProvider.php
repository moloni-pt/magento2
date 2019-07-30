<?php
/**
 * Created by PhpStorm.
 * User: Nuno
 * Date: 30/07/2019
 * Time: 16:20
 */

namespace Invoicing\Moloni\Ui\DataProvider;

use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\App\Request\Http;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderDocumentsProvider extends AbstractDataProvider
{
    /**
     * @var \Magento\Ui\DataProvider\AddFieldToCollectionInterface[]
     */
    protected $addFieldStrategies;

    /**
     * @var \Magento\Ui\DataProvider\AddFilterToCollectionInterface[]
     */
    protected $addFilterStrategies;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var Moloni
     */
    private $moloni;

    /**
     * Construct
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param Http $request
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Http $request,
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
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrder()
    {
        $orderId = $this->request->getParam("order_id");
        return $this->orderRepository->get($orderId);
    }

    /***
     * @return array
     */
    public function getData()
    {
        $documentsList = [
            [
                'entity_name' => print_r($this->request->getParams(), true),
                'entity_vat' => print_r($this->data, true)
            ]
        ];

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

    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
    }
}