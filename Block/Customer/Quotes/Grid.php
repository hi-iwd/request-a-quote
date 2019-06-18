<?php

namespace IWD\CartToQuote\Block\Customer\Quotes;

use IWD\CartToQuote\Model\Api\Error;
use Magento\Framework\View\Element\Template;
use IWD\CartToQuote\Model\Api\Customer;
use Psr\Log\LoggerInterface;

/**
 * Class Grid
 * @package IWD\CartToQuote\Block\Customer\Quotes
 */
class Grid extends Template
{
    /**
     * @var Customer
     */
    private $customer;

    /**
     * @var bool
     */
    private $error;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $iFrameUrl = '';

    /**
     * Quotes constructor.
     * @param Template\Context $context
     * @param Customer $customer
     * @param LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Customer $customer,
        LoggerInterface $logger,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->customer = $customer;
        $this->logger = $logger;

        $this->init();
    }

    /**
     * initialisation before display widget
     */
    private function init()
    {
        try {
            $this->customer->authOrRegisterCustomer();
            if (!$this->customer->isTokenExists()) {
                $this->error = true;
            }
        } catch (\Exception $e) {
            $this->error = true;
            $this->logger->error('IWD C2Q issue: ' . $e->getMessage());
        }
    }

    /**
     * @return bool
     */
    public function isErrorDuringRequestToApi()
    {
        return $this->error;
    }

    /**
     * @return bool
     */
    public function isConnectionError()
    {
        return $this->customer->isConnectionError();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        if ($this->isErrorDuringRequestToApi()) {
            if ($this->customer->getResponseCode() == Error::STORE_DOES_NOT_EXISTS) {
                $url = $this->customer->getUrl('user/store/error');
            } elseif ($this->customer->getResponseCode() == Error::STORE_API_LICENSE_ERROR) {
                $url = $this->customer->getUrl('user/license/error');
            } else {
                $url = $this->customer->getUrl('user/error');
            }
        } else {
            $url = $this->customer->getUrl(
                'user',
                [
                    'key' => $this->customer->getKey(),
                    'token' => $this->customer->getToken(),
                ]
            );
        }

        $iFrameUrl = $this->getIFrameUrl();
        return $url . (empty($iFrameUrl) ? '' : '?p=' . $iFrameUrl);
    }

    /**
     * @param $url
     */
    public function setIFrameUrl($url)
    {
        $this->iFrameUrl = $url;
    }

    /**
     * @return string
     */
    public function getIFrameUrl()
    {
        return $this->iFrameUrl;
    }
}
