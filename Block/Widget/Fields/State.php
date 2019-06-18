<?php

namespace IWD\CartToQuote\Block\Widget\Fields;

class State extends \Magento\Framework\View\Element\Template
{
    use AbstractField;

    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('IWD_CartToQuote::widget/state.phtml');
    }

    /**
     * @return string
     */
    public function getSelectName()
    {
        $name = $this->getCodeByFieldName();
        return sprintf("iwd_c2q_data[%s_id]", $name);
    }

    /**
     * @return string
     */
    public function getSelectId()
    {
        return 'iwd_c2q_' . $this->getFieldProp('id') . '_id';
    }
}
