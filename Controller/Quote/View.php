<?php

namespace IWD\CartToQuote\Controller\Quote;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\View\Result\PageFactory;
use IWD\CartToQuote\Helper\Data;

/**
 * Class View
 * @package IWD\CartToQuote\Controller\Quote
 */
class View extends Action
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
     * Quotes constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $helper
    ) {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
    }

    /**
     * Customer order history
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /**
         * @var \Magento\Backend\Model\View\Result\Page $resultPage
         */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Shared Quote'));

        /**
         *@var \IWD\CartToQuote\Block\Quote\Share\View $quoteShareView
         */
        $quoteShareView = $resultPage->getLayout()->getBlock('quote.share.view');
        if ($quoteShareView) {
            $id = $this->getRequest()->getParam('id', '0');
            $quoteShareView->setQuoteHash($id);
        }

        return $resultPage;
    }
}
