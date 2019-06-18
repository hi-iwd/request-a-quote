<?php

namespace IWD\CartToQuote\Block\Adminhtml\Quotes\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\Currency;
use Magento\Framework\DataObject;

/**
 * Class CustomPrice
 * @package IWD\CartToQuote\Block\Adminhtml\Quotes\Renderer
 */
class CustomPrice extends Currency
{
    /**
     * @var CurrencyFactory
     */
    private $currencyCode;

    /**
     * Commission constructor.
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Directory\Model\Currency\DefaultLocator $currencyLocator
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\Currency\DefaultLocator $currencyLocator,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        array $data = []
    ) {
        parent::__construct($context, $storeManager, $currencyLocator, $currencyFactory, $localeCurrency, $data);
        $this->currencyCode = $currencyFactory->create();
    }

    public function render(DataObject $row)
    {
        $quoteItemId = $row->getItemId();
        $priceValue = $row->getCustomPrice() ? $row->getCustomPrice() : $row->getPrice();

        $currencyCode = $this->_currencyLocator->getDefaultCurrency($this->_request);
        $priceValue = floatval($priceValue) * $this->_defaultBaseCurrency->getRate($currencyCode);
        $priceValueFormated = number_format($priceValue, 2, '.', '');

        $currencySymbol = $this->currencyCode->load($currencyCode)->getCurrencySymbol();

        $html = "<span> {$currencySymbol} </span>";
        $html .= "<input type=\"number\" name=\"custom_quote_params[{$quoteItemId}][price]\" value=\"{$priceValueFormated}\" min=\"0\" step=\"0.01\">";

        return $html;
    }
}
