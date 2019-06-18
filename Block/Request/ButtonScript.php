<?php

namespace IWD\CartToQuote\Block\Request;

use Magento\Framework\View\Element\Template;

/**
 * Class Button
 * @package IWD\CartToQuote\Block\Request
 */
class ButtonScript extends Template
{
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
