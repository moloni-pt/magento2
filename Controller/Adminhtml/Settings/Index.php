<?php
/**
 * Module for Magento 2 by Moloni
 * Copyright (C) 2017  Moloni, lda
 *
 * This file is part of Invoicing/Moloni.
 *
 * Invoicing/Moloni is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Invoicing\Moloni\Controller\Adminhtml\Settings;

use Magento\Backend\App\Action;
use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{

    private $resultPageFactory;
    private $moloni;
    private $request;
    protected $messageManager;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Http $request
     * @param PageFactory $resultPageFactory
     * @param ManagerInterface $messageManager
     * @param Moloni $Moloni
     */
    public function __construct(
        Context $context,
        Http $request,
        PageFactory $resultPageFactory,
        ManagerInterface $messageManager,
        Moloni $Moloni
    )
    {
        $this->request = $request;
        $this->resultPageFactory = $resultPageFactory;
        $this->messageManager = $messageManager;
        $this->moloni = $Moloni;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return bool|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->moloni->checkActiveSession()) {
            $this->_redirect($this->moloni->redirectTo);
            return false;
        }

        if (is_array($this->getRequest()->getParam('general'))) {
            $settings = [];
            $settings = array_merge($settings, $this->getRequest()->getParam('general'));

            foreach ($settings as $label => $value) {
                $this->moloni->settingsRepository->saveSetting($this->moloni->session->companyId, $label, $value);
            }

            $this->messageManager->addSuccessMessage(__('Alterações guardadas com sucesso'));
            $this->_redirect('*/*/*/id/1');
            return false;
        }

        return $this->resultPageFactory->create();
    }
}
