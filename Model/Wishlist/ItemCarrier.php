<?php

namespace IWD\CartToQuote\Model\Wishlist;

class ItemCarrier extends \Magento\Wishlist\Model\ItemCarrier
{
    public function moveAllToCart(\Magento\Wishlist\Model\Wishlist $wishlist, $qtys)
    {
        $isOwner = $wishlist->isOwner($this->customerSession->getCustomerId());

        $messages = [];
        $addedProducts = [];
        $notSalable = [];

        $cart = $this->cart;
        $collection = $wishlist->getItemCollection()->setVisibilityFilter();

        foreach ($collection as $item) {
            /** @var $item \Magento\Wishlist\Model\Item */
            try {
                $disableAddToCart = $item->getProduct()->getDisableAddToCart();
                $item->unsProduct();

                // Set qty
                if (isset($qtys[$item->getId()])) {
                    $qty = $this->quantityProcessor->process($qtys[$item->getId()]);
                    if ($qty) {
                        $item->setQty($qty);
                    }
                }
                $item->getProduct()->setDisableAddToCart($disableAddToCart);
                // Add to cart
                if ($item->addToCart($cart, $isOwner)) {
                    $addedProducts[] = $item->getProduct();
                }
            } catch (LocalizedException $e) {
                if ($e instanceof ProductException) {
                    $notSalable[] = $item;
                } else {
                    $messages[] = __('%1 for "%2".', trim($e->getMessage(), '.'), $item->getProduct()->getName());
                }

                $cartItem = $cart->getQuote()->getItemByProduct($item->getProduct());
                if ($cartItem) {
                    $cart->getQuote()->deleteItem($cartItem);
                }
            } catch (\IWD\CartToQuote\Exceptions\NonEditableQuoteWarning $exception) {
                /* Do Nothing */
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $messages[] = __('We can\'t add this item to your shopping cart right now.');
            }
        }

        if ($isOwner) {
            $indexUrl = $this->helper->getListUrl($wishlist->getId());
        } else {
            $indexUrl = $this->urlBuilder->getUrl('wishlist/shared', ['code' => $wishlist->getSharingCode()]);
        }
        if ($this->cartHelper->getShouldRedirectToCart()) {
            $redirectUrl = $this->cartHelper->getCartUrl();
        } elseif ($this->redirector->getRefererUrl()) {
            $redirectUrl = $this->redirector->getRefererUrl();
        } else {
            $redirectUrl = $indexUrl;
        }

        if ($notSalable) {
            $products = [];
            foreach ($notSalable as $item) {
                $products[] = '"' . $item->getProduct()->getName() . '"';
            }
            $messages[] = __(
                'We couldn\'t add the following product(s) to the shopping cart: %1.',
                join(', ', $products)
            );
        }

        if ($messages) {
            foreach ($messages as $message) {
                $this->messageManager->addError($message);
            }
            $redirectUrl = $indexUrl;
        }

        if ($addedProducts) {
            // save wishlist model for setting date of last update
            try {
                $wishlist->save();
            } catch (\Exception $e) {
                $this->messageManager->addError(__('We can\'t update the Wish List right now.'));
                $redirectUrl = $indexUrl;
            }

            $products = [];
            foreach ($addedProducts as $product) {
                /** @var $product \Magento\Catalog\Model\Product */
                $products[] = '"' . $product->getName() . '"';
            }

            $this->messageManager->addSuccess(
                __('%1 product(s) have been added to shopping cart: %2.', count($addedProducts), join(', ', $products))
            );

            // save cart and collect totals
            $cart->save()->getQuote()->collectTotals();
        }
        $this->helper->calculate();
        return $redirectUrl;
    }
}