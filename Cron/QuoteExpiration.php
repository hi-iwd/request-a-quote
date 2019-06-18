<?php
/**
 * Created by PhpStorm.
 * User: vlad
 * Date: 12.07.2018
 * Time: 11:45
 */

namespace IWD\CartToQuote\Cron;

use Magento\Reports\Model\ResourceModel\Quote\CollectionFactory as QuoteFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use IWD\CartToQuote\Helper\EmailNotification;
use IWD\CartToQuote\Helper\Data;
use IWD\CartToQuote\Model\Api\Quote;

/**
 * Class QuoteExpiration
 * @package IWD\CartToQuote\Cron
 */
class QuoteExpiration
{
    /**
     * @var QuoteFactory
     */
    private $quoteFactory;
    /**
     * @var DateTime
     */
    private $date;
    private $emailNotification;
    private $hostedQuote;

    /**
     * QuoteExpiration constructor.
     * @param QuoteFactory $quoteFactory
     * @param DateTime $date
     * @param EmailNotification $emailNotification
     * @param Quote $hostedQuote
     */
    public function __construct(
        QuoteFactory $quoteFactory,
        DateTime $date,
        EmailNotification $emailNotification,
        Quote $hostedQuote
    )
    {
        $this->quoteFactory = $quoteFactory;
        $this->date = $date;
        $this->emailNotification = $emailNotification;
        $this->hostedQuote = $hostedQuote;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $date = $this->date->gmtDate();
        $quoteCollection = $this->quoteFactory->create()
            ->addFieldToFilter('expired_at', ['to' => $date])
            ->addFieldToFilter('is_quote_expired', ['eq' => '0']);
        foreach ($quoteCollection as $quote) {
            $this->emailNotification->initEmailTemplate(Data::QUOTE_EXPIRED_STATUS);
            $this->emailNotification->sendEmail($quote);
            $this->hostedQuote->editExistingQuote($quote, Data::QUOTE_EXPIRED_STATUS);
            $quote->setIsQuoteExpired(true)->save();
        }
        return $this;
    }
}