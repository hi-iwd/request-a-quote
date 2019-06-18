<?php

namespace IWD\CartToQuote\Block\Widget\Fields;

use IWD\CartToQuote\Helper\Data;

/**
 * Trait AbstractField
 * @package IWD\CartToQuote\Block\Widget\Fields
 */
trait AbstractField
{
    /**
     * @var string|int|float
     */
    public $field;

    /**
     * @param $field
     * @return $this
     */
    public function setField($field)
    {
        $this->field = $field;
        return $this;
    }

    /**
     * @param $prop
     * @return string|int|float
     */
    public function getFieldProp($prop)
    {
        return $this->field[$prop];
    }

    /**
     * @return string
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * @return string
     */
    public function isRequired()
    {
        return $this->getFieldProp('required');
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->getFieldProp('name');
    }

    /**
     * @return string
     */
    public function getHtmlId()
    {
        return 'iwd_c2q_' . $this->getFieldProp('id');
    }

    /**
     * @return string
     */
    public function getName()
    {
        $name = $this->getCodeByFieldName();
        return 'iwd_c2q_data[' . $name . ']';
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return '';
    }

    /**
     * @return float|int|string
     */
    public function getTitle()
    {
        return __($this->getFieldProp('name'));
    }

    /**
     * @return float|int|string
     */
    public function getPlaceholder()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getValidation()
    {
        $validationClass = [];
        $types = \IWD\CartToQuote\Model\Request\Form\Fields::getFieldTypes();
        if ($this->getFieldProp('required')) {
            $validationClass [] = 'required:true';
        }

        $code = $this->getFieldProp('code');
        if ($types[$code]['validation']) {
            $validationClass [] = "'" . $types[$code]['validation'] . "':true";
        }

        if ($validationClass) {
            return 'data-validate="{' . implode(',', $validationClass) . '}"';
        }

        return '';
    }

    /**
     * @return string
     */
    public function getCodeByFieldName()
    {
        $name = $this->getFieldProp('name');
        $name = trim($name);
        return Data::prepareCodeFromTitle($name);
    }
}
