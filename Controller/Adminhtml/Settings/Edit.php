<?php

namespace Invoicing\Moloni\Controller\Adminhtml\Settings;

use Invoicing\Moloni\Controller\Adminhtml\Settings;

class Edit extends Settings
{

    public function execute()
    {
        if (!$this->moloni->checkActiveSession()) {
            $this->redirectInterface->redirect($this->response, $this->moloni->redirectTo);
            return false;
        }

        return $this->initAction();
    }
}
