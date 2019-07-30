<?php

namespace Invoicing\Moloni\Block\Adminhtml\Order\View\Tab;

class Documents extends \Magento\Framework\View\Element\Text\ListText implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Documentos Moloni');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Documentos Moloni');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
