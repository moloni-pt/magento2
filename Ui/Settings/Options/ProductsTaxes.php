<?php

namespace Invoicing\Moloni\Ui\Settings\Options;

use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;
use Magento\Framework\Data\OptionSourceInterface;

class ProductsTaxes implements OptionSourceInterface
{
    private Moloni $moloni;

    /**
     * DocumentSets constructor.
     * @param Moloni $moloni
     */
    public function __construct(Moloni $moloni)
    {
        $this->moloni = $moloni;
    }

    /**
     * Retrieve options array.
     * @return array
     * @throws \JsonException
     */
    public function toOptionArray(): array
    {
        $result = [];

        $taxes = $this->moloni->taxes->getAll();

        $result[] = [
            'value' => '',
            'label' => __('Os meus artigos têm taxas configuradas')
        ];

        if ($taxes && is_array($taxes)) {
            foreach ($taxes as $tax) {
                $result[] = [
                    "value" => $tax['tax_id'],
                    "label" => $tax['name'] . " (" . $tax['value'] . "%)"
                ];
            }
        }

        return $result;
    }
}
