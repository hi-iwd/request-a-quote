<?php

namespace IWD\CartToQuote\Block\Request;

use Magento\Catalog\Block\ShortcutInterface;
use Magento\Framework\View\Element\Template;

/**
 * Class Button
 * @package IWD\CartToQuote\Block\Request
 */
class Button extends Template implements ShortcutInterface
{
    /**
     *
     */
    const ALIAS_ELEMENT_INDEX = 'alias';

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $_checkoutSession;

    /**
     * @var \IWD\CartToQuote\Helper\Data
     */
    private $_ctqHelper;

    /**
     * Button constructor.
     * @param Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \IWD\CartToQuote\Helper\Data $ctqHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \IWD\CartToQuote\Helper\Data $ctqHelper,
        array $data = []
    )
    {
        $this->_ctqHelper = $ctqHelper;
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($context, $data);
    }


    /**
     * @inheritdoc
     */
    public function getAlias()
    {
        return $this->getData(self::ALIAS_ELEMENT_INDEX);
    }

    /**
     * @return bool
     */
    public function canShowButton()
    {
        if ($this->_ctqHelper->getEnable()) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->canShowButton()) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * @return string
     */
    public function getUserInfoUrl()
    {
        return $this->getUrl('iwdc2q/ajax/customerData', ['_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * @return string
     */
    public function getCheckUserEmailUrl()
    {
        return $this->getUrl('iwdc2q/ajax/checkUserEmail', ['_secure' => $this->getRequest()->isSecure()]);
    }
}
