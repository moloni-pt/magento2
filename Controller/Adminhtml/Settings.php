<?php

namespace Invoicing\Moloni\Controller\Adminhtml;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Message\ManagerInterface;
use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;
use Magento\Framework\View\Result\PageFactory;

abstract class Settings extends \Magento\Backend\App\AbstractAction
{

    const ADMIN_RESOURCE = 'Invoicing_Moloni::settings';
    protected $moloni;
    protected $messageManager;
    protected $resultPage;

    /**
     * Settings constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ManagerInterface $messageManager
     * @param Moloni $Moloni
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ManagerInterface $messageManager,
        Moloni $Moloni
    )
    {
        parent::__construct($context);

        $this->resultFactory = $resultPageFactory;
        $this->messageManager = $messageManager;
        $this->moloni = $Moloni;
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    protected function initAction()
    {
        $resultPage = $this->resultFactory->create();
        $resultPage->setActiveMenu('Invoicing_Moloni::settings');
        $resultPage->addBreadcrumb(__('Moloni'), __('Moloni'));
        $resultPage->getConfig()->getTitle()->prepend(__("Configurações"));
        return $resultPage;
    }

}
