<?php

namespace IWD\CartToQuote\Block\Request\MiniCart;

use Magento\Framework\View\Element\Template;

/**
 * Class Button
 * @package IWD\CartToQuote\Block\Request\MiniCart
 */
class Button extends Template
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    private $checkoutHelper;

    /**
     * @var \IWD\CartToQuote\Helper\Data
     */
    private $ctqHelper;


    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    private $quoteFactory;

    /**
     * Button constructor.
     * @param Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \IWD\CartToQuote\Helper\Data $ctqHelper
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \IWD\CartToQuote\Helper\Data $ctqHelper,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        array $data = []
    ) {
        $this->ctqHelper = $ctqHelper;
        $this->checkoutSession = $checkoutSession;
        $this->checkoutHelper = $checkoutHelper;
        $this->quoteFactory = $quoteFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function canShowButton()
    {
        if ($this->checkoutSession->getQuote() && $this->checkoutSession->getQuote()->getId()) {
            $quote = $this->checkoutSession->getQuote();

            /* If quote is non-editable - hide "Request Quote" button */
            if ($quote->getEditableItems()) {
                return $this->ctqHelper->getEnable();
            }
        } else {
            if($this->isQuoteEditable() == 1 || $this->isQuoteEditable() == NULL) {
                return $this->ctqHelper->getEnable();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if ($this->canShowButton()) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * @return mixed
     */
    public function isQuoteEditable()
    {
        return $this->getActiveQuote()->getEditableItems();
    }

    /**
     * @return \Magento\Quote\Model\Quote
     */
    public function getActiveQuote()
    {
        $session = $this->getCustomerSession();
        $customer = $session->getCustomer();

        $quote = $this->quoteFactory->create()->loadByCustomer($customer);

        return $quote;
    }

    /**
     * @return mixed
     */
    private function getCustomerSession()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager->get('Magento\Customer\Model\SessionFactory')->create();
    }
}
