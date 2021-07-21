<?php

namespace Invoicing\Moloni\Controller\Adminhtml\Settings;

use Invoicing\Moloni\Controller\Adminhtml\Settings;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

class Edit extends Settings
{

    /**
     * Execute action based on request and return result
     *
     * @return ResultInterface|ResponseInterface
     */
    public function execute()
    {
        if (!$this->moloni->checkActiveSession()) {
            return $this->redirectFactory->create()->setPath($this->moloni->redirectTo);
        }

        return $this->initAction();
    }
}
