<?php

namespace Invoicing\Moloni\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class SalesOrderViewGridUrl extends Column
{

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['view_url'])) {
                    $html = "<a href='" . $item['view_url'] . "' target='_BLANK'>";
                    $html .= $item['status'] > 0 ? __('Ver') : __('Editar');
                    $html .= "</a>";
                    $item['view_url'] = $html;
                }

                if (isset($item['download_url']) && !empty($item['download_url'])) {
                    $html = "<a href='" . $item['download_url'] . "' target='_BLANK'>";
                    $html .= __('Descarregar');
                    $html .= "</a>";
                    $item['download_url'] = $html;
                } else {
                    $item['download_url'] = '-';
                }

            }
        }

        return $dataSource;
    }
}
