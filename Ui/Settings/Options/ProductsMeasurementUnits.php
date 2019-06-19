<?php

namespace Invoicing\Moloni\Ui\Settings\Options;

use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;
use Magento\Framework\Data\OptionSourceInterface;

class ProductsMeasurementUnits implements OptionSourceInterface
{
    private $moloni;

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
     */
    public function toOptionArray()
    {
        $result = [];

        $measurementUnits = $this->moloni->measurementUnits->getAll();


        if ($measurementUnits && is_array($measurementUnits)) {
            foreach ($measurementUnits as $measurementUnit) {
                $unit = [
                    "value" => $measurementUnit['unit_id'],
                    "label" => $measurementUnit['name'] . " (" . $measurementUnit['short_name'] . ")"
                ];

                if ($measurementUnit['name'] == 'Unidade') {
                    array_unshift($result, $unit);
                    continue;
                }

                $result[] = $unit;
            }
        } else {
            $result[] = [
                'value' => '',
                'label' => __('Verifique as unidades de medida na sua conta Moloni')
            ];
        }

        return $result;
    }
}