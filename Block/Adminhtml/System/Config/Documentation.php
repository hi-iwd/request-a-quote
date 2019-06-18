<?php

namespace IWD\CartToQuote\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Documentation
 * @package IWD\CartToQuote\Block\Adminhtml\System\Config
 */
class Documentation extends Field
{
    /**
     * @var string
     */
    private $userGuide = "https://www.iwdagency.com/help/m2-b2b-suite/b2b-request-a-quote-user-guide";

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return sprintf(
            "<span style='margin-bottom:-8px; display:block;'><a href='%s' target='_blank'>%s</a></span>",
            $this->userGuide,
            __("Support Articles")
        ) . $element->getValue();
    }
}
