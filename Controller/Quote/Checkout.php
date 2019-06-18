<?php

namespace IWD\CartToQuote\Controller\Quote;

use IWD\CartToQuote\Helper\Data;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Checkout
 * @package IWD\CartToQuote\Controller\Quote
 */
class Checkout extends Action
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var \Magento\Quote\Api\Data\CartInterface|\Magento\Quote\Model\Quote
     */
    private $quote = false;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * Checkout constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Data $helper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        UrlInterface $urlBuilder
    )
    {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $url = $this->urlBuilder->getUrl('checkout/cart', ['_secure' => true]);

            if ($this->checkoutSession->getQuote() && $this->checkoutSession->getQuote()->getId()) {
                $this->disableCurrentQuote();

                /* Just disable current quote and return to the Shopping Cart */
                if($this->getRequest()->getParam('disable_quote')){
                    return $this->resultRedirectFactory->create()->setUrl($url);
                }
            }

            $this->loadQuote();

            if ($this->isQuoteDoesNotExists()) {
                $this->messageManager->addErrorMessage('This quote does not exist anymore!');
                return $this->resultRedirectFactory->create()->setUrl('/');
            }

            $this->enableQuote();
            $this->messageManager->addSuccessMessage('Your quote has been loaded. Please, proceed to checkout.');

            return $this->resultRedirectFactory->create()->setUrl($url);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->resultRedirectFactory->create()->setUrl('/');
        }
    }

    /**
     * @return \Magento\Quote\Api\Data\CartInterface|\Magento\Quote\Model\Quote|null
     */
    private function loadQuote()
    {
        if ($this->quote === false) {
            $id = $this->getRequest()->getParam('id', 0);
            $this->quote = $this->quoteRepository->get($id);
        }

        return $this->quote;
    }

    /**
     * @return bool
     */
    private function isQuoteDoesNotExists()
    {
        $quote = $this->loadQuote();
        return !$quote || !$quote->getId();
    }

    private function disableCurrentQuote()
    {
        $quote = $this->checkoutSession->getQuote();
        $this->checkoutSession->clearHelperData();
        $quote->setIsActive(false);
        $quote->setIsPersistent(true);

        $this->quoteRepository->save($quote);
    }

    private function enableQuote()
    {
        $quote = $this->loadQuote();
        $quote->setIsActive(true);
        $quote->setIsPersistent(false);
        $this->quoteRepository->save($quote);
        $this->checkoutSession->setQuoteId($quote->getId());
        $this->checkoutSession->getQuote();
    }
}
