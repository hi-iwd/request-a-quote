<?php

namespace IWD\CartToQuote\Controller\Ajax;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\UserLockedException;

/**
 * Class Login
 * @package IWD\CartToQuote\Controller\Ajax
 */
class Login extends Action
{
    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    private $customerAccountManagement;

    /**
     * @var \Magento\Customer\Model\Account\Redirect
     */
    private $accountRedirect;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $session;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private $cookieMetadataManager;

    /**
     * Login constructor.
     * @param Context $context
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\Stdlib\Cookie\PhpCookieManager $cookieMetadataManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Api\AccountManagementInterface $customerAccountManagement
     * @param \Magento\Customer\Model\Account\Redirect $accountRedirect
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Stdlib\Cookie\PhpCookieManager $cookieMetadataManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\AccountManagementInterface $customerAccountManagement,
        \Magento\Customer\Model\Account\Redirect $accountRedirect
    ) {
        $this->session = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->cookieMetadataManager = $cookieMetadataManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->accountRedirect = $accountRedirect;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $responseData = $this->login();
        /**
         * @var \Magento\Framework\Controller\Result\Json $resultJson
         */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        return $resultJson->setData($responseData);
    }

    /**
     * @return array
     */
    public function login()
    {
        $responseData = [
            'status' => false
        ];
        if ($this->session->isLoggedIn()) {
            $responseData['status'] = true;
        } else {
            if ($this->getRequest()->isPost()) {
                $login = $this->getRequest()->getPost('iwd_c2q_login');
                if (!empty($login['username']) && !empty($login['password'])) {
                    try {
                        $customer = $this->customerAccountManagement->authenticate($login['username'], $login['password']);
                        //TODO add check if this is b2b site
                        $this->session->setCustomerDataAsLoggedIn($customer);
                        $this->session->regenerateId();
                        if ($this->cookieMetadataManager->getCookie('mage-cache-sessid')) {
                            $metadata = $this->cookieMetadataFactory->createCookieMetadata();
                            $metadata->setPath('/');
                            $this->cookieMetadataManager->deleteCookie('mage-cache-sessid', $metadata);
                        }

                        $redirectUrl = $this->accountRedirect->getRedirectCookie();
                        $responseData['status'] = true;
                        if (!$this->scopeConfig->getValue('customer/startup/redirect_dashboard') && $redirectUrl) {
                            $this->accountRedirect->clearRedirectCookie();
                            $resultRedirect = $this->resultRedirectFactory->create();
                            // URL is checked to be internal in $this->_redirect->success()
                            $resultRedirect->setUrl($this->_redirect->success($redirectUrl));
                            $redirectUrl = $resultRedirect;
                        }

                        $responseData['redirect'] = $redirectUrl;
                    } catch (EmailNotConfirmedException $e) {
                        $responseData['message'] = __('This account is not confirmed.');
                    } catch (UserLockedException $e) {
                        $responseData['message'] =  __(
                            'The account is locked. Please wait and try again or contact %1.',
                            $this->scopeConfig->getValue('contact/email/recipient_email')
                        );
                    } catch (AuthenticationException $e) {
                        $responseData['message'] = __('Invalid login or password.');
                    } catch (LocalizedException $e) {
                        $responseData['message'] = $e->getMessage();
                    } catch (\Exception $e) {
                        $responseData['message'] =
                            __('An unspecified error occurred. Please contact us for assistance.');
                    }
                } else {
                    $responseData['message'] = __('A login and a password are required.');
                }
            } else {
                $responseData['message'] = __('A login and a password are required.');
            }
        }

        return $responseData;
    }
}
