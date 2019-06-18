<?php

namespace IWD\CartToQuote\Controller\Adminhtml\Quotes;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use IWD\CartToQuote\Helper\Data;

/**
 * Class Index
 * @package IWD\CartToQuote\Controller\Adminhtml\Quotes
 */
class Index extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'IWD_CartToQuote::carttoquote_quotes_index';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var Data
     */
    private $helper;

    /**
     * Index constructor.
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
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('IWD_CartToQuote::carttoquote_quotes_index');
        $resultPage->getConfig()->getTitle()->prepend(__('Quotes List'));

        if (!$this->helper->getEnable()) {
            return $this->_redirect('adminhtml/system_config/edit', ['section' => 'iwd_cart_to_quote']);
        }

        return $resultPage;
    }
}
