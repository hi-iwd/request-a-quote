<?php

namespace IWD\CartToQuote\Controller\Wishlist\Index;

use Magento\Framework\Controller\ResultFactory;

class Fromcart extends \Magento\Wishlist\Controller\Index\Fromcart
{
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect->setPath('*/*/');
        }

        $wishlist = $this->wishlistProvider->getWishlist();
        if (!$wishlist) {
            throw new NotFoundException(__('Page not found.'));
        }

        try {
            $itemId = (int)$this->getRequest()->getParam('item');
            $item = $this->cart->getQuote()->getItemById($itemId);
            if (!$item) {
                throw new LocalizedException(
                    __('The requested cart item doesn\'t exist.')
                );
            }

            $productId = $item->getProductId();
            $buyRequest = $item->getBuyRequest();
            $wishlist->addNewItem($productId, $buyRequest);

            $this->cart->getQuote()->removeItem($itemId);
            $this->cart->save();

            $this->wishlistHelper->calculate();
            $wishlist->save();

            $this->messageManager->addSuccessMessage(__(
                "%1 has been moved to your wish list.",
                $this->escaper->escapeHtml($item->getProduct()->getName())
            ));
        } catch (\IWD\CartToQuote\Exceptions\NonEditableQuoteWarning $exception) {
            /* Do Nothing */
        }  catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We can\'t move the item to the wish list.'));
        }
        return $resultRedirect->setUrl($this->cartHelper->getCartUrl());
    }
}