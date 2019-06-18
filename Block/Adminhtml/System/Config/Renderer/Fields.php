<?php

namespace IWD\CartToQuote\Block\Adminhtml\System\Config\Renderer;

/**
 * Class Fields
 * @package IWD\CartToQuote\Block\Adminhtml\System\Config\Renderer
 */
class Fields extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'config/fields.phtml';

    /**
     * @var
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
     * Fields constructor.
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
    public function getFields()
    {
        return $this->getValue() ? $this->jsonHelper->jsonDecode($this->getValue()) : [];
    }
}
