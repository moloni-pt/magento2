<?php

namespace Invoicing\Moloni\Controller\Adminhtml;

use Invoicing\Moloni\Libraries\MoloniLibrary\Controllers\DocumentsFactory as MoloniDocumentsFactory;
use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;
use Invoicing\Moloni\Model\DocumentsRepository;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

abstract class Documents implements ActionInterface
{

    public const ADMIN_RESOURCE = 'Invoicing_Moloni::documents';

    /**
     * @var Moloni
     */
    protected Moloni $moloni;

    /**
     * @var MoloniDocumentsFactory
     */
    protected MoloniDocumentsFactory $moloniDocumentsFactory;

    /**
     * @var DocumentsRepository
     */
    protected DocumentsRepository $documentsRepository;

    /**
     * @var PageFactory
     */
    protected PageFactory $resultFactory;

    /**
     * @var Context
     */
    protected Context $context;

    /**
     * @var UrlInterface
     */
    protected UrlInterface $urlBuilder;

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
     * Documents constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Moloni $moloni
     * @param MoloniDocumentsFactory $moloniDocumentsFactory
     * @param DocumentsRepository $documentsRepository
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Moloni $moloni,
        MoloniDocumentsFactory $moloniDocumentsFactory,
        DocumentsRepository $documentsRepository,
        UrlInterface $urlBuilder
    )
    {
        $this->context = $context;
        $this->moloni = $moloni;
        $this->moloniDocumentsFactory = $moloniDocumentsFactory;
        $this->documentsRepository = $documentsRepository;
        $this->resultFactory = $resultPageFactory;
        $this->urlBuilder = $urlBuilder;

        $this->redirect = $context->getRedirect();
        $this->request = $context->getRequest();
        $this->messageManager = $context->getMessageManager();
    }

    /**
     * @return false|Page
     */
    protected function initAction()
    {
        if (!$this->context->getAuth()->isLoggedIn()) {
            $adminUrl = $this->context->getUrl()->getUrl('admin');
            $this->context->getRedirect()->redirect($this->context->getResponse(), $adminUrl);
            return false;
        }

        $resultPage = $this->resultFactory->create();
        $resultPage->setActiveMenu('Invoicing_Moloni::home');
        $resultPage->addBreadcrumb(__('Moloni'), __('Moloni'));
        $resultPage->getConfig()->getTitle()->prepend(__("Moloni"));
        return $resultPage;
    }

    /**
     * @param int $orderId
     * @return bool
     */
    protected function documentExists(int $orderId): bool
    {
        $forceDocumentCreation = (int)$this->context->getRequest()->getParam('force') === 1;
        $hasDocument = $this->documentsRepository->getByOrderId($orderId);
        if ($hasDocument && !$forceDocumentCreation) {
            $forceCreateUrlParams = ['order_id' => $orderId, 'force' => true];
            $forceCreateUrl = $this->urlBuilder->getUrl('moloni/documents/create', $forceCreateUrlParams);

            $this->context->getMessageManager()->addComplexErrorMessage(
                'createDocumentExistsMessage',
                [
                    'invoice_date' => $hasDocument[0]->getInvoiceDate(),
                    'create_url' => $forceCreateUrl,
                ]
            );

            return true;
        }

        return false;
    }
}
