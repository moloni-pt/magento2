<?php

namespace Invoicing\Moloni\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Framework\Message\ManagerInterface;
use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;

abstract class Settings extends \Magento\Backend\App\AbstractAction
{

    const ADMIN_RESOURCE = 'Invoicing_Moloni::settings';
    protected $moloni;
    protected $messageManager;

    public function __construct(
        Action\Context $context,
        ManagerInterface $messageManager,
        Moloni $Moloni
    )
    {

        parent::__construct($context);

        $this->messageManager = $messageManager;
        $this->moloni = $Moloni;
    }

    protected function initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Invoicing_Moloni::settings'
        )->_addBreadcrumb(
            __('Moloni'),
            __('Moloni')
        )->_addBreadcrumb(
            __('Configurações'),
            __('Configurações')
        );

        return $this;
    }

}