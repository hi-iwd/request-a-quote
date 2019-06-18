<?php

namespace IWD\CartToQuote\Model\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use IWD\CartToQuote\Model\Logger\Logger;

/**
 * Class ApiRequest
 * @package IWD\CartToQuote\Model\Api
 */
abstract class ApiRequest
{
    /**
     * @var bool
     */
    private $logEnable = false; //TODO: remove

    /**
     * @var Curl
     */
    private $curl;

    /**
     * @var array
     */
    private $response;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Token
     */
    private $token;

    /**
     * @var bool
     */
    private $connectionError = false;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * ApiRequest constructor.
     * @param Curl $curl
     * @param StoreManagerInterface $storeManager
     * @param Token $token
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     */
    public function __construct(
        Curl $curl,
        StoreManagerInterface $storeManager,
        Token $token,
        ScopeConfigInterface $scopeConfig,
        Logger $logger
    ) {
        $this->curl = $curl;
        $this->storeManager = $storeManager;
        $this->token = $token;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Framework\Session\SessionManager
     */
    abstract public function getStorageForTemporaryToken();

    /**
     * @return bool
     */
    public function isTokenExists()
    {
        return !empty($this->getToken());
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->getStorageForTemporaryToken()->getData(Token::XPATH_TEMPORARY_TOKEN);
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return Token::SECRET_KEY;
    }

    /**
     * @return string
     */
    public function getDomain()
    {

        $defaultStore = $this->storeManager->getDefaultStoreView();
        if (!$defaultStore) {
            $allStores = $this->storeManager->getStores();
            if (isset($allStores[0])) {
                $defaultStore = $allStores[0];
            }
        }
        $url = $defaultStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK);

        $url = trim($url, '/');
        $protocols = ['http://', 'https://'];
        foreach ($protocols as $protocol) {
            if (strpos($url, $protocol) === 0) {
                return str_replace($protocol, '', $url);
            }
        }

        return $url;
    }

    public function getUrl($action = '', $params = [])
    {
        $url = Token::getApiUrl();
        if (!empty($action)) {
            $url .= $action . '/';
        }

        if (!empty($params)) {
            $url .= base64_encode(json_encode($params));
        }

        return $url;
    }

    /**
     * @param $action
     * @param $params
     * @param $extraOptions
     */
    public function request($action, $params, $extraOptions = [])
    {
        $url = $this->getUrl($action, $params);

        $this->log(
            PHP_EOL . 'REQUEST: '
            . $action . PHP_EOL
            . json_encode($params) . PHP_EOL
            . json_encode($extraOptions) . PHP_EOL
            . $url . PHP_EOL
        );

        $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, 0);

        try {
            $this->curl->post($url, $extraOptions);
            $this->connectionError = false;
        } catch (\Exception $e) {
            $this->connectionError = true;
        }

        $response = $this->curl->getBody();

//        echo "<pre>";
//        var_dump($action);
//        var_dump($response);
//        die();

        $this->log('RESPONSE: ' . PHP_EOL . $response . PHP_EOL);

        $this->parseResponse($response);
    }

    public function log($message)
    {
        if ($this->logEnable) {
            $this->logger->info($message);
        }
    }

    /**
     * @param $response
     * @throws LocalizedException
     */
    public function parseResponse($response)
    {
        $this->response = (array)json_decode($response, true);
        $this->getResponse('status');
    }

    /**
     * @param null|string $key
     * @return array|string|int|float
     * @throws LocalizedException
     */
    public function getResponse($key = null)
    {
        if (!empty($key)) {
            if (isset($this->response[$key])) {
                return $this->response[$key];
            } else {
                throw new LocalizedException(__('Incorrect response format. Can not get param %1', $key));
            }
        }
        return $this->response;
    }

    /**
     * @return int
     * @throws LocalizedException
     */
    public function getResponseCode()
    {
        try {
            return $this->getResponse('status');
        } catch (\Exception $e) {
            return -1;
        }
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getResponseHtml()
    {
        return $this->getResponse('html');
    }

    /**
     * @return bool
     */
    public function isResponseOk()
    {
        return $this->getResponseCode() == Error::RESPONSE_WITHOUT_ERROR;
    }

    /**
     * @return bool
     */
    public function isResponseError()
    {
        return $this->getResponseCode() != Error::RESPONSE_WITHOUT_ERROR;
    }

    /**
     * @return bool
     */
    public function isConnectionError()
    {
        return $this->connectionError;
    }

    /**
     * @return Token
     */
    public function getTokenModel()
    {
        return $this->token;
    }

    /**
     * @throws LocalizedException
     */
    public function saveNewToken()
    {
        $token = $this->getResponse('token');
        $this->getStorageForTemporaryToken()->setData(Token::XPATH_TEMPORARY_TOKEN, $token);
    }

    /**
     * @return string
     */
    public function getOwnerEmail()
    {
        return $this->scopeConfig->getValue('iwd_cart_to_quote/general/purchased_email');
    }
}
