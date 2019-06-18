<?php

namespace IWD\CartToQuote\Block\Adminhtml\Quotes;

use IWD\CartToQuote\Model\Api\Error;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\View\Element\Template;
use IWD\CartToQuote\Model\Api\Admin;
use Psr\Log\LoggerInterface;

/**
 * Class Grid
 * @package IWD\CartToQuote\Block\Adminhtml\Quotes
 */
class Grid extends Template
{
    /**
     * @var Admin
     */
    private $adminCustomer;

    /**
     * @var bool
     */
    private $error;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var MessageManagerInterface
     */
    private $messageManager;

    /**
     * Quotes constructor.
     * @param Template\Context $context
     * @param Admin $adminCustomer
     * @param LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Admin $adminCustomer,
        LoggerInterface $logger,
        MessageManagerInterface $messageManager,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->messageManager = $messageManager;
        $this->adminCustomer = $adminCustomer;
        $this->logger = $logger;

        $this->init();
    }

    /**
     * initialisation before display widget
     */
    private function init()
    {
        try {
            $this->adminCustomer->authOrRegisterAdmin();
            if (!$this->adminCustomer->isTokenExists()) {
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
        return $this->adminCustomer->isConnectionError();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        if ($this->isErrorDuringRequestToApi()) {
            if ($this->adminCustomer->getResponseCode() == Error::STORE_DOES_NOT_EXISTS) {
                return $this->adminCustomer->getUrl('admin/store/error');
            } elseif ($this->adminCustomer->getResponseCode() == Error::STORE_API_LICENSE_ERROR) {
                return $this->adminCustomer->getUrl('admin/license/error');
            } else {
                return $this->adminCustomer->getUrl('admin/error');
            }
        } else {
            return $this->adminCustomer->getUrl(
                'admin',
                [
                    'key' => $this->adminCustomer->getKey(),
                    'token' => $this->adminCustomer->getToken(),
                ]
            );
        }
    }
}
