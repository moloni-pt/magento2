<?php

namespace Invoicing\Moloni\Block\Adminhtml\Buttons\Products;

use Magento\Catalog\Block\Adminhtml\Product\Edit;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class SyncButton
 */
class SyncButton extends Edit implements ButtonProviderInterface
{
    /**
     * Clear Cache button
     *
     * @return array
     */
    public function getButtonData(): array
    {
        $message = __('As alterações efectuadas serão perdidas, deseja continuar?');
        $syncUrl = $this->getUrl('moloni/sync/product', ['id' => $this->getProductId()]);

        return [
            'id' => 'moloni_sync_product',
            'label' => __('Sincronizar Moloni'),
            'on_click' => "confirmSetLocation('{$message}', '{$syncUrl}')",
            'class' => 'delete',
            'sort_order' => 0
        ];
    }
}
