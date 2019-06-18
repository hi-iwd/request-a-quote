<?php
/**
 * Created by PhpStorm.
 * User: vlad
 * Date: 13.07.2018
 * Time: 12:16
 */

namespace IWD\CartToQuote\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use IWD\CartToQuote\Helper\EmailNotification;
use IWD\CartToQuote\Model\Api\Quote;
use Magento\Framework\View\DesignInterface;
use IWD\CartToQuote\Helper\Data;

class CheckoutSubmitAllAfterObserver implements ObserverInterface
{
    private $emailNotification;
    private $hostedQuote;
    private $_viewDesign;

    public function __construct(
        EmailNotification $emailNotification,
        Quote $hostedQuote,
        DesignInterface $viewDesign
    )
    {
        $this->_viewDesign = $viewDesign;
        $this->emailNotification = $emailNotification;
        $this->hostedQuote = $hostedQuote;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $order = $observer->getOrder();
        if ($order) {
            $quote = $observer->getEvent()->getQuote();
            if (!empty($quote)) {
                $this->hostedQuote->editExistingQuote($quote, Data::QUOTE_CLOSED_STATUS);
                $this->emailNotification->initEmailTemplate(Data::QUOTE_CLOSED_STATUS);
                $this->emailNotification->sendEmail($quote);
            }
        }
    }
}
