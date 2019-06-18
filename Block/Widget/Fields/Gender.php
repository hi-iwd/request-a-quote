<?php

namespace IWD\CartToQuote\Block\Widget\Fields;

use Magento\Customer\Api\Data\OptionInterface;

class Gender extends \Magento\Customer\Block\Widget\Gender
{
    use AbstractField;

    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('IWD_CartToQuote::widget/gender.phtml');
    }

    /**
     * Returns options from gender attribute
     * @return OptionInterface[]
     */
    public function getGenderOptions()
    {
        $options = parent::getGenderOptions();

        if (isset($options[0])) {
            $emptyRow = $options[0];
            $options[0] = $emptyRow->setLabel('Please select a gender');
        }

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldId($field)
    {
        return $this->getHtmlId();
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldName($field)
    {
        return $this->getName();
    }
}
