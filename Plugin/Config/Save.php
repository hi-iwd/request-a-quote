<?php

namespace IWD\CartToQuote\Plugin\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Store\Model\StoreRepository;
use Magento\Store\Model\StoreManagerInterface;
use IWD\CartToQuote\Model\Agreement;
use IWD\CartToQuote\Model\Api\Error;
use IWD\CartToQuote\Model\Api\Admin;
use IWD\CartToQuote\Model\Api\Token;
use IWD\CartToQuote\Model\Logger\Logger;

/**
 * Class Save
 * @package IWD\CartToQuote\Plugin\Config
 */
class Save
{
    /**
     * You can set this email for reset keys and email
     */
    const RESET_KEY = 'reset@reset.reset';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Config
     */
    private $resourceConfig;

    /**
     * @var MessageManagerInterface
     */
    private $messageManager;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Admin
     */
    private $admin;

    /**
     * @var string
     */
    private $oldEmail;

    /**
     * @var bool
     */
    private $isError = false;

    /**
     * @var
     */
    private $cacheTypeList;

    /**
     * @var Agreement
     */
    private $checkoutAgreements;

    /**
     * @var StoreRepository
     */
    private $storeRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Save constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param Config $resourceConfig
     * @param MessageManagerInterface $messageManager
     * @param Logger $logger
     * @param Admin $admin
     * @param TypeListInterface $cacheTypeList
     * @param Agreement $checkoutAgreements
     * @param StoreRepository $storeRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Reader $configReader,
        ScopeConfigInterface $scopeConfig,
        Config $resourceConfig,
        MessageManagerInterface $messageManager,
        Logger $logger,
        Admin $admin,
        TypeListInterface $cacheTypeList,
        Agreement $checkoutAgreements,
        StoreRepository $storeRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->configReader = $configReader;
        $this->scopeConfig = $scopeConfig;
        $this->resourceConfig = $resourceConfig;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
        $this->admin = $admin;
        $this->cacheTypeList = $cacheTypeList;
        $this->checkoutAgreements = $checkoutAgreements;
        $this->storeRepository = $storeRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\Config\Model\Config $subject
     * @param \Closure $proceed
     * @return mixed
     */
    public function aroundSave(\Magento\Config\Model\Config $subject, \Closure $proceed)
    {
        if ($subject->getData('section') == 'iwd_cart_to_quote') {
            $_this = $proceed();
            $this->registerOrUpdateOwner($subject);
            $this->updateStoreAttributes();
            $this->resetEmail($subject);
            $this->cleanConfigCache();
        } elseif ($subject->getData('section') == 'web') {
            $this->updateStoreAttributes();
            $_this = $proceed();
        } else {
            $_this = $proceed();
        }

        return $_this;
    }

    /**
     * @param \Magento\Config\Model\Config $subject
     */
    private function registerOrUpdateOwner($subject)
    {
        $this->oldEmail = $this->scopeConfig->getValue(Token::XPATH_OWNER_EMAIL);
        $newEmail = $subject->getData('groups/general/fields/purchased_email/value');

        if ($this->oldEmail != $newEmail && $newEmail != self::RESET_KEY) {
            empty($this->oldEmail && !$this->admin->isAdminKeyExists())
                ? $this->registerOwner($newEmail)
                : $this->updateOwner($this->oldEmail, $newEmail);
        }
    }

    /**
     * You can reset license email and admin/user secret keys
     * Don't do it if you don't know what you do
     *
     * @param $subject
     */
    private function resetEmail($subject)
    {
        $newEmail = $subject->getData('groups/general/fields/purchased_email/value');
        if ($newEmail == self::RESET_KEY) {
            $this->resourceConfig->saveConfig(Token::XPATH_SECURITY_KEY_ADMIN, '', 'default', 0);
            $this->resourceConfig->saveConfig(Token::XPATH_SECURITY_KEY_CUSTOMER, '', 'default', 0);
            $this->resourceConfig->saveConfig(Token::XPATH_OWNER_EMAIL, '', 'default', 0);
        }

        if ($this->isError) {
            $this->resourceConfig->saveConfig(Token::XPATH_OWNER_EMAIL, $this->oldEmail, 'default', 0);
        }
    }

    /**
     * @param $newEmail
     */
    private function registerOwner($newEmail)
    {
        $this->admin->registerStoreAndOwner($newEmail);
        $this->handleResponse();
    }

    /**
     * @param $oldEmail
     * @param $newEmail
     */
    private function updateOwner($oldEmail, $newEmail)
    {
        $this->admin->updateStoreAndOwner($oldEmail, $newEmail);
        $this->handleResponse();
    }

    /**
     * Handle response
     */
    private function handleResponse()
    {
        $responseCode = $this->admin->getResponseCode();

        if ($responseCode == Error::RESPONSE_WITHOUT_ERROR) {
            $this->messageManager->addSuccessMessage(
                'Your store has been successfully connected to Cart2Quote service.'
            );
        } else {
            $this->isError = true;

            if ($responseCode == Error::STORE_EXISTS) {
                $this->messageManager->addErrorMessage(
                    'Sorry, but this store has been registered for another email. ' .
                    'Please, enter correct email or contact with IWD Agency support for resolve this issue.'
                );
            } else {
                $this->messageManager->addErrorMessage(
                    'An error occurred when applying these changes; please make sure your email is correct.'
                );
            }

            $this->logger->error('IWD C2Q issue: ' . $responseCode);
        }
    }

    /**
     * @param $subject
     */
    public function updateStoreAttributes($subject = null)
    {
        if ($subject == null) {
            $email = $this->scopeConfig->getValue(Token::XPATH_OWNER_EMAIL);
            $requestForm = $this->scopeConfig->getValue('iwd_cart_to_quote/fields/fields');
            $statuses = $this->scopeConfig->getValue('iwd_cart_to_quote/statuses/statuses');
        } else {
            $email = $subject->getData('groups/general/fields/purchased_email/value');
            $requestForm = $subject->getData('groups/fields/fields/fields/value');
            $statuses = $subject->getData('groups/statuses/fields/statuses/value');
        }

        if (!$this->isError && !empty($email)) {
            $this->admin->updateStoreSettings(
                $email,
                [
                    'request_form' => $requestForm,
                    'statuses' => $statuses,
                    'term_and_conditions' => $this->checkoutAgreements->getCheckoutAgreementsList(),
                    'stores' => $this->getStores()
                ]
            );
        }
    }

    /**
     * Clean config cache
     */
    private function cleanConfigCache()
    {
        $this->cacheTypeList->cleanType('config');
    }

    /**
     * @return string
     */
    private function getStores()
    {
        $stores = [];
        $storesList = $this->storeRepository->getList();

        $config = $this->configReader->load();
        $adminSuffix = $config['backend']['frontName'];

        foreach ($storesList as $store) {
            $stores[$store->getId()] = [
                'base_url' => $this->storeManager->getStore($store->getId())->getBaseUrl(),
                'admin_url' => $this->storeManager->getStore($store->getId())->getBaseUrl() . $adminSuffix
            ];
        }

        return json_encode($stores);
    }
}
