<?php

namespace Invoicing\Moloni\Controller\Adminhtml\Settings;

class Edit extends \Invoicing\Moloni\Controller\Adminhtml\Settings
{

    public function execute()
    {
        if (!$this->moloni->checkActiveSession()) {
            $this->_redirect($this->moloni->redirectTo);
            return false;
        }

        $this->initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__("Moloni - Configurações"));
        $this->_view->renderLayout();
    }
}