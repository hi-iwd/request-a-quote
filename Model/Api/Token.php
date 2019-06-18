<?php

namespace IWD\CartToQuote\Model\Api;

use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Token
 * @package IWD\CartToQuote\Model\Api
 */
class Token
{
    const SECRET_KEY = '51a6b7cc6465fdbe1391';

    const XPATH_TEMPORARY_TOKEN = 'iwd_c2q_token';
    const XPATH_SECURITY_KEY_ADMIN = 'iwd_cart_to_quote/general/admin_key';
    const XPATH_SECURITY_KEY_CUSTOMER = 'iwd_cart_to_quote/general/customer_key';
    const XPATH_OWNER_EMAIL = 'iwd_cart_to_quote/general/purchased_email';

    /**
     * @var Config
     */
    private $resourceConfig;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Admin constructor.
     * @param Config $resourceConfig
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Config $resourceConfig,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->resourceConfig = $resourceConfig;
        $this->scopeConfig = $scopeConfig;
    }

    public static function getApiUrl()
    {
        return 'https://raq.iwdagency.com/';
    }

    /**
     * We will add secret key for each request.
     * It's permanent secret key for all requests.
     * @return string
     */
    public function getKey()
    {
        return self::SECRET_KEY;
    }

    /**
     * Generate secret key
     * We will use it as admin or customer project key
     * @return string
     */
    public function generateSecretKey()
    {
        return sha1(time() . random_int(0, 9999) . self::SECRET_KEY);
    }

    /**
     * Save admin project key
     * It's secret key for admin login and register requests
     * @param $key
     */
    public function saveAdminKey($key)
    {
        $this->resourceConfig->saveConfig(self::XPATH_SECURITY_KEY_ADMIN, $key, 'default', 0);
    }

    /**
     * Get admin project key
     * It's secret key for admin login and register requests
     * @return string
     */
    public function getAdminKey()
    {
        return $this->scopeConfig->getValue(self::XPATH_SECURITY_KEY_ADMIN, 'default', 0);
    }

    /**
     * Save customer project key
     * It's secret key for customer login and register requests
     * @param $key
     */
    public function saveCustomerKey($key)
    {
        $this->resourceConfig->saveConfig(self::XPATH_SECURITY_KEY_CUSTOMER, $key, 'default', 0);
    }

    /**
     * Get customer project key
     * It's secret key for customer login and register requests
     * @return string
     */
    public function getCustomerKey()
    {
        return $this->scopeConfig->getValue(self::XPATH_SECURITY_KEY_CUSTOMER, 'default', 0);
    }

    /**
     * Reset admin key and customer key
     */
    public function resetKeys()
    {
        $this->resourceConfig->saveConfig(self::XPATH_SECURITY_KEY_ADMIN, '', 'default', 0);
        $this->resourceConfig->saveConfig(self::XPATH_SECURITY_KEY_CUSTOMER, '', 'default', 0);
        $this->resourceConfig->saveConfig(self::XPATH_OWNER_EMAIL, '', 'default', 0);
    }
}
