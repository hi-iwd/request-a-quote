<?php

namespace IWD\CartToQuote\Block\Widget\Fields;

use Magento\Framework\View\Element\Template;
use Magento\Directory\Model\ResourceModel\Country\Collection as CountryCollection;

class Country extends Template
{
    use AbstractField;

    /**
     * @var CountryCollection
     */
    private $countryCollection;

    /**
     * @var null|array
     */
    private $countries = null;

    /**
     * Country constructor.
     * @param Template\Context $context
     * @param CountryCollection $countryCollection
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CountryCollection $countryCollection,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->countryCollection = $countryCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('IWD_CartToQuote::widget/country.phtml');
    }

    /**
     * @return mixed
     */
    public function getDefaultCountry()
    {
        return $this->_scopeConfig->getValue(\Magento\Shipping\Model\Config::XML_PATH_ORIGIN_COUNTRY_ID);
    }

    /**
     * @return array|null
     */
    public function getCountries()
    {
        if (!$this->countries) {
            $this->countries = $this->countryCollection->loadByStore()->toOptionArray(__('Please select a country'));
        }

        return $this->countries;
    }
}
