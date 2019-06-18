<?php

namespace IWD\CartToQuote\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\DataObject;

use \Magento\Checkout\Model\Session as CheckoutSession;

/**
 * Class BlockCartTruncate
 * @package IWD\CartToQuote\Observer
 */
class BlockB2BQuoteFromEditing implements ObserverInterface
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
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\UrlInterface $url,
        ObjectManagerInterface $objectManager,
        CheckoutSession $checkoutSession
    ) {
        $this->messageManager = $messageManager;
        $this->checkoutSession = $checkoutSession;
        $this->request = $request;
        $this->url = $url;
        $this->objectManager = $objectManager;
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
            $currentResponse = $observer->getControllerAction()->getResponse();

            /* If quote is non-editable - throw exception and block it from any modification */
            if (!$quote->getEditableItems()) {
                $response = new DataObject();

                $checkoutLink = $this->url->getUrl('checkout', ['_secure' => true]);
                $disableQuote = $this->url->getUrl('iwdc2q/quote/checkout', ['disable_quote' => 'true']);

                $message = __('<b>Your current Quote is custom and non-editable.</b><br/> 
                        Please pass <a href="%1"><b>Checkout</b></a> first before adding/editing/deleting products from the Shopping Cart.<br/>
                        <b>OR</b>
                        Deactivate current Quote clicking <a href="%2"><b>This Link</b></a>, if you want to pass checkout with the custom Quote later.', $checkoutLink, $disableQuote);

                $response->setData('error', true);
                $response->setData('c2q_blocked_quote', true);
                $response->setData('message', $message);

                $controller_action = $observer->getData('controller_action');
                $controller_action->getActionFlag()->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                $this->pushJson($response, $currentResponse);
                return;
            }
        }
    }

    public function pushJson($response, $currentResponse) {
        $jsonHelper = $this->objectManager->get('Magento\Framework\Json\Helper\Data');
        $currentResponse->setBody($jsonHelper->jsonEncode($response));
        return;
    }

}
