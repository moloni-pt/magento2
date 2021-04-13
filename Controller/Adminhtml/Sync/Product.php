<?php

namespace Invoicing\Moloni\Controller\Adminhtml\Sync;

use Invoicing\Moloni\Libraries\MoloniLibrary\Controllers\ProductsFactory as MoloniProductsFactory;
use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;
use Magento\Backend\App\AbstractAction;
use Magento\Backend\App\Action\Context;

class Product extends AbstractAction
{

    const ADMIN_RESOURCE = 'Invoicing_Moloni::home';

    /**
     * @var Moloni
     */
    protected $moloni;

    /**
     * @var MoloniProductsFactory
     */
    private $productsFactory;

    public function __construct(
        Context $context,
        Moloni $moloni,
        MoloniProductsFactory $productsFactory
    )
    {
        parent::__construct($context);

        $this->moloni = $moloni;
        $this->productsFactory = $productsFactory;
    }

    /**
     * @return bool|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->moloni->checkActiveSession()) {
            $this->messageManager->addErrorMessage(__('Erro com sessão Moloni'));
            $this->_redirect('catalog/product/index');
            return false;
        }

        $productId = $this->getRequest()->getParam('id');

        if (!$productId) {
            $this->messageManager->addErrorMessage(__('Id de artigo não encontrado'));
            $this->_redirect('catalog/product/index');
            return false;
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
        $this->_redirect('catalog/product/edit', ['id' => $productId]);
        return true;
    }
}
