<?php

namespace Invoicing\Moloni\Controller\Adminhtml;

use Magento\Backend\App\AbstractAction;
use Magento\Backend\App\Action\Context;
use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;
use Magento\Framework\View\Result\PageFactory;
use Invoicing\Moloni\Libraries\MoloniLibrary\Controllers\DocumentsFactory as MoloniDocumentsFactory;
use Invoicing\Moloni\Model\DocumentsRepository;

abstract class Documents extends AbstractAction
{

    const ADMIN_RESOURCE = 'Invoicing_Moloni::documents';

    /**
     * @var Moloni
     */
    protected $moloni;

    /**
     * @var MoloniDocumentsFactory
     */
    protected $moloniDocumentsFactory;

    /**
     * @var DocumentsRepository
     */
    protected $documentsRepository;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Moloni $moloni,
        MoloniDocumentsFactory $moloniDocumentsFactory,
        DocumentsRepository $documentsRepository
    )
    {
        parent::__construct($context);

        $this->moloni = $moloni;
        $this->moloniDocumentsFactory = $moloniDocumentsFactory;
        $this->documentsRepository = $documentsRepository;
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

    /**
     * @param int $orderId
     * @return bool
     */
    protected function documentExists($orderId)
    {
        $forceDocumentCreation = $this->getRequest()->getParam('force') == 1;
        $hasDocument = $this->documentsRepository->getByOrderId($orderId);
        if ($hasDocument && !$forceDocumentCreation) {
            $forceCreateUrlParams = ['order_id' => $orderId, 'force' => true];
            $forceCreateUrl = $this->_url->getUrl('moloni/documents/create', $forceCreateUrlParams);

            $this->messageManager->addComplexErrorMessage(
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
