<?php

namespace IWD\CartToQuote\Model\Api;

use IWD\CartToQuote\Helper\Data;
use IWD\CartToQuote\Model\Logger\Logger;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Quote
 * @package IWD\CartToQuote\Model\Api
 */
class Quote extends ApiRequest
{
    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Catalog\Helper\Product\Configuration
     */
    private $productConfig = null;

    /**
     * @var \Magento\Downloadable\Helper\Catalog\Product\Configuration
     */
    private $downloadableProductConfiguration;

    /**
     * @var \Magento\Bundle\Helper\Catalog\Product\Configuration
     */
    private $bundleProductConfiguration;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;
    /**
     * @var Data
     */
    private $data;

    /**
     * @var array
     */
    private $options = [];

    /**
     * Quote constructor.
     * @param Curl $curl
     * @param StoreManagerInterface $storeManager
     * @param Token $token
     * @param ScopeConfigInterface $scopeConfig
     * @param CustomerSession $customerSession
     * @param Logger $logger
     * @param \Magento\Catalog\Helper\Product\Configuration $productConfig
     * @param \Magento\Downloadable\Helper\Catalog\Product\Configuration $downloadableProductConfiguration
     * @param \Magento\Bundle\Helper\Catalog\Product\Configuration $bundleProductConfiguration
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param Data $data
     */
    public function __construct(
        Curl $curl,
        StoreManagerInterface $storeManager,
        Token $token,
        ScopeConfigInterface $scopeConfig,
        CustomerSession $customerSession,
        Logger $logger,
        \Magento\Catalog\Helper\Product\Configuration $productConfig,
        \Magento\Downloadable\Helper\Catalog\Product\Configuration $downloadableProductConfiguration,
        \Magento\Bundle\Helper\Catalog\Product\Configuration $bundleProductConfiguration,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        Data $data
    )
    {
        parent::__construct($curl, $storeManager, $token, $scopeConfig, $logger);
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->data = $data;
        $this->productConfig = $productConfig;
        $this->downloadableProductConfiguration = $downloadableProductConfiguration;
        $this->bundleProductConfiguration = $bundleProductConfiguration;
        $this->eventManager = $eventManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getStorageForTemporaryToken()
    {
        return $this->customerSession;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param $email
     * @param $additionalData
     */
    public function addNewQuote($quote, $email, $additionalData)
    {
        $requestForm = $this->scopeConfig->getValue('iwd_cart_to_quote/fields/fields');
        $this->options = [
            'quote' => [
                'total' => $quote->getGrandTotal(),
                'quote_id' => $quote->getId(),
                'expired' => $this->data->getDefaultExpiredDays(),
                'status' => $this->getDefaultStatus(),
                'currency' => $quote->getQuoteCurrencyCode(),
                'request_data' => $additionalData,
                'store_id' => $quote->getStoreId(),
                'request_form' => $requestForm
            ],
            'quote_items' => $this->getQuoteItems($quote)
        ];

        $this->eventManager->dispatch('iwd_c2q_add_new_quote_before', ['quote' => $quote, 'object' => $this]);

        $this->request(
            'user/quote/add',
            [
                'email' => $email,
                'domain' => $this->getDomain(),
                'user_key' => $this->getTokenModel()->getCustomerKey(),
                'key' => $this->getKey(),
            ],
            $this->getQuoteOptions()
        );
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param string $status
     */
    public function editExistingQuote($quote, $status = 'approved')
    {
        if ($status == 'approved') {
            $expired = $this->data->getDefaultExpiredDays();
        } else {
            $expired = false;
        }
        $this->addQuoteOptions(
            'quote',
            [
                'total' => $quote->getGrandTotal(),
                'subtotal' => $quote->getSubtotal(),
                'discount' => ($quote->getSubtotal() - $quote->getSubtotalWithDiscount()),
                'shipping' => $quote->getShippingAddress()->getShippingAmount(),
                'tax' => $quote->getShippingAddress()->getTaxAmount(),
                'quote_id' => $quote->getId(),
                'expired' => $expired,
                'status' => $status,
                'currency' => $quote->getQuoteCurrencyCode(),
                'store_id' => $quote->getStoreId(),
            ]
        );
        $this->addQuoteOptions('quote_items', $this->getQuoteItems($quote));

        $this->request(
            'user/quote/edit',
            [
                'domain' => $this->getDomain(),
                'user_key' => $this->getTokenModel()->getCustomerKey(),
                'key' => $this->getKey(),
            ],
            $this->getQuoteOptions()
        );
    }

    /**
     * @return array
     */
    public function getQuoteOptions()
    {
        return $this->options;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function addQuoteOptions($key, $value)
    {
        $this->options[$key] = $value;
        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return mixed
     */
    private function getQuoteItems($quote)
    {
        $items = $quote->getAllItems();
        $data = [];

        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($items as $item) {
            $data[$item->getId()] = [
                'product_id' => $item->getProduct()->getId(),
                'name' => $item->getProduct()->getName(),
                'sku' => $item->getProduct()->getSku(),
                'qty' => $item->getQty(),
                'price' => $item->getPrice(),
                'product_type' => $item->getProductType(),
                'quote_item_id' => $item->getId(),
                'parent_item_id' => $item->getParentItemId()
            ];

            if ($optionsHtml = $this->prepareProductOptions($item)) {
                $data[$item->getId()]['options'] = $optionsHtml;
            }
        }

        return $data;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return array
     */
    private function prepareProductOptions($item)
    {
        $productOptions = [];
        if ($options = $this->getProductOptions($item)) {
            foreach ($options as $option) {
                $formatedValue = $this->getFormatedOptionValue($option);
                $value = (isset($formatedValue['full_view']))
                    ? $formatedValue['full_view']
                    : $formatedValue['value'];

                $productOptions[] = [
                    'value' => $value,
                    'label' => $option['label']
                ];
            }
        }

        return $productOptions;
    }

    /**
     * @param $optionValue
     * @return array
     */
    private function getFormatedOptionValue($optionValue)
    {
        $params = [
            'max_length' => 55,
            'cut_replacer' => ' <a href="#" class="dots tooltip toggle" onclick="return false">...</a>'
        ];
        return $this->productConfig->getFormattedOptionValue($optionValue, $params);
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return array
     */
    private function getProductOptions($item)
    {
        if ($item->getProductType() == 'configurable') {
            return $this->productConfig->getOptions($item);
        } elseif ($item->getProductType() == 'downloadable') {
            return $this->downloadableProductConfiguration->getOptions($item);
        } elseif ($item->getProductType() == 'bundle') {
            return $this->bundleProductConfiguration->getOptions($item);
        }

        return $this->productConfig->getCustomOptions($item);
    }

    /**
     * @return string
     */
    private function getDefaultStatus()
    {
        return 'requested'; //TODO:!!!
    }
}
