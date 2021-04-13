<?php

namespace Invoicing\Moloni\Controller\Adminhtml;

use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;
use Invoicing\Moloni\Model\TokensRepository;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Request\DataPersistor;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;

abstract class Home implements ActionInterface
{

    public const ADMIN_RESOURCE = 'Invoicing_Moloni::home';

    protected Moloni $moloni;
    protected TokensRepository $tokensRepository;
    protected DataPersistor $dataPersistor;

    /**
     * @var RedirectInterface
     */
    protected RedirectInterface $redirect;

    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * @var ManagerInterface
     */
    protected ManagerInterface $messageManager;

    /**
     * @var PageFactory
     */
    protected PageFactory $resultFactory;

    /**
     * @var Context
     */
    protected Context $context;

    /**
     * @var ResponseInterface
     */
    protected ResponseInterface $response;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        DataPersistor $dataPersistor,
        TokensRepository $tokensRepository,
        Moloni $moloni
    )
    {
        $this->moloni = $moloni;
        $this->resultFactory = $resultPageFactory;
        $this->dataPersistor = $dataPersistor;
        $this->tokensRepository = $tokensRepository;

        $this->context = $context;
        $this->response = $context->getResponse();
        $this->redirect = $context->getRedirect();
        $this->request = $context->getRequest();
        $this->messageManager = $context->getMessageManager();
    }

    protected function initAction()
    {
        $resultPage = $this->resultFactory->create();
        $resultPage->setActiveMenu('Invoicing_Moloni::home');
        $resultPage->addBreadcrumb(__('Moloni'), __('Moloni'));
        $resultPage->getConfig()->getTitle()->prepend(__("Moloni"));

        if (!$this->context->getAuth()->isLoggedIn()) {
            $adminUrl = $this->context->getUrl()->getUrl('admin');
            $this->redirect->redirect($this->response, $adminUrl);
            return false;
        }

        return $resultPage;
    }
}
