<?php

namespace IWD\CartToQuote\Plugin\Sales\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use IWD\CartToQuote\Model\Logger\Logger;

/**
 * Created by PhpStorm.
 * User: vlad
 * Email: vladokrushko@gmail.com
 * Date: 12.07.2018
 * Time: 18:36
 */
class Order
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Order constructor.
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param Logger $logger
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        Logger $logger
    )
    {
        $this->logger = $logger;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param \Magento\Sales\Model\Order $subject
     * @param $proceed
     * @return bool
     */
    public function aroundCanReorderIgnoreSalable(\Magento\Sales\Model\Order $subject, $proceed)
    {
        try {
            $quote = $this->quoteRepository->get($subject->getQuoteId());
            if ($quote->getIsIwdDisabledReorder()) {
                $returnValue = false;
            } else {
                $returnValue = $proceed();
            }
            return $returnValue;

        } catch (NoSuchEntityException $e) {
            return $proceed();
        }
    }

    /**
     * @param \Magento\Sales\Model\Order $subject
     * @param $proceed
     * @return bool
     */
    public function aroundCanReorder(\Magento\Sales\Model\Order $subject, $proceed)
    {
        try {
            $quote = $this->quoteRepository->get($subject->getQuoteId());
            if ($quote->getIsIwdDisabledReorder()) {
                $returnValue = false;
            } else {
                $returnValue = $proceed();
            }
            return $returnValue;

        } catch (NoSuchEntityException $e) {
            $this->logger->error($e->getMessage());
            return $proceed();
        }
    }
}