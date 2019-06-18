<?php

namespace IWD\CartToQuote\Block\Adminhtml\System\Config\Renderer;

/**
 * Class Statuses
 * @package IWD\CartToQuote\Block\Adminhtml\System\Config\Renderer
 */
class Statuses extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'config/statuses.phtml';

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var \IWD\CartToQuote\Helper\Data
     */
    private $c2qHelper;

    /**
     * Statuses constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \IWD\CartToQuote\Helper\Data $c2qHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \IWD\CartToQuote\Helper\Data $c2qHelper,
        array $data = []
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->c2qHelper = $c2qHelper;
        parent::__construct($context, $data);
    }

    /**
     * @param $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return array|mixed
     */
    public function getStatuses()
    {
        return $this->getValue() ? $this->jsonHelper->jsonDecode($this->getValue()) : [];
    }
}
