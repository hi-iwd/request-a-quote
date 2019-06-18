<?php

namespace IWD\CartToQuote\Block\Widget\Fields;
use Magento\Framework\View\Element\Template;

/**
 * Class Input
 * @package IWD\CartToQuote\Block\Widget\Fields
 */
class Input extends \Magento\Framework\View\Element\Template
{
    use AbstractField;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    public function __construct(
        Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
    }

    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('IWD_CartToQuote::widget/input.phtml');
    }
}
