<?php

namespace IWD\CartToQuote\Block\Request;

use Magento\Framework\View\Element\Template;

/**
 * Class Dialog
 * @package IWD\CartToQuote\Block\Request
 */
class Dialog extends Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \IWD\CartToQuote\Helper\Data
     */
    private $helper;

    /**
     * @var null
     */
    private $regions = null;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Region\Collection
     */
    private $regionCollection;

    /**
     * @var \Magento\Customer\Model\Options
     */
    private $options;

    /**
     * @var \IWD\CartToQuote\Model\Request\Form\Fields
     */
    private $formFields;

    /**
     * @var \Magento\Customer\Model\Url
     */
    private $customerUrl;

    /**
     * Dialog constructor.
     * @param Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \IWD\CartToQuote\Helper\Data $helper
     * @param \Magento\Directory\Model\ResourceModel\Region\Collection $regionCollection
     * @param \Magento\Customer\Model\Options $options
     * @param \IWD\CartToQuote\Model\Request\Form\Fields $formFields
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \IWD\CartToQuote\Helper\Data $helper,
        \Magento\Directory\Model\ResourceModel\Region\Collection $regionCollection,
        \Magento\Customer\Model\Options $options,
        \IWD\CartToQuote\Model\Request\Form\Fields $formFields,
        \Magento\Customer\Model\Url $customerUrl,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->regionCollection = $regionCollection;
        $this->options = $options;
        $this->formFields = $formFields;
        $this->customerUrl = $customerUrl;
    }

    //TODO add check if this is b2b site

    /**
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->getUrl('iwdc2q/ajax/login', ['_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('iwdc2q/ajax/requestQuote', ['_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * @return array
     */
    public function getFields()
    {
        $fieldsArr = $this->formFields->getFields();
        return (count($fieldsArr) <= 5)
            ? [0 => $fieldsArr]
            : array_chunk($fieldsArr, ceil(count($fieldsArr) / 2), true);
    }

    public function getRegions()
    {
        if (!$this->regions) {
            $options = $this->regionCollection->load()->toOptionArray();
            $this->regions = json_encode($options, JSON_HEX_APOS);
        }

        return $this->regions;
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if ($this->helper->getEnable()) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * @param $field
     * @return string
     */
    public function getFieldHtml($field)
    {
        $type = $this->getBlockTypeByCode($field);
        $block = $this->getLayout()->createBlock('IWD\CartToQuote\Block\Widget\Fields\\' . $type);
        if ($block) {
            return $block->setField($field)->toHtml();
        }

        return '';
    }

    /**
     * @param $field
     * @return mixed|string
     */
    private function getBlockTypeByCode($field)
    {
        $map = [
            'date_of_birth' => 'Dob',
            'gender' => 'Gender',
            'suffix' => 'Suffix',
            'prefix' => 'Prefix',
            'country' => 'Country',
            'state' => 'State',
            'email' => 'Email',
        ];
        $code = isset($field['code']) ? $field['code'] : '';

        return isset($map[$code]) ? $map[$code] : 'Input';
    }

    /**
     * @param $field
     * @return int
     */
    public function isFieldEnabled($field)
    {
        return isset($field['enable']) ? $field['enable'] : 0;
    }
}
