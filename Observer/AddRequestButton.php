<?php

namespace IWD\CartToQuote\Observer;

use IWD\CartToQuote\Block\Request\Button;
use Magento\Catalog\Block\ShortcutButtons;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class AddRequestButton
 * @package IWD\CartToQuote\Observer
 */
class AddRequestButton implements ObserverInterface
{
    /**
     * Block class
     */
    const IWD_CTQ_SHORTCUT_BLOCK = Button::class;

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        return;

        // Remove button from catalog pages
        if ($observer->getData('is_catalog_product')) {
            return;
        }

        /** @var ShortcutButtons $shortcutButtons */
        $shortcutButtons = $observer->getEvent()->getContainer();
        $shortcut = $shortcutButtons->getLayout()->createBlock(self::IWD_CTQ_SHORTCUT_BLOCK);
        $shortcutButtons->addShortcut($shortcut);
    }
}
