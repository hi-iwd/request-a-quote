<?php

namespace IWD\CartToQuote\Controller\Cart;

class Delete extends \Magento\Checkout\Controller\Cart\Delete
{
    /**
     * @return mixed
     */
    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $id = (int)$this->getRequest()->getParam('id');
        if ($id) {
            try {
                $this->cart->removeItem($id)->save();
            } catch (\IWD\CartToQuote\Exceptions\NonEditableQuoteWarning $exception) {
                /* Do Nothing */
            }  catch (\Exception $e) {
                $this->messageManager->addError(__('We can\'t remove the item.'));
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            }
        }
        $defaultUrl = $this->_objectManager->create('Magento\Framework\UrlInterface')->getUrl('*/*');
        return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRedirectUrl($defaultUrl));
    }
}
