<?php

namespace IWD\CartToQuote\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\View\Result\PageFactory;
use IWD\CartToQuote\Helper\Data;

/**
 * Class Quotes
 * @package IWD\CartToQuote\Controller\Customer
 */
class Quotes extends Action
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
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Customer order history
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('My Quotes'));

        $block = $resultPage->getLayout()->getBlock('customer.account.link.back');
        $gridBlock = $resultPage->getLayout()->getBlock('iwdc2q.customer.quotes');

        if ($block && $gridBlock && $this->helper->getEnable()) {
            $block->setRefererUrl($this->_redirect->getRefererUrl());

            $iFrameUrl = $this->getRequest()->getParam('p', '');
            $gridBlock->setIFrameUrl($iFrameUrl);
        } else {
            return $this->_redirect('customer/account');
        }

        return $resultPage;
    }
}
