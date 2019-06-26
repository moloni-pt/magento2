<?php

namespace Invoicing\Moloni\Controller\Adminhtml;

use Magento\Backend\App\AbstractAction;
use Magento\Backend\App\Action\Context;
use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;
use Magento\Framework\View\Result\PageFactory;

abstract class Documents extends AbstractAction
{

    const ADMIN_RESOURCE = 'Invoicing_Moloni::documents';

    protected $moloni;
    protected $tokensRepository;
    protected $dataPersistor;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Moloni $moloni
    )
    {
        parent::__construct($context);

        $this->moloni = $moloni;
        $this->resultFactory = $resultPageFactory;
    }

    protected function initAction()
    {
        $resultPage = $this->resultFactory->create();
        $resultPage->setActiveMenu('Invoicing_Moloni::home');
        $resultPage->addBreadcrumb(__('Moloni'), __('Moloni'));
        $resultPage->getConfig()->getTitle()->prepend(__("Moloni"));
        return $resultPage;
    }

}
