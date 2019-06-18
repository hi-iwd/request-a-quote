<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\CartToQuote\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use IWD\CartToQuote\Block\Request\Button;
use \Magento\Checkout\Model\Session as CheckoutSession;

class CartToQuoteShortcutsObserver implements \Magento\Framework\Event\ObserverInterface
{

    /** @var CheckoutSession */
    protected $checkoutSession;

    /**
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->_request = $request;
    }

    /**
     * Add Cart to quote shortcut buttons
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        if(!$observer->getEvent()->getIsCatalogProduct()) {
            return;
        }

        if ($this->checkoutSession->getQuote() && $this->checkoutSession->getQuote()->getId()) {
            $quote = $this->checkoutSession->getQuote();

            /* If quote is non-editable - hide "Request Quote" button */
            if ($quote->getEditableItems() && $this->_request->getFullActionName() == 'catalog_product_view') {
                $this->addRequestButton($observer);
            }
        } else {
            if ($this->_request->getFullActionName() == 'catalog_product_view') {
                $this->addRequestButton($observer);
            }
        }
    }

    /**
     * @param $observer
     */
    public function addRequestButton($observer)
    {
        /** @var \Magento\Catalog\Block\ShortcutButtons $shortcutButtons */
        $shortcutButtons = $observer->getEvent()->getContainer();
        $shortcut = $shortcutButtons->getLayout()->createBlock(Button::class);
        $shortcut->setIsInCatalogProduct(
            $observer->getEvent()->getIsCatalogProduct()
        )->setShowOrPosition(
            $observer->getEvent()->getOrPosition()
        )->setTemplate('IWD_CartToQuote::catalog/view/button.phtml');
        $shortcutButtons->addShortcut($shortcut);
    }
}