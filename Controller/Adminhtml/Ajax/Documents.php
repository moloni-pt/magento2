<?php

namespace Invoicing\Moloni\Controller\Adminhtml\Ajax;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use \Magento\Framework\Controller\Result\JsonFactory;
use Invoicing\Moloni\Libraries\MoloniLibrary\Moloni;

class Documents extends Action
{
    private $jsonFactory;

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

    public function execute()
    {
        $result = $this->jsonFactory->create();
        $response = ["Olá"];
        $this->_actionFlag->set('', self::FLAG_NO_POST_DISPATCH, true);
        return $result->setData($response);
    }


}
