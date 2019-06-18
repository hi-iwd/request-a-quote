<?php

namespace IWD\CartToQuote\Controller\Cart;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Add
 * @package IWD\CartToQuote\Controller\Cart
 */
class Add extends \Magento\Checkout\Controller\Cart\Add
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepositoryInterface;
    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    private $cartManagementInterface;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;
    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    private $quoteFactory;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    private $checkoutSession;

    /**
     * Add constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param CustomerCart $cart
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagementInterface
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Checkout\Model\CartFactory $cartFactory
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        CustomerCart $cart,
        ProductRepositoryInterface $productRepository,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Checkout\Model\CartFactory $cartFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\RequestInterface $request
    )
    {
        parent::__construct($context, $scopeConfig, $checkoutSession, $storeManager, $formKeyValidator, $cart, $productRepository);
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->cartManagementInterface = $cartManagementInterface;
        $this->productFactory = $productFactory;
        $this->quoteFactory = $quoteFactory;
        $this->cartFactory = $cartFactory;
        $this->url = $url;
        $this->request = $request;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

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
                return $this->goBack();
            }
        }

        $params = $this->getRequest()->getParams();
        $isIwdRequestOnlyCart = $this->getRequest()->getParam('iwd_request_only');
        try {
            if (isset($params['qty'])) {
                $filter = new \Zend_Filter_LocalizedToNormalized(
                    ['locale' => $this->_objectManager->get('Magento\Framework\Locale\ResolverInterface')->getLocale()]
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');

            /**
             * Check product availability
             */
            if (!$product) {
                return $this->goBack();
            }

            if ($isIwdRequestOnlyCart) {
                $store = $this->_storeManager->getStore();
                try {
                    //init the quote
                    $cart = $this->cartFactory->create();
                    $quote = $this->quoteFactory->create();
                    $quote->setStore($store);
                    $quote->setIsActive(false);
                    $cart->setQuote($quote);
                    $cart->setStore($store);
                    $cart->setCurrency();
                    //add item in quote
                    $cart->addProduct($product, $params);
                    if (!empty($related)) {
                        $cart->addProductsByIds(explode(',', $related));
                    }

                    $cart->save();
                    $cart->setInventoryProcessed(false);

                    $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                    $result = [
                        'iwd_quote_id' => $cart->getQuote()->getId()
                    ];
                    $resultJson->setData($result);
                    return $resultJson;
                } catch (\Exception $exception) {
                    var_dump($exception->getMessage());
                    die();
                }
            }
            $this->cart->addProduct($product, $params);
            if (!empty($related)) {
                $this->cart->addProductsByIds(explode(',', $related));
            }

            $this->cart->save();

            /**
             * @todo remove wishlist observer \Magento\Wishlist\Observer\AddToCart
             */
            $this->_eventManager->dispatch(
                'checkout_cart_add_product_complete',
                ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
            );

            if (!$this->_checkoutSession->getNoCartRedirect(true)) {
                if (!$this->cart->getQuote()->getHasError()) {
                    $message = __(
                        'You added %1 to your shopping cart.',
                        $product->getName()
                    );
                    $this->messageManager->addSuccessMessage($message);
                }
                return $this->goBack(null, $product);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($this->_checkoutSession->getUseNotice(true)) {
                $this->messageManager->addNotice(
                    $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($e->getMessage())
                );
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->messageManager->addError(
                        $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($message)
                    );
                }
            }

            $url = $this->_checkoutSession->getRedirectUrl(true);

            if (!$url) {
                $cartUrl = $this->_objectManager->get('Magento\Checkout\Helper\Cart')->getCartUrl();
                $url = $this->_redirect->getRedirectUrl($cartUrl);
            }

            return $this->goBack($url);

        } catch (\IWD\CartToQuote\Exceptions\NonEditableQuoteWarning $exception) {
            /* Do Nothing */
            return $this->goBack();
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We can\'t add this item to your shopping cart right now.'));
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            return $this->goBack();
        }
    }
}
