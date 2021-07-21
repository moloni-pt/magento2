<?php

namespace Invoicing\Moloni\Ui\Settings\Options;

use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;
use Magento\Framework\Data\OptionSourceInterface;

class Countries implements OptionSourceInterface
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
    public function toOptionArray(): array
    {
        $result = [];

        $countries = $this->moloni->countries->getAll();

        $result[] = [
            'value' => '',
            'label' => __('Escolher um país')
        ];

        if ($countries && is_array($countries)) {
            foreach ($countries as $country) {
                $result[] = [
                    "value" => $country['country_id'],
                    "label" => $country['name'] . ' (' . strtoupper($country['iso_3166_1']) . ')'
                ];
            }
        }

        return $result;
    }
}
