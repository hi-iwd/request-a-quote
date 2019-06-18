<?php

namespace IWD\CartToQuote\Block\Quote\Share;

use IWD\CartToQuote\Model\Api\Customer;
use Magento\Framework\View\Element\Template;

/**
 * Class View
 * @package IWD\CartToQuote\Block\Quote\Share
 */
class View extends Template
{
    /**
     * @var Customer
     */
    private $customer;

    /**
     * @var string
     */
    private $hash = '0';

    /**
     * Quotes constructor.
     * @param Template\Context $context
     * @param Customer $customer
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Customer $customer,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customer = $customer;
    }

    /**
     * @return string
     */
    public function getQuoteUrl()
    {
        return $this->customer->getUrl('quote/view/sharedQuote/' . $this->getQuoteHash());
    }

    /**
     * @param $hash
     * @return $this
     */
    public function setQuoteHash($hash)
    {
        $this->hash = $hash;
        return $this;
    }

    /**
     * @return string
     */
    public function getQuoteHash()
    {
        return $this->hash;
    }
}
