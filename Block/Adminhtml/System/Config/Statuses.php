<?php

namespace IWD\CartToQuote\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Backend\Block\Template\Context;

class Statuses extends Field
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $element->getElementHtml() . $this->getLayout()
            ->createBlock('IWD\CartToQuote\Block\Adminhtml\System\Config\Renderer\Statuses')
            ->setValue($element->getValue())
            ->toHtml();
    }
}
