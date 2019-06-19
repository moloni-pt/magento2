<?php

namespace Invoicing\Moloni\Controller\Adminhtml\Settings;

use Invoicing\Moloni\Controller\Adminhtml\Settings;

class Edit extends Settings
{

    public function execute()
    {
        if (!$this->moloni->checkActiveSession()) {
            $this->_redirect($this->moloni->redirectTo);
            return false;
        }

        $page = $this->initAction();
        return $page;
    }
}
