<?php

namespace IWD\CartToQuote\Block\Widget\Fields;

/**
 * Class Email
 * @package IWD\CartToQuote\Block\Widget\Fields
 */
class Email extends Input
{
    /**
     * @return string
     */
    public function getHtmlId()
    {
        $name = $this->getFieldProp('name');
        if (strtolower($name) == 'email') {
            return 'iwd_c2q_email';
        }

        return parent::getHtmlId();
    }
}
