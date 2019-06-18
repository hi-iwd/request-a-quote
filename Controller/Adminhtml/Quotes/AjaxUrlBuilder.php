<?php

namespace IWD\CartToQuote\Controller\Adminhtml\Quotes;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class Index
 * @package IWD\CartToQuote\Controller\Adminhtml\Quotes
 */
class AjaxUrlBuilder extends \Magento\Backend\App\Action
{

    /**
     * Index constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $params = $this->getRequest()->getParams();

        $controllerPath = $params['controller_path'];
        $urlParamsString = $params['url_params'];

        parse_str($urlParamsString, $urlParamsArray);

        if ($this->getRequest()->isAjax()) {
            $editQuoteUrl = $this->getUrl($controllerPath, $urlParamsArray);
            return $result->setData($editQuoteUrl);
        }
    }
}
