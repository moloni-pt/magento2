<?php

namespace Invoicing\Moloni\Controller\Adminhtml;

use Magento\Backend\App\AbstractAction;
use Magento\Backend\App\Action\Context;
use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Request\DataPersistor;
use Invoicing\Moloni\Model\TokensRepository;

abstract class Home extends AbstractAction
{

    const ADMIN_RESOURCE = 'Invoicing_Moloni::home';

    protected $moloni;
    protected $tokensRepository;
    protected $dataPersistor;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        DataPersistor $dataPersistor,
        TokensRepository $tokensRepository,
        Moloni $moloni
    )
    {
        parent::__construct($context);

        $this->moloni = $moloni;
        $this->resultFactory = $resultPageFactory;
        $this->dataPersistor = $dataPersistor;
        $this->tokensRepository = $tokensRepository;
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
