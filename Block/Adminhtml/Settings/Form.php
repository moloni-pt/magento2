<?php

namespace Invoicing\Moloni\Block\Adminhtml\Settings;

use Magento\Config\Model\Config\Reader\Source\Deployed\SettingChecker;

class Form extends \Magento\Config\Block\System\Config\Form
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Config\Model\Config\Factory $configFactory,
        \Magento\Config\Model\Config\Structure $configStructure,
        \Magento\Config\Block\System\Config\Form\Fieldset\Factory $fieldsetFactory,
        \Magento\Config\Block\System\Config\Form\Field\Factory $fieldFactory,
        array $data = [],
        ?SettingChecker $settingChecker = null
    )
    {
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $configFactory,
            $configStructure,
            $fieldsetFactory,
            $fieldFactory,
            $data,
            $settingChecker
        );
    }
}
