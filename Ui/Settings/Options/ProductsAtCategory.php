<?php

namespace Invoicing\Moloni\Ui\Settings\Options;

use Magento\Framework\Data\OptionSourceInterface;

class ProductsAtCategory implements OptionSourceInterface
{

    /**
     * Retrieve options array.
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];
        $result[] = ['label' => __('Mercadorias'), 'value' => 'M'];
        $result[] = ['label' => __('Matérias-primas, subsidiárias e de consumo'), 'value' => 'P'];
        $result[] = ['label' => __('Produtos acabados e intermédios'), 'value' => 'A'];
        $result[] = ['label' => __('Subprodutos, desperdícios e refugos'), 'value' => 'S'];
        $result[] = ['label' => __('Produtos e trabalhos em curso'), 'value' => 'T'];


        return $result;
    }
}
