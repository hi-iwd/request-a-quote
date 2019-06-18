<?php

namespace IWD\CartToQuote\Controller\Cart;

use Magento\Framework;
use Magento\Framework\Controller\ResultFactory;

class Configure extends \Magento\Checkout\Controller\Cart\Configure
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
        Framework\App\Action\Context $context,
        Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Checkout\Model\Cart $cart
    ) {
        $this->messageManager = $messageManager;
        $this->checkoutSession = $checkoutSession;
        $this->request = $request;
        $this->url = $url;
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );
    }

    /**
     * @return mixed
     */
    public function execute()
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
                $this->messageManager->addNotice($message);
                return $this->_goBack();
            }
        }

        // Extract item and product to configure
        $id = (int)$this->getRequest()->getParam('id');
        $productId = (int)$this->getRequest()->getParam('product_id');
        $quoteItem = null;
        if ($id) {
            $quoteItem = $this->cart->getQuote()->getItemById($id);
        }

        try {
            if (!$quoteItem || $productId != $quoteItem->getProduct()->getId()) {
                $this->messageManager->addError(__("We can't find the quote item."));
                return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('checkout/cart');
            }

            $params = new \Magento\Framework\DataObject();
            $params->setCategoryId(false);
            $params->setConfigureMode(true);
            $params->setBuyRequest($quoteItem->getBuyRequest());

            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            $this->_objectManager->get('Magento\Catalog\Helper\Product\View')
                ->prepareAndRender(
                    $resultPage,
                    $quoteItem->getProduct()->getId(),
                    $this,
                    $params
                );
            return $resultPage;
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We cannot configure the product.'));
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            return $this->_goBack();
        }
    }
}
