<?php

namespace IWD\CartToQuote\Block\Widget\Fields;

use Magento\Customer\Block\Widget\Dob as CustomerDob;

/**
 * Class Dob
 * @package IWD\CartToQuote\Block\Request\Fields
 */
class Dob extends CustomerDob
{
    use AbstractField;

    /**
     * {@inheritdoc}
     */
    public function getFieldHtml()
    {
        $extraParams = ($this->isRequired() ? 'data-validate="{required:true}"' : '')
            . 'readonly="readonly"';

        $this->dateElement->setData([
            'extra_params' => $extraParams,
            'name' => $this->getName(),
            'id' => $this->getHtmlId(),
            'class' => $this->getHtmlClass(),
            'value' => $this->getValue(),
            'date_format' => $this->getDateFormat(),
            'image' => $this->getViewFileUrl('Magento_Theme::calendar.png'),
            'years_range' => '-120y:c+nn',
            'max_date' => '-1d',
            'change_month' => 'true',
            'change_year' => 'true',
            'show_on' => 'both'
        ]);
        return $this->dateElement->getHtml();
    }
}
