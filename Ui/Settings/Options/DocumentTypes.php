<?php

namespace Invoicing\Moloni\Ui\Settings\Options;

use Magento\Framework\Data\OptionSourceInterface;

class DocumentTypes implements OptionSourceInterface
{

    /**
     * Retrieve options array.
     * @return array
     */
    public function toOptionArray(): array
    {
        $result = [];
        $result[] = ['label' => __('Fatura'), 'value' => 'invoices'];
        $result[] = ['label' => __('Fatura/Recibo'), 'value' => 'invoiceReceipts'];
        $result[] = ['label' => __('Fatura simplificada'), 'value' => 'simplifiedInvoices'];
        $result[] = ['label' => __('Guia de transporte'), 'value' => 'billsOfLading'];
        $result[] = ['label' => __('Nota de encomenda'), 'value' => 'deliveryNotes'];
        $result[] = ['label' => __('Orçamento'), 'value' => 'estimates'];


        return $result;
    }
}
