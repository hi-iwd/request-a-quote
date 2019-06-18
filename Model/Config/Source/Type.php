<?php

namespace IWD\CartToQuote\Model\Config\Source;

/**
 * Class Type
 * @package IWD\CartToQuote\Model\Config\Source
 */
class Type implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => \IWD\CartToQuote\Helper\Data::REQUEST_TYPE_BOTH,
                'label' => __('Request A Quote + Purchase Products')
            ],
            [
                'value' => \IWD\CartToQuote\Helper\Data::REQUEST_TYPE_QUOTE_ONLY,
                'label' => __('Request A Quote')
            ]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            \IWD\CartToQuote\Helper\Data::REQUEST_TYPE_QUOTE_ONLY => __('Only request to quote button'),
            \IWD\CartToQuote\Helper\Data::REQUEST_TYPE_BOTH => __('Request to quote and proceed to checkout button')
        ];
    }
}
