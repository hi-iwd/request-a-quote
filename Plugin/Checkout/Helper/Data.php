<?php

namespace IWD\CartToQuote\Plugin\Checkout\Helper;

/**
 * Class Data
 * @package IWD\CartToQuote\Plugin\Checkout\Helper
 */
class Data
{
    /** @var CheckoutSession */
    protected $checkoutSession;

    /**
     * @var \IWD\CartToQuote\Helper\Data
     */
    private $ctqHelper;

    /**
     * Data constructor.
     * @param \IWD\CartToQuote\Helper\Data $ctqHelper
     * @param array $data
     */
    public function __construct(
        \IWD\CartToQuote\Helper\Data $ctqHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    ) {
        $this->ctqHelper = $ctqHelper;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param \Magento\Checkout\Helper\Data $subject
     * @param $result
     * @return bool
     */
    public function afterCanOnepageCheckout(\Magento\Checkout\Helper\Data $subject, $result)
    {
        $quote = $this->checkoutSession->getQuote();

        if ($result
            && $this->ctqHelper->getEnable()
            && $this->ctqHelper->getRequestType() === \IWD\CartToQuote\Helper\Data::REQUEST_TYPE_QUOTE_ONLY
            && $quote->getEditableItems()
        ) {
            $result = false;
        }

        return $result;
    }
}
