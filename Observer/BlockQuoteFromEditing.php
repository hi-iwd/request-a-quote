<?php

namespace IWD\CartToQuote\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

use \Magento\Checkout\Model\Session as CheckoutSession;

/**
 * Class BlockQuoteFromEditing
 * @package IWD\CartToQuote\Observer
 */
class BlockQuoteFromEditing implements ObserverInterface
{

    /** @var CheckoutSession */
    protected $checkoutSession;

    /**
     * @var MessageManager
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\UrlInterface $url,
        CheckoutSession $checkoutSession
    ) {
        $this->messageManager = $messageManager;
        $this->checkoutSession = $checkoutSession;
        $this->request = $request;
        $this->url = $url;
    }

    /**
     * @param Observer $observer
     * @return mixed
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Quote\Model\Quote  */
        if ($this->checkoutSession->getQuote() && $this->checkoutSession->getQuote()->getId()) {
            $quote = $this->checkoutSession->getQuote();
            $checkoutLink = $this->url->getUrl('checkout', ['_secure' => true]);
            $disableQuote = $this->url->getUrl('iwdc2q/quote/checkout', ['disable_quote' => 'true']);

            $message = __('<b>Your current Quote is custom and non-editable.</b><br/> 
                        Please pass <a href="%1"><b>Checkout</b></a> first before adding/editing/deleting products from the Shopping Cart.<br/>
                        <b>OR</b>
                        Deactivate current Quote clicking <a href="%2"><b>This Link</b></a>, if you want to pass checkout with the custom Quote later.', $checkoutLink, $disableQuote);

            /* If quote is non-editable - throw exception and block it from any modification */
            if (!$quote->getEditableItems()) {
                if ($this->request->getControllerName() != 'sidebar') {
                    $this->messageManager->addNotice($message);
                }
                throw new \IWD\CartToQuote\Exceptions\NonEditableQuoteWarning($message);
            }
        }
    }

}
