<?php
/**
 * Created by PhpStorm.
 * User: vlad
 * Date: 02.07.2018
 * Time: 12:34
 */

namespace IWD\CartToQuote\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Area;
use Magento\Store\Model\Store;
use IWD\CartToQuote\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class EmailNotification
 * @package IWD\CartToQuote\Helper
 */
class EmailNotification extends AbstractHelper
{
    /**
     *
     */
    const XML_PATH_QUOTE_REQUESTED_ADMIN = 'iwd_cart_to_quote/email_notifications/quote_requested_admin';
    /**
     *
     */
    const XML_PATH_QUOTE_RECEIVED = 'iwd_cart_to_quote/email_notifications/quote_received';
    /**
     *
     */
    const XML_PATH_QUOTE_EXPIRED = 'iwd_cart_to_quote/email_notifications/quote_expired';
    /**
     *
     */
    const XML_PATH_QUOTE_APPROVED = 'iwd_cart_to_quote/email_notifications/quote_approved';
    /**
     *
     */
    const XML_PATH_QUOTE_REJECTED = 'iwd_cart_to_quote/email_notifications/quote_rejected';
    /**
     *
     */
    const XML_PATH_GENERAL_EMAIL_IDENTITY = 'trans_email/ident_general/email';
    /**
     *
     */
    const XML_PATH_GENERAL_NAME_IDENTITY = 'trans_email/ident_general/name';

    /**
     * @var StateInterface
     */
    protected $inlineTranslation;
    /**
     * @var Escaper
     */
    protected $escaper;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    /**
     * @var \IWD\CartToQuote\Helper\Data
     */
    protected $helper;
    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * @var TransportBuilder
     */
    protected $_transportBuilder;
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var
     */
    protected $emailTemplate;


    /**
     * EmailNotification constructor.
     * @param Context $context
     * @param StateInterface $inlineTranslation
     * @param Escaper $escaper
     * @param TransportBuilder $transportBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param \IWD\CartToQuote\Helper\Data $helper
     */
    public function __construct(
        Context $context,
        StateInterface $inlineTranslation,
        Escaper $escaper,
        TransportBuilder $transportBuilder,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Data $helper
    )
    {
        parent::__construct($context);
        $this->inlineTranslation = $inlineTranslation;
        $this->escaper = $escaper;
        $this->_transportBuilder = $transportBuilder;
        $this->helper = $helper;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->logger = $context->getLogger();
    }

    /**
     * @param $quoteStatus
     */
    public function initEmailTemplate($quoteStatus)
    {
        switch ($quoteStatus) {
            case Data::QUOTE_EXPIRED_STATUS:
                $emailTemplate = self::XML_PATH_QUOTE_EXPIRED;
                break;
            case 'approved':
                $emailTemplate = self::XML_PATH_QUOTE_APPROVED;
                break;
            case 'rejected':
                $emailTemplate = self::XML_PATH_QUOTE_REJECTED;
                break;
            case 'requested_admin':
                $emailTemplate = self::XML_PATH_QUOTE_REQUESTED_ADMIN;
                break;
            case 'requested':
                $emailTemplate = self::XML_PATH_QUOTE_RECEIVED;
                break;
            default:
                $emailTemplate = false;
        }
        $this->emailTemplate = $emailTemplate;
    }

    /**
     * @return mixed
     */
    public function getEmailTemplate()
    {
        return $this->emailTemplate;
    }

    /**
     * @param $quote
     * @param $receiver
     * @return $this
     */
    public function sendEmail($quote, $receiver = null)
    {
        $emailTemplate = $this->getEmailTemplate();
        try {
            if (!$this->_scopeConfig->getValue(
                $emailTemplate,
                ScopeInterface::SCOPE_STORE
            )
            ) {
                return $this;
            }
            $sender['email'] = $this->_scopeConfig->getValue(
                self::XML_PATH_GENERAL_EMAIL_IDENTITY,
                ScopeInterface::SCOPE_STORE
            );
            $sender['name'] = $this->_scopeConfig->getValue(
                self::XML_PATH_GENERAL_NAME_IDENTITY,
                ScopeInterface::SCOPE_STORE
            );
            if (!$receiver) {
                $receiver = new \Magento\Framework\DataObject();
                $name = $quote->getCustomer()->getFirstName() . ' ' . $quote->getCustomer()->getLastName();
                $receiver->setName($name);
                $receiver->setEmail($quote->getBillingAddress()->getEmail());
            }
            $this->inlineTranslation->suspend();
            $store = $this->_storeManager->getStore();
            $this->_transportBuilder->setTemplateIdentifier(
                $this->_scopeConfig->getValue(
                    $emailTemplate,
                    ScopeInterface::SCOPE_STORE
                )
            )->setTemplateOptions(
                [
                    'area' => Area::AREA_FRONTEND,
                    'store' => $store->getId(),
                ]
            )->setTemplateVars(
                ['quote' => $quote, 'receiver' => $receiver, 'store' => $store]
            )->setFrom($sender)
                ->addTo(
                    $receiver->getEmail(),
                    $receiver->getName()
                );
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();

            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return $this;
    }
}