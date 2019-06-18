<?php

namespace IWD\CartToQuote\Block\Widget\Fields;

class Suffix extends Prefix
{
    use AbstractField;

    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('IWD_CartToQuote::widget/suffix.phtml');
    }

    public function getSuffixOptions()
    {
        return $this->getOptions()->getNameSuffixOptions($this->getCheckoutSession()->getQuote()->getStore());
    }
}
