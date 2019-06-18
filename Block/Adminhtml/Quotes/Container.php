<?php

namespace IWD\CartToQuote\Block\Adminhtml\Quotes;

use Magento\Framework\View\Element\Template;

/**
 * Class Container
 * @package IWD\CartToQuote\Block\Adminhtml\Quotes
 */
class Container extends Template
{
    /**
     * Quotes constructor.
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function getQuoteIdFromRequest()
    {
        $params = $this->getRequest()->getParams();
        return $params['id'];
    }
}
