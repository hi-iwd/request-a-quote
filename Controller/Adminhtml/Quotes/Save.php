<?php

namespace IWD\CartToQuote\Controller\Adminhtml\Quotes;

use Magento\Backend\App\Action\Context;
use Magento\Quote\Model\QuoteFactory;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class Save
 * @package IWD\CartToQuote\Controller\Adminhtml\Quotes
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'IWD_CartToQuote::carttoquote_quotes_save';

    /**
     * @var MessageManagerInterface
     */
    protected $messageManager;
    protected $emailNotification;

    /**
     * Index constructor.
     * @param Context $context
     * @param QuoteFactory $quoteFactory
     * @param MessageManagerInterface $messageManager
     * @param \IWD\CartToQuote\Model\Api\Customer $customer
     * @param \IWD\CartToQuote\Helper\EmailNotification $emailNotification
     * @param \IWD\CartToQuote\Helper\Data $helper
     * @param \IWD\CartToQuote\Model\Api\Quote $hostedQuote
     * @param DateTime $dateTime
     */
    public function __construct(
        Context $context,
        QuoteFactory $quoteFactory,
        MessageManagerInterface $messageManager,
        \IWD\CartToQuote\Model\Api\Customer $customer,
        \IWD\CartToQuote\Helper\EmailNotification $emailNotification,
        \IWD\CartToQuote\Helper\Data $helper,
        \IWD\CartToQuote\Model\Api\Quote $hostedQuote,
        DateTime $dateTime
    )
    {
        parent::__construct($context);
        $this->quoteFactory = $quoteFactory;
        $this->messageManager = $messageManager;
        $this->hostedCustomer = $customer;
        $this->hostedQuote = $hostedQuote;
        $this->emailNotification = $emailNotification;
        $this->dateTime = $dateTime;
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $quoteId = $params['quote_id'];
        $quote = $this->quoteFactory->create()->loadByIdWithoutStore($quoteId);
        $quoteItems = $quote->getAllVisibleItems();
        $customQuoteParams = $params['custom_quote_params'];

        foreach ($quoteItems as $quoteItem) {
            foreach ($customQuoteParams as $customQuoteId => $customQuoteItemParams) {
                if ($quoteItem->getId() == $customQuoteId) {
                    $quoteItem->setCustomPrice($customQuoteItemParams['price']);
                    $quoteItem->setOriginalCustomPrice($customQuoteItemParams['price']);
                    $quoteItem->setQty($customQuoteItemParams['qty']);
                    $quoteItem->getProduct()->setIsSuperMode(true);
                    $quoteItem->save();
                }
            }
        }
        $quote->collectTotals()->save();

        try {
            $date = $this->dateTime->gmtDate('Y-m-d', date('Y-m-d', strtotime("+" . $this->helper->getDefaultExpiredDays() . " days")));
            $this->sendQuoteToHostedServer($quote);
            $quote->setExpiredAt($date);
            $quote->save();
            $this->emailNotification->initEmailTemplate('approved');
            $this->emailNotification->sendEmail($quote);
            $responseData['status'] = true;
            $this->messageManager->addSuccessMessage(
                'Quote #' . sprintf("%'.07d", $quoteId) . ' was successfully edited and approved.'
            );
        } catch (\Exception $e) {
            $responseData['status'] = false;
            $this->messageManager->addErrorMessage('Sorry, but something went wrong.');
        }

        return $this->_redirect('carttoquote/quotes/index');
    }

    /**
     * @inheritdoc
     */
    public function sendQuoteToHostedServer($quote)
    {
        $this->hostedQuote->editExistingQuote($quote);
    }
}
