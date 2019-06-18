<?php
/**
 * Created by PhpStorm.
 * User: vlad
 * Date: 03.07.2018
 * Time: 18:01
 */

namespace IWD\CartToQuote\Controller\Adminhtml\Quotes;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use IWD\CartToQuote\Helper\EmailNotification;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Class ChangeQuoteStatus
 * @package IWD\CartToQuote\Controller\Adminhtml\Quotes
 */
class ChangeQuoteStatus extends \Magento\Backend\App\Action
{

    protected $resultJsonFactory;
    protected $notificationHelper;
    protected $quoteRepository;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        EmailNotification $notificationHelper,
        CartRepositoryInterface $quoteRepository
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->notificationHelper = $notificationHelper;
        $this->quoteRepository = $quoteRepository;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        if ($this->getRequest()->isAjax()) {
            $quoteId = $this->getRequest()->getParam('quote_id');
            $newQuoteStatus = $this->getRequest()->getParam('new_status');
            $notificationHelper = $this->notificationHelper;

            if ($quoteId && $newQuoteStatus) {
                $notificationHelper->initEmailTemplate($newQuoteStatus);
                if ($notificationHelper->getEmailTemplate()) {
                    $quote = $this->quoteRepository->get($quoteId);
                    $notificationHelper->sendEmail($quote);
                }
            }
        }
    }
}
