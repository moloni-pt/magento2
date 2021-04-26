<?php

namespace Invoicing\Moloni\Ui\Settings\Options;

use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;
use JsonException;
use Magento\Framework\Data\OptionSourceInterface;

class ProductsTaxExemptions implements OptionSourceInterface
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
     * @throws JsonException
     */
    public function toOptionArray(): array
    {
        $result = [];

        $exemptions = $this->moloni->taxExemptions->getAll();

        $result[] = [
            'value' => '',
            'label' => __('Sem razão de isenção seleccionada')
        ];

        if ($exemptions && is_array($exemptions)) {
            foreach ($exemptions as $exemption) {
                $result[] = [
                    "value" => $exemption['code'],
                    "label" => $exemption['name']
                ];
            }
        }

        return $result;
    }
}
