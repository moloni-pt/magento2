<?php
/**
 * Created by PhpStorm.
 * User: Nuno
 * Date: 30/07/2019
 * Time: 11:15
 */

namespace Invoicing\Moloni\Controller\Documents;

use Magento\Framework\App\Action;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Element\Html\Links;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Controller\AbstractController\OrderLoaderInterface;

class View implements ActionInterface
{
    /**
     * @var OrderLoaderInterface
     */
    protected OrderLoaderInterface $orderLoader;

    /**
     * @var PageFactory
     */
    protected PageFactory $resultPageFactory;

    /**
     * @var RequestInterface
     */
    protected RequestInterface $requestInterface;
    /**
     * @var RedirectInterface
     */
    protected RedirectInterface $redirectInterface;

    /**
     * @var ResponseInterface
     */
    protected ResponseInterface $response;

    /**
     * @param Action\Context $context
     * @param OrderLoaderInterface $orderLoader
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Action\Context $context,
        OrderLoaderInterface $orderLoader,
        PageFactory $resultPageFactory
    )
    {
        $this->orderLoader = $orderLoader;
        $this->resultPageFactory = $resultPageFactory;

        $this->response = $context->getResponse();
        $this->requestInterface = $context->getRequest();
        $this->redirectInterface = $context->getRedirect();
    }

    /**
     * Execute action based on request and return result
     *
     * @return ResultInterface|ResponseInterface
     *
     */
    public function execute()
    {
        $result = $this->orderLoader->load($this->requestInterface);
        if ($result instanceof ResultInterface) {
            return $result;
        }

        $resultPage = $this->resultPageFactory->create();

        /** @var Links $navigationBlock */
        $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('sales/order/history');
        }
        return $resultPage;
    }
}
