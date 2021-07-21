<?php

namespace Invoicing\Moloni\Controller\Adminhtml;

use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;


/**
 * Class Settings
 *
 * @package Invoicing\Moloni\Controller\Adminhtml
 */
abstract class Settings implements ActionInterface
{

    public const ADMIN_RESOURCE = 'Invoicing_Moloni::settings';
    protected $moloni;
    protected $messageManager;
    protected $context;
    protected $resultFactory;

    /**
     * @var RequestInterface
     */
    protected $requestInterface;

    /**
     * @var RedirectInterface
     */
    protected $redirectInterface;

    /**
     * @var RedirectFactory
     */
    protected $redirectFactory;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * Settings constructor.
     *
     * @param $context Context
     * @param $resultPageFactory PageFactory
     * @param RedirectFactory $redirectFactory
     * @param $messageManager ManagerInterface
     * @param $Moloni Moloni
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        RedirectFactory $redirectFactory,
        ManagerInterface $messageManager,
        Moloni $Moloni
    )
    {
        $this->context = $context;
        $this->resultFactory = $resultPageFactory;
        $this->redirectFactory = $redirectFactory;
        $this->messageManager = $messageManager;
        $this->moloni = $Moloni;

        $this->response = $context->getResponse();
        $this->requestInterface = $context->getRequest();
        $this->redirectInterface = $context->getRedirect();
    }

    /**
     * @return Redirect|Page
     */
    protected function initAction()
    {
        if (!$this->context->getAuth()->isLoggedIn()) {
            $adminUrl = $this->context->getUrl()->getUrl('admin');
            return $this->redirectFactory->create()->setPath($adminUrl);
        }

        $resultPage = $this->resultFactory->create();
        $resultPage->setActiveMenu('Invoicing_Moloni::settings');
        $resultPage->addBreadcrumb(__('Moloni'), __('Moloni'));
        $resultPage->getConfig()->getTitle()->prepend(__("Configurações"));
        return $resultPage;
    }
}
