<?php

namespace IWD\CartToQuote\Model\Api;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use IWD\CartToQuote\Model\Logger\Logger;

/**
 * Class Customer
 * @package IWD\CartToQuote\Model\Api
 */
class Customer extends ApiRequest
{
    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var \Magento\Customer\Model\Customer|null
     */
    private $user = null;

    /**
     * Customer constructor.
     * @param Curl $curl
     * @param StoreManagerInterface $storeManager
     * @param Token $token
     * @param ScopeConfigInterface $scopeConfig
     * @param CustomerSession $customerSession
     * @param Logger $logger
     */
    public function __construct(
        Curl $curl,
        StoreManagerInterface $storeManager,
        Token $token,
        ScopeConfigInterface $scopeConfig,
        CustomerSession $customerSession,
        Logger $logger
    ) {
        parent::__construct($curl, $storeManager, $token, $scopeConfig, $logger);
        $this->customerSession = $customerSession;
    }

    /**
     * {@inheritdoc}
     */
    public function getStorageForTemporaryToken()
    {
        return $this->customerSession;
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    public function getUser()
    {
        if (empty($this->user)) {
            $this->user = $this->customerSession->getCustomer();
        }

        return $this->user;
    }

    /**
     * @param $user
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * authorize or register user and get temporary token
     * @param null $customer
     * @throws LocalizedException
     */
    public function authOrRegisterCustomer($customer = NULL)
    {
        if($customer) {
            $this->user = $customer;
        }

        if (empty($this->getTokenModel()->getAdminKey())) {
            throw new LocalizedException(__('User key is empty'));
        }

        $this->authCustomer();
        if ($this->getResponseCode() == Error::USER_DOES_NOT_EXISTS) {
            $this->registerCustomer();
        }

        if ($this->getResponseCode() == Error::RESPONSE_WITHOUT_ERROR) {
            $this->saveNewToken();
        } else {
            throw new LocalizedException(__('Can not authorize. Error #%1', $this->getResponseCode()));
        }
    }

    public function authCustomer()
    {
        $this->request(
            'user/auth',
            [
                'email' => $this->getUser()->getEmail(),
                'owner_email' => $this->getOwnerEmail(),
                'domain' => $this->getDomain(),
                'user_key' => $this->getTokenModel()->getCustomerKey(),
                'key' => $this->getKey(),
                'user_role' => 'user'
            ]
        );
    }

    public function registerCustomer()
    {
        $this->request(
            'user/register',
            [
                'email' => $this->getUser()->getEmail(),
                'domain' => $this->getDomain(),
                'firstname' => $this->getUser()->getFirstname(),
                'lastname' => $this->getUser()->getLastname(),
                'user_key' => $this->getTokenModel()->getCustomerKey(),
                'key' => $this->getKey(),
                'user_role' => 'customer'
            ]
        );
    }
}
