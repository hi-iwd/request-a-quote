<?php

namespace IWD\CartToQuote\Model\Request\Form;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Fields
{
    const FIELDS_PATH = 'iwd_cart_to_quote/fields/fields';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * Fields constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        $formFields = [];

        $value = $this->scopeConfig->getValue(self::FIELDS_PATH);
        if ($value) {
            $fields = $this->jsonHelper->jsonDecode($value);
            foreach ($fields as $field) {
                if (isset($field['enable']) && $field['enable']) {
                    $formFields[] = $field;
                }
            }
        }

        return $formFields;
    }

    /**
     * @return array
     */
    public static function getFieldTypes()
    {
        return array(
            'text' => [
                'code' => 'text',
                'name' => 'Text Field',
                'validation' => '',
            ],
            'email' => [
                'code' => 'email',
                'name' => 'Email',
                'validation' => 'validate-email',
            ],
            'country' => [
                'code' => 'country',
                'name' => 'Country',
                'validation' => '',
            ],
            'state' => [
                'code' => 'state',
                'name' => 'State',
                'validation' => '',
            ],
            'postcode' => [
                'code' => 'postcode',
                'name' => 'Post Code',
                'validation' => '',
            ],
            'gender' => [
                'code' => 'gender',
                'name' => 'Gender',
                'validation' => '',
            ],
            'date_of_birth' => [
                'code' => 'date_of_birth',
                'name' => 'Date of Birth',
                'validation' => '',
            ],
            'suffix' => [
                'code' => 'suffix',
                'name' => 'Suffix',
                'validation' => '',
            ],
            'prefix' => [
                'code' => 'prefix',
                'name' => 'Prefix',
                'validation' => '',
            ],
        );
    }

    /**
     * @return array
     */
    public static function getNativeFields()
    {
        return array(
            1 => [
                'id' => 1,
                'name' => 'First Name',
                'code' => 'text',
                'enable' => 1,
                'required' => 1,
                'native' => 1,
            ],
            2 => [
                'id' => 2,
                'name' => 'Last Name',
                'code' => 'text',
                'enable' => 1,
                'required' => 1,
                'native' => 1,
            ],
            3 => [
                'id' => 3,
                'name' => 'Email',
                'code' => 'email',
                'enable' => 1,
                'required' => 1,
                'native' => 1,
            ],
            4 => [
                'id' => 4,
                'name' => 'Country',
                'code' => 'country',
                'enable' => 1,
                'required' => 1,
                'native' => 0,
            ],
            5 => [
                'id' => 5,
                'name' => 'State',
                'code' => 'state',
                'enable' => 1,
                'required' => 1,
                'native' => 0,
            ],
            6 => [
                'id' => 6,
                'name' => 'City',
                'code' => 'text',
                'enable' => 1,
                'required' => 1,
                'native' => 0,
            ],
            8 => [
                'id' => 8,
                'name' => 'Post Code',
                'code' => 'postcode',
                'enable' => 1,
                'required' => 1,
                'native' => 0,
            ],
            9 => [
                'id' => 9,
                'name' => 'Telephone',
                'code' => 'text',
                'enable' => 1,
                'required' => 1,
                'native' => 0,
            ],
        );
    }
}
