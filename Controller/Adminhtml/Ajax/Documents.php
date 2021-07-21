<?php

namespace Invoicing\Moloni\Controller\Adminhtml\Ajax;

use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;

class Documents extends Action
{
    private JsonFactory $jsonFactory;

    /**
     * @var Moloni
     */
    protected Moloni $moloni;

    /**
     * Documents constructor.
     *
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param Moloni $moloni
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        Moloni $moloni
    )
    {
        parent::__construct($context);

        $this->moloni = $moloni;
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * @return Json
     */
    public function execute(): Json
    {
        $result = $this->jsonFactory->create();
        $response = [];
        $this->_actionFlag->set('', self::FLAG_NO_POST_DISPATCH, true);
        return $result->setData($response);
    }
}
