<?php

namespace IWD\CartToQuote\Controller\Adminhtml\Quotes;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Model\QuoteFactory;

/**
 * Class Index
 * @package IWD\CartToQuote\Controller\Adminhtml\Quotes
 */
class Edit extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'IWD_CartToQuote::carttoquote_quotes_edit';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param QuoteFactory $quoteFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        QuoteFactory $quoteFactory,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        $params = $this->getRequest()->getParams();
        $quoteId = $params['id'];

        $quote = $this->quoteFactory->create()->loadByIdWithoutStore($quoteId);

        $this->registry->register('requested_quote', $quote);

        $resultPage->getConfig()->getTitle()->prepend(__('Edit Quote #'.sprintf("%'.07d", $quoteId)));

        return $resultPage;
    }
}
