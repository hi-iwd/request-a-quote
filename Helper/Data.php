<?php

namespace IWD\CartToQuote\Helper;

use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Data
 * @package IWD\CartToQuote\Helper
 */
class Data extends AbstractHelper
{
    /**
     *
     */
    const ENABLE = 'iwd_cart_to_quote/general/enable';
    /**
     *
     */
    const REQUEST_TYPE = 'iwd_cart_to_quote/general/button_type';
    /**
     *
     */
    const SUCCESS_MESSAGE = 'iwd_cart_to_quote/general/success_message';
    /**
     *
     */
    const EXPIRATION_DATE = 'iwd_cart_to_quote/general/expiration_date';

    /**
     *
     */
    const QUOTE_EXPIRED_STATUS = 'expired';
    /**
     *
     */
    const QUOTE_CLOSED_STATUS = 'closed';
    /**
     *
     */
    const REQUEST_TYPE_BOTH = 1;
    /**
     *
     */
    const REQUEST_TYPE_QUOTE_ONLY = 0;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Config
     */
    private $resourceConfig;

    /**
     * @var CurlFactory
     */
    private $curlFactory;

    /**
     * @var null
     */
    private $apiResponse = null;

    /**
     * Data constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param Config $resourceConfig
     * @param CurlFactory $curlFactory
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Config $resourceConfig,
        CurlFactory $curlFactory
    )
    {
        $this->storeManager = $storeManager;
        $this->resourceConfig = $resourceConfig;
        $this->curlFactory = $curlFactory;
        parent::__construct($context);
    }

    /**
     * @return int
     */
    public function getDefaultExpiredDays()
    {
        $days = $this->scopeConfig->getValue(self::EXPIRATION_DATE);
        return empty($days) ? 30 : $days;
    }

    /**
     * @return bool
     */
    public function getEnable()
    {
        return (bool)$this->scopeConfig->getValue(self::ENABLE);
    }

    /**
     * @return int
     */
    public function getRequestType()
    {
        return (int)$this->scopeConfig->getValue(self::REQUEST_TYPE);
    }

    /**
     * @return string
     */
    public function getSuccessMessage()
    {
        $message = $this->scopeConfig->getValue(self::SUCCESS_MESSAGE);
        $message = !empty($message) ? $message : __('Your quote was successfully sent to our system');

        return $message;
    }

    /**
     * @return bool
     */
    public function getLicenseStatus()
    {
        if (!$this->scopeConfig->getValue('iwd_cart_to_quote/general/license_next_check')
            || $this->scopeConfig->getValue('iwd_cart_to_quote/general/license_next_check') < time()
        ) {
            $status = $this->checkIsAllow();
        } else {
            $status = $this->scopeConfig->getValue('iwd_cart_to_quote/general/license');
        }
        return (bool)$status;
    }

    /**
     * @return bool|string
     */
    public function checkIsAllow()
    {
        if ($this->apiResponse === null) {
            try {
                $request = $this->prepareRequest();

                $config = [
                    'timeout' => 15,
                    'header' => false,
                    'verifypeer' => 0,
                    'verifyhost' => 0
                ];

                $url = 'https://api.iwdagency.com/getLicense/';

                $http = $this->curlFactory->create();
                $http->setConfig($config);
                $http->write(\Zend_Http_Client::POST, $url . $request, '1.1');

                $response = $http->read();
                $http->close();

                $this->apiResponse = $this->parseResponse($response);
            } catch (\Exception $e) {
                $this->apiResponse = [
                    'Error' => 1,
                    'ErrorMessage' => $e->getMessage(),
                    'ErrorCode' => ($e->getCode() == 111)
                        ? 'email_empty'
                        : (($e->getCode() == 222) ? 'connect_error' : $e->getCode())
                ];
            }
        }

        $status = (bool)$this->apiResponse['Error'] === false;
        $this->resourceConfig->saveConfig('iwd_cart_to_quote/general/license', $status, 'default', 0);
        $this->resourceConfig->saveConfig('iwd_cart_to_quote/general/license_next_check', strtotime('+6 hours'), 'default', 0);

        if ($status === true) {
            (string)$status = "megaSaveCartToQuote";
        }

        return $status;
    }

    /**
     * @return null
     */
    public function getLastResponse()
    {
        return $this->apiResponse;
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function prepareRequest()
    {
        $clientEmail = $this->scopeConfig->getValue('iwd_cart_to_quote/general/purchased_email');
        if (empty($clientEmail)) {
            throw new \Exception('Email is empty. Please, enter valid purchased email.', 111);
        }

        $defaultStore = $this->storeManager->getDefaultStoreView();
        if (!$defaultStore) {
            $allStores = $this->storeManager->getStores();
            if (isset($allStores[0])) {
                $defaultStore = $allStores[0];
            }
        }
        $baseUrl = $defaultStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK);

        $requestJson = [
            'Domains' => $baseUrl,
            'ExtensionCode' => 'RequestQuote',
            'ClientEmail' => $clientEmail,
            'SecretCode' => 'IWDEXTENSIONS',
        ];

        return base64_encode(json_encode($requestJson));
    }

    /**
     * @param $response
     * @return array|bool|mixed|string
     */
    private function parseResponse($response)
    {
        if (empty($response)) {
            if ($this->checkDemoLicense()) {
                return ['Error' => 0];
            } else {
                return ['Error' => 'connect_error'];
            }
        }

        $p = strpos($response, "\r\n\r\n");
        if ($p !== false) {
            $response = substr($response, 0, $p);
            $response = substr($response, $p + 4);
        }

        $response = json_decode($response, true);

        if (!isset($response['Error'])) {
            if ($this->checkDemoLicense()) {
                return ['Error' => 0];
            } else {
                return ['Error' => 'connect_error'];
            }
        }
        $this->resourceConfig->saveConfig('iwd_cart_to_quote/general/last_successful_connection', time(), 'default', 0);
        return $response;
    }

    /**
     * @return bool
     */
    private function checkDemoLicense()
    {
        if (strtotime('+14 days', (int)$this->scopeConfig->getValue('iwd_cart_to_quote/general/last_successful_connection')) > time()) {
            if (strtotime('+1 day', (int)$this->scopeConfig->getValue('iwd_cart_to_quote/general/last_email_time')) < time()) {
                $this->resourceConfig->saveConfig('iwd_cart_to_quote/general/last_email_time', time(), 'default', 0);
            }
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        $response = $this->getLastResponse();
        if (isset($response['ErrorCode'])) {
            switch ($response['ErrorCode']) {
                case 'no_license':
                    return 'License Inactive';
                case 'domains_limit':
                    return 'License Inactive';
                case 'domain_banned':
                    return 'License Inactive';
                case 'add_domain_failed':
                    return 'License Inactive';
                case 'license_disabled':
                    return 'License Inactive';
                case 'license_expired':
                    return 'License Inactive';
                case 'email_empty':
                    return 'License Inactive';
                case 'connect_error':
                    return 'Connection error';
            }
        }

        return isset($response['ErrorMessage']) ? $response['ErrorMessage'] : 'API Error!';
    }

    /**
     * @return string
     */
    public function getHelpText()
    {
        $response = $this->getLastResponse();

        if (isset($response['ErrorCode'])) {
            $licensingInformation = '<a href="https://www.iwdagency.com/help/general-information/managing-your-product-license">licensing information</a>';
            $email = $this->scopeConfig->getValue('iwd_cart_to_quote/general/purchased_email');

            switch ($response['ErrorCode']) {
                case 'no_license':
                    return "We were unable to locate a license with your email $email. Please enter the email address used to purchase this product from IWD. To learn more, please review our $licensingInformation.";
                case 'domains_limit':
                    $domainsCount = isset($response["DomainsCount"]) ? (int)$response["DomainsCount"] : 3;
                    return "You have used the maximum of $domainsCount domains for your license. To learn more or to request support, please review our $licensingInformation.";
                case 'domain_banned':
                    return "This domain has been forbidden to use this licensed product. To learn more or to request support, please review our $licensingInformation.";
                case 'add_domain_failed':
                    return "We were unable to verify this domain with your product license. Please activate this domain in your customer account at IWD. To learn more or to request support, please review our $licensingInformation.";
                case 'license_disabled':
                    return "Your license has been disabled. To learn more or to request support, please review our $licensingInformation.";
                case 'license_expired':
                    return "Your license for this product has expired. To learn more or to request support, please review our $licensingInformation.";
                case 'email_empty':
                    return "Please enter the email address used to purchase this product in order to activate your license. To learn more or to request support, please review our $licensingInformation";
                case 'connect_error':
                    return 'Could not connect to server API.';
            }
        }

        return '';
    }

    /**
     * @param $name
     * @return mixed|null|string|string[]
     */
    public static function prepareCodeFromTitle($name)
    {
        $name = trim($name);
        $name = strtolower($name);
        $name = preg_replace("/[^a-z0-9\s]/", "_", $name);
        $name = trim($name);
        $name = str_replace(' ', '_', $name);

        return $name;
    }
}
