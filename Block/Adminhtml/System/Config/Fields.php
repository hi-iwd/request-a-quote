<?php

namespace IWD\CartToQuote\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Fields
 * @package IWD\CartToQuote\Block\Adminhtml\System\Config
 */
class Fields extends Field
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $element->getElementHtml() . $this->getLayout()
            ->createBlock('IWD\CartToQuote\Block\Adminhtml\System\Config\Renderer\Fields')
            ->setValue($element->getValue())
            ->toHtml();
    }
}
