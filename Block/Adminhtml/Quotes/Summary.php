<?php

namespace IWD\CartToQuote\Block\Adminhtml\Quotes;

use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Directory\Model\Currency\DefaultLocator;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\DataObject;

/**
 * Class Summary
 * @package IWD\CartToQuote\Block\Adminhtml\Quotes
 */
class Summary extends Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Summary constructor.
     * @param Template\Context $context
     * @param StoreManagerInterface $storeManager
     * @param DefaultLocator $currencyLocator
     * @param CurrencyFactory $currencyFactory
     * @param CurrencyInterface $localeCurrency
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        StoreManagerInterface $storeManager,
        DefaultLocator $currencyLocator,
        CurrencyFactory $currencyFactory,
        CurrencyInterface $localeCurrency,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->storeManager = $storeManager;
        $this->currencyLocator = $currencyLocator;
        $this->currencyFactory = $currencyFactory;
        $this->localeCurrency = $localeCurrency;
        $this->registry = $registry;
    }

    /**
     * @return mixed
     */
    public function getQuote()
    {
        return $this->registry->registry('requested_quote');
    }

    /**
     * @return mixed
     */
    public function getQuoteSubtotal()
    {
        return $this->getQuote()->getSubtotal();
    }

    /**
     * @return mixed
     */
    public function getQuoteSubtotalWithDiscount()
    {
        return $this->getQuote()->getSubtotalWithDiscount();
    }

    /**
     * @return mixed
     */
    public function getQuoteDiscount()
    {
        $quoteSubtotal = $this->getQuoteSubtotal();
        $quoteSubtotalWithDiscount = $this->getQuoteSubtotalWithDiscount();
        $quoteDiscount = $quoteSubtotal - $quoteSubtotalWithDiscount;

        return $quoteDiscount;
    }

    /**
     * @return mixed
     */
    public function getQuoteShippingAmount()
    {
        return $this->getQuote()->getShippingAddress()->getShippingAmount();
    }

    /**
     * @return mixed
     */
    public function getQuoteTaxAmount()
    {
        return $this->getQuote()->getShippingAddress()->getTaxAmount();
    }

    /**
     * @return mixed
     */
    public function getQuoteGrandTotal()
    {
        return $this->getQuote()->getGrandTotal();
    }

    /**
     * @param $rate
     * @return mixed
     */
    public function getFormatedPrice($rate)
    {
        $currencyCode = $this->currencyLocator->getDefaultCurrency($this->_request);
        $rate = $this->localeCurrency->getCurrency($currencyCode)->toCurrency($rate);

        return $rate;
    }

    /**
     * @return mixed
     */
    public function getFormatedQuoteSubtotal()
    {
        $quoteSubtotal = $this->getQuoteSubtotal();
        return $this->getFormatedPrice($quoteSubtotal);
    }

    /**
     * @return mixed
     */
    public function getFormatedQuoteDiscount()
    {
        $quoteDiscount = $this->getQuoteDiscount();
        return $this->getFormatedPrice($quoteDiscount);
    }

    /**
     * @return mixed
     */
    public function getFormatedQuoteShippingAmount()
    {
        $quoteShippingAmount = $this->getQuoteShippingAmount();
        return $this->getFormatedPrice($quoteShippingAmount);
    }

    /**
     * @return mixed
     */
    public function getFormatedQuoteTaxAmount()
    {
        $quoteTaxAmount = $this->getQuoteTaxAmount();
        return $this->getFormatedPrice($quoteTaxAmount);
    }

    /**
     * @return mixed
     */
    public function getFormatedQuoteGrandTotal()
    {
        $quoteGrandTotal = $this->getQuoteGrandTotal();
        return $this->getFormatedPrice($quoteGrandTotal);
    }
}
