<?php

namespace Invoicing\Moloni\Controller\Adminhtml\Sync;

use Invoicing\Moloni\Libraries\MoloniLibrary\Controllers\ProductsFactory as MoloniProductsFactory;
use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\HttpInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;

class Product implements ActionInterface
{

    public const ADMIN_RESOURCE = 'Invoicing_Moloni::home';

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Moloni
     */
    protected $moloni;

    /**
     * @var PageFactory
     */
    protected $resultFactory;

    /**
     * @var RedirectFactory
     */
    protected $redirectFactory;

    /**
     * @var MoloniProductsFactory
     */
    private $productsFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var RequestInterface
     */
    protected $requestInterface;

    /**
     * @var RedirectInterface
     */
    protected $redirectInterface;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var HttpInterface
     */
    protected $http;

    /**
     * Product constructor.
     *
     * @param Context $context
     * @param Moloni $moloni
     * @param MoloniProductsFactory $productsFactory
     * @param RedirectFactory $redirectFactory
     * @param ManagerInterface $messageManager
     * @param PageFactory $resultFactory
     */
    public function __construct(
        Context $context,
        Moloni $moloni,
        MoloniProductsFactory $productsFactory,
        RedirectFactory $redirectFactory,
        ManagerInterface $messageManager,
        PageFactory $resultFactory
    )
    {
        $this->moloni = $moloni;
        $this->context = $context;
        $this->productsFactory = $productsFactory;
        $this->redirectFactory = $redirectFactory;
        $this->messageManager = $messageManager;
        $this->resultFactory = $resultFactory;

        $this->response = $context->getResponse();
        $this->requestInterface = $context->getRequest();
        $this->redirectInterface = $context->getRedirect();
    }


    /**
     * Execute action based on request and return result
     *
     * @return ResultInterface|ResponseInterface
     */
    public function execute()
    {
        if (!$this->moloni->checkActiveSession()) {
            $this->messageManager->addErrorMessage(__('Erro com sessão Moloni'));

            return $this->redirectFactory->create()->setPath('catalog/product/index');
        }

        $productId = $this->requestInterface->getParam('id');

        if (!$productId) {
            $this->messageManager->addErrorMessage(__('Id de artigo não encontrado'));
            return $this->redirectFactory->create()->setPath('catalog/product/index');
        }

        $syncProduct = $this->productsFactory->create();

        if ($syncProduct->syncProductFromId($productId)) {
            if ($syncProduct->productInserted) {
                $this->messageManager->addSuccessMessage(__("Artigo inserido no Moloni com successo"));
            } else {
                $this->messageManager->addSuccessMessage(__("Artigo atualizado com successo"));
            }
        } else {
            $this->messageManager->addErrorMessage(
                __('Erro ao actualizar o artigo: ') .
                $this->moloni->errors->getErrors('first')['message']
            );
        }

        return $this->redirectFactory->create()->setPath('catalog/product/index', ['id' => $productId]);
    }
}
