<?php

namespace IWD\CartToQuote\Block\Widget\Fields;

use Magento\Framework\View\Element\Template;

class Prefix extends \Magento\Framework\View\Element\Template
{
    use AbstractField;

    private $options;
    private $checkoutSession;

    public function __construct(
        Template\Context $context,
        \Magento\Customer\Model\Options $options,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->options = $options;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('IWD_CartToQuote::widget/prefix.phtml');
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getCheckoutSession()
    {
        return $this->checkoutSession;
    }

    public function getPrefixOptions()
    {
        return $this->getOptions()->getNamePrefixOptions($this->getCheckoutSession()->getQuote()->getStore());
    }
}
