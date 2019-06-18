<?php

namespace IWD\CartToQuote\Controller\Ajax;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Api\CustomerRepositoryInterface;
use IWD\CartToQuote\Model\Logger\Logger;
use IWD\CartToQuote\Helper\EmailNotification;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class RequestQuote
 * @package IWD\CartToQuote\Controller\Ajax
 */
class RequestQuote extends Action
{
    /**
     * @var \IWD\CartToQuote\Helper\Data
     */
    private $c2qHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var \IWD\CartToQuote\Model\Api\Quote
     */
    private $hostedQuote;

    /**
     * @var \IWD\CartToQuote\Model\Api\Customer
     */
    private $hostedCustomer;

    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory
     */
    private $customerFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var GroupManagementInterface
     */
    private $customerGroupManagement;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    private $modelCustomer;
    /**
     * @var EmailNotification
     */
    private $notificationHelper;
    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepositoryInterface;
    /**
     * @var
     */
    private $quote;

    /**
     * RequestQuote constructor.
     * @param Context $context
     * @param \IWD\CartToQuote\Helper\Data $c2qHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     * @param \IWD\CartToQuote\Model\Api\Customer $customer
     * @param \IWD\CartToQuote\Model\Api\Quote $hostedQuote
     * @param \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory
     * @param StoreManagerInterface $storeManager
     * @param GroupManagementInterface $customerGroupManagement
     * @param CustomerSession $customerSession
     * @param AccountManagementInterface $accountManagement
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Model\Customer $modelCustomer
     * @param EmailNotification $notificationHelper
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        \IWD\CartToQuote\Helper\Data $c2qHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \IWD\CartToQuote\Model\Api\Customer $customer,
        \IWD\CartToQuote\Model\Api\Quote $hostedQuote,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory,
        StoreManagerInterface $storeManager,
        GroupManagementInterface $customerGroupManagement,
        CustomerSession $customerSession,
        AccountManagementInterface $accountManagement,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\Customer $modelCustomer,
        EmailNotification $notificationHelper,
        ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        Logger $logger
    )
    {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->c2qHelper = $c2qHelper;
        $this->hostedCustomer = $customer;
        $this->hostedQuote = $hostedQuote;
        $this->accountManagement = $accountManagement;
        $this->customerFactory = $customerFactory;
        $this->storeManager = $storeManager;
        $this->customerGroupManagement = $customerGroupManagement;
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->modelCustomer = $modelCustomer;
        $this->notificationHelper = $notificationHelper;
        $this->_scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $responseData = [];
        $useMagentoQuote = true;
        try {
            $iwdQuoteId = (int)$this->getParam('iwd_quote_id');

            if ($iwdQuoteId) {
                $this->quote = $this->cartRepositoryInterface->get($iwdQuoteId);
                $useMagentoQuote = false;

                if ($this->quote->getIsIwdDisabledReorder()) {
                    return false;
                }
            } elseif (!$this->checkoutSession->getQuote() && !$this->checkoutSession->getQuote()->getId()) {
                throw new LocalizedException(__('Quote is empty'));
            }

            if ($useMagentoQuote) {
                $this->quote = $this->checkoutSession->getQuote();
            }

            $customer = $this->loadCustomer();

            if (empty($customer) || empty($customer->getId())) {
                $customer = $this->registerNewUser();
            }

            $this->authOrRegisterHostedCustomer($customer);
            $this->sendEmailNotifications();
            $this->sendQuoteToHostedServer();
            $this->disableQuote($customer);

            $responseData['status'] = true;
            $responseData['message'] = $this->c2qHelper->getSuccessMessage();
            $this->messageManager->addSuccessMessage($responseData['message']);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $responseData['status'] = false;
            $responseData['message'] = __('Sorry, but something went wrong.');
            $this->messageManager->addErrorMessage($responseData['message']);
        }

        /**
         * @var \Magento\Framework\Controller\Result\Json $resultJson
         */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseData);
        return $resultJson;
    }

    /**
     *
     */
    private function sendEmailNotifications()
    {
        $quote = $this->quote;
        $notificationHelper = $this->notificationHelper;
        $email = $this->_scopeConfig->getValue('trans_email/ident_general/email', ScopeInterface::SCOPE_STORE);
        $name = $this->_scopeConfig->getValue('trans_email/ident_general/name', ScopeInterface::SCOPE_STORE);
        $receiver = new \Magento\Framework\DataObject();
        $receiver->setName($name);
        $receiver->setEmail($email);
        $notificationHelper->initEmailTemplate('requested_admin');
        $notificationHelper->sendEmail($quote, $receiver);

        $notificationHelper->initEmailTemplate('requested');
        $notificationHelper->sendEmail($quote);
    }

    /**
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     */
    private function loadCustomer()
    {
        try {
            $email = $this->getParam('email');
            $store = $this->storeManager->getStore();

            return $this->customerRepository->get($email, $store->getWebsiteId());
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function registerNewUser()
    {
        $store = $this->storeManager->getStore();
        $customer = $this->customerFactory->create();
        $customer->setGroupId(
            $this->customerGroupManagement->getDefaultGroup($store->getId())->getId()
        );

        $customer->setWebsiteId($store->getWebsiteId());
        $customer->setStoreId($store->getId());
        $customer->setEmail($this->getParam('email'));
        $customer->setFirstname($this->getParam('first_name'));
        $customer->setLastname($this->getParam('last_name'));

        $customer = $this->accountManagement->createAccount($customer);

        $this->_eventManager->dispatch(
            'customer_register_success',
            ['account_controller' => $this, 'customer' => $customer]
        );

        return $customer;
    }

    /**
     * @param $key
     * @param string $default
     * @return string
     */
    private function getParam($key, $default = '')
    {
        $params = $this->getRequest()->getParam('iwd_c2q_data');
        return isset($params[$key]) ? $params[$key] : $default;
    }

    /**
     * @throws LocalizedException
     */
    private function authOrRegisterHostedCustomer($customer)
    {
        $this->hostedCustomer->authOrRegisterCustomer($customer);
        if (!$this->hostedCustomer->isTokenExists()) {
            throw new LocalizedException(__('Token does not exists'));
        }
    }

    /**
     * @inheritdoc
     */
    public function sendQuoteToHostedServer()
    {
        $quote = $this->quote;

        $email = $this->getParam('email');
        $additionalData = $this->getRequest()->getParam('iwd_c2q_data', []);

        $user = $this->customerSession->getCustomer();
        try {
            $user->setData('iwd_c2q_customer_data', json_encode($additionalData))->save();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        $this->hostedQuote->addNewQuote($quote, $email, $additionalData);
    }

    /**
     * @param $customer
     */
    public function disableQuote($customer)
    {
        $quote = $this->quote;
        $this->checkoutSession->clearHelperData();
        $quote->setIsActive(false);
        $quote->setCustomer($customer);
        $quote->setEditableItems(0);
        $quote->setIsIwdDisabledReorder(true);
        $this->quoteRepository->save($quote);
    }
}
