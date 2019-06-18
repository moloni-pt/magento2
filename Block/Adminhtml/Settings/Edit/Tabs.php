<?php

namespace Invoicing\Moloni\Block\Adminhtml\Settings\Edit;

use \Invoicing\Moloni\Block\Adminhtml\Settings\Edit\Tab\General;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('page_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Configurações'));
    }

    /**
     * @return \Magento\Backend\Block\Widget\Tabs
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'general_section',
            [
                'label' => __('Gerais'),
                'title' => __('Gerais'),
                'content' => $this->getLayout()->createBlock(General::class)->toHtml(),
                'active' => true
            ]
        );

        /* $this->addTab(
             'roles_section',
             [
                 'label' => __('User Role'),
                 'title' => __('User Role'),
                 'content' => $this->getLayout()->createBlock(
                     \Magento\User\Block\User\Edit\Tab\Roles::class,
                     'user.roles.grid'
                 )->toHtml()
             ]
         );*/
        return parent::_beforeToHtml();
    }
}
