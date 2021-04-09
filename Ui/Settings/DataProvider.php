<?php

namespace Invoicing\Moloni\Ui\Settings;

use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;

class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    public array $loadedData = [];
    private Moloni $moloni;

    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        Moloni $moloni,
        array $meta = [],
        array $data = []
    )
    {
        $this->moloni = $moloni;
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

    public function getData(): array
    {
        if (!empty($this->loadedData)) {
            return $this->loadedData;
        }

        $this->loadedData['0']['general'] = [
            "document_set_id" => $this->moloni->settings['document_set_id'],
            "document_type" => $this->moloni->settings['document_type'],
            "document_auto" => $this->moloni->settings['document_auto'],
            "document_status" => $this->moloni->settings['document_status'],
            "document_email" => $this->moloni->settings['document_email'],
            "shipping_details" => $this->moloni->settings['shipping_details'],
            "shipping_document" => $this->moloni->settings['shipping_document'],
            "delivery_departure_address" => $this->moloni->settings['delivery_departure_address'],
            "delivery_departure_city" => $this->moloni->settings['delivery_departure_city'],
            "delivery_departure_zip_code" => $this->moloni->settings['delivery_departure_zip_code'],
            "delivery_departure_country" => $this->moloni->settings['delivery_departure_country'],
        ];

        $this->loadedData['0']['products'] = [
            "products_at_category" => $this->moloni->settings['products_at_category'],
            "default_measurement_unit_id" => $this->moloni->settings['default_measurement_unit_id'],
            "products_tax" => $this->moloni->settings['products_tax'],
            "products_tax_exemption" => $this->moloni->settings['products_tax_exemption'],
            "shipping_tax" => $this->moloni->settings['shipping_tax'],
            "shipping_tax_exemption" => $this->moloni->settings['shipping_tax_exemption'],
            "products_auto_create" => $this->moloni->settings['products_auto_create'],
        ];

        $this->loadedData['0']['orders'] = [
            "orders_statuses" => $this->moloni->settings['orders_statuses'],
            "orders_since" => $this->moloni->settings['orders_since'],
        ];

        $this->loadedData['0']['sync'] = [
            "products_sync_stock" => $this->moloni->settings['products_sync_stock'],
            "products_sync_price" => $this->moloni->settings['products_sync_price'],
        ];

        /*if (is_array($this->moloni->settings) && !empty($this->moloni->settings)) {
            foreach ($this->moloni->settings as $option => $value) {
                // Lazy way for not setting each one individually
                $this->loadedData['0']['general'][$option] = $value;
                $this->loadedData['0']['products'][$option] = $value;
            }
        }*/

        return $this->loadedData;
    }
}
