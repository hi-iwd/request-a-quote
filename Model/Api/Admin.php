<?php

namespace IWD\CartToQuote\Model\Api;

use IWD\CartToQuote\Model\Logger\Logger;
use Magento\Backend\Model\Auth\Session as AdminSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Customer
 * @package IWD\CartToQuote\Model\Api
 */
class Admin extends ApiRequest
{
    /**
     * @var AdminSession
     */
    private $adminSession;

    /**
     * Admin constructor.
     * @param Curl $curl
     * @param StoreManagerInterface $storeManager
     * @param Token $token
     * @param ScopeConfigInterface $scopeConfig
     * @param AdminSession $adminSession
     * @param Logger $logger
     */
    public function __construct(
        Curl $curl,
        StoreManagerInterface $storeManager,
        Token $token,
        ScopeConfigInterface $scopeConfig,
        AdminSession $adminSession,
        Logger $logger
    )
    {
        parent::__construct($curl, $storeManager, $token, $scopeConfig, $logger);
        $this->adminSession = $adminSession;
    }

    /**
     * {@inheritdoc}
     */
    public function getStorageForTemporaryToken()
    {
        return $this->adminSession;
    }

    /**
     * @return \Magento\User\Model\User
     */
    public function getUser()
    {
        return $this->adminSession->getUser();
    }

    /**
     * authorize or register admin user and get temporary token
     */
    public function authOrRegisterAdmin()
    {
        if (empty($this->getTokenModel()->getAdminKey())) {
            throw new LocalizedException(__('admin key is empty'));
        }

        $this->authAdmin();
        if ($this->getResponseCode() == Error::USER_DOES_NOT_EXISTS) {
            $this->registerAdmin();
        }

        if ($this->getResponseCode() == Error::RESPONSE_WITHOUT_ERROR) {
            $this->saveNewToken();
        } else {
            throw new LocalizedException(__('Can not authorize. Error #%1', $this->getResponseCode()));
        }
    }

    private function authAdmin()
    {
        $this->request(
            'admin/auth',
            [
                'email' => $this->getUser()->getEmail(),
                'owner_email' => $this->getOwnerEmail(),
                'domain' => $this->getDomain(),
                'admin_key' => $this->getTokenModel()->getAdminKey(),
                'key' => $this->getKey(),
                'user_role' => 'admin'
            ]
        );
    }

    private function registerAdmin()
    {
        $this->request(
            'admin/register',
            [
                'email' => $this->getUser()->getEmail(),
                'domain' => $this->getDomain(),
                'firstname' => $this->getUser()->getFirstname(),
                'lastname' => $this->getUser()->getLastname(),
                'admin_key' => $this->getTokenModel()->getAdminKey(),
                'key' => $this->getKey(),
                'user_role' => 'admin'
            ]
        );
    }

    /**
     * Register new store and owner (admin)
     * (after enter license email)
     *
     * @param $ownerEmail
     */
    public function registerStoreAndOwner($ownerEmail)
    {
        $this->request(
            'admin/store/register',
            [
                'email' => $ownerEmail,
                'domain' => $this->getDomain(),
                'firstname' => $this->getUser()->getFirstname(),
                'lastname' => $this->getUser()->getLastname(),
                'admin_key' => $this->createAdminKey(),
                'user_key' => $this->createCustomerKey(),
                'key' => $this->getKey(),
            ]
        );
    }

    /**
     * @param $email
     * @param array $attributes
     */
    public function updateStoreSettings($email, $attributes = [])
    {
        if (!empty($attributes)) {
            $this->request(
                'admin/store/updateSettings',
                [
                    'email' => $email,
                    'domain' => $this->getDomain(),
                    'admin_key' => $this->getTokenModel()->getAdminKey() ? $this->getTokenModel()->getAdminKey() : $this->createAdminKey(),
                    'firstname' => $this->getUser()->getFirstname(),
                    'lastname' => $this->getUser()->getLastname(),
                    'user_key' => $this->createCustomerKey(),
                    'key' => $this->getKey(),
                ],
                $attributes
            );
        }
    }

    /**
     * @return string
     */
    private function createAdminKey()
    {
        $key = $this->getTokenModel()->generateSecretKey();
        $this->getTokenModel()->saveAdminKey($key);

        return $key;
    }

    /**
     * @return string
     */
    private function createCustomerKey()
    {
        $key = $this->getTokenModel()->generateSecretKey();
        $this->getTokenModel()->saveCustomerKey($key);

        return $key;
    }

    /**
     * Update new store and owner (admin)
     * (after update license email)
     *
     * @param $oldEmail
     * @param $newEmail
     */
    public function updateStoreAndOwner($oldEmail, $newEmail)
    {
        $this->request(
            'admin/store/update',
            [
                'old_email' => $oldEmail,
                'new_email' => $newEmail,
                'domain' => $this->getDomain(),
                'firstname' => $this->getUser()->getFirstname(),
                'lastname' => $this->getUser()->getLastname(),
                'admin_key' => $this->getTokenModel()->getAdminKey(),
                'key' => $this->getKey(),
            ]
        );
    }

    /**
     * @return bool
     */
    public function isAdminKeyExists()
    {
        return empty($this->getTokenModel()->getAdminKey());
    }

    public function loadQuotesGrid()
    {
        if (!$this->isTokenExists()) {
            throw new LocalizedException(__('You are not authorized.'));
        }

        $this->request(
            'admin/quote/grid',
            [
                'email' => $this->getUser()->getEmail(),
                'domain' => $this->getDomain(),
                'token' => $this->getToken(),
                'key' => $this->getKey(),
            ]
        );

        if ($this->getResponseCode() != Error::RESPONSE_WITHOUT_ERROR) {
            throw new LocalizedException(__('Error [%1] during load quotes grid', $this->getResponseCode()));
        }

        $this->saveNewToken();
    }
}
