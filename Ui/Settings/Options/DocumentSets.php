<?php

namespace Invoicing\Moloni\Ui\Settings\Options;

use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;
use Magento\Framework\Data\OptionSourceInterface;

class DocumentSets implements OptionSourceInterface
{
    private $moloni;

    /**
     * DocumentSets constructor.
     *
     * @param $moloni Moloni
     */
    public function __construct(Moloni $moloni)
    {
        $this->moloni = $moloni;
    }

    /**
     * Retrieve options array.
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $result = [];

        $documentSets = $this->moloni->documentSets->getAll();

        $result[] = [
            'value' => '',
            'label' => __('Escolher uma opção')
        ];

        if ($documentSets && is_array($documentSets)) {
            foreach ($documentSets as $documentSet) {
                if (!$documentSet['is_invisible']) {
                    $result[] = [
                        "value" => $documentSet['document_set_id'],
                        "label" => $documentSet['name']
                    ];
                }
            }
        }

        return $result;
    }
}
