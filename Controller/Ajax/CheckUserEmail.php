<?php

namespace IWD\CartToQuote\Controller\Ajax;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Api\CustomerRepositoryInterface;
use IWD\CartToQuote\Helper\Data;
use IWD\CartToQuote\Model\Request\Form\Fields;
use IWD\CartToQuote\Model\Logger\Logger;

/**
 * Class CheckUserEmail
 * @package IWD\CartToQuote\Controller\Ajax
 */
class CheckUserEmail extends Action
{
    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Fields
     */
    private $fields;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * CustomerData constructor.
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param Fields $fields
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        Fields $fields,
        Logger $logger,
        CustomerRepositoryInterface $customerRepository
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->logger = $logger;
        $this->fields = $fields;
        $this->customerRepository = $customerRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $email = $this->getRequest()->getParam('email', '');
            $user = $this->customerRepository->get($email);

            $this->data['exists'] = ($user->getId() != null);
            $this->data['status'] = true;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->data['status'] = false;
        }

        /**
         * @var \Magento\Framework\Controller\Result\Json $resultJson
         */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($this->data);
        return $resultJson;
    }

    private function getDataFromCustomerAttribute()
    {
        $user = $this->customerSession->getCustomer();
        $data = $user->getData('iwd_c2q_customer_data');
        if (!empty($data) && !empty(json_decode($data, true))) {
            $this->data['data'] = json_decode($data, true);
        }
    }

    private function prepareData()
    {
        $user = $this->customerSession->getCustomer();

        if (empty($this->data['data'])) {
            $data = [];
            $map = [
                'country' => 'country_id',
                'state_id' => 'region_id',
                'state' => 'region',
                'date_of_birthday' => 'dob',
            ];

            $fields = $this->fields->getFields();
            foreach ($fields as $field) {
                $name = Data::prepareCodeFromTitle($field['name']);
                if ($name) {
                    if (isset($map[$name])) {
                        $customerValue = $this->getCustomerData($map[$name]);
                    } else {
                        $customerValue = $this->getCustomerData($name);
                        if (empty($customerValue)) {
                            $name2 = str_replace('_', '', $name);
                            if ($name2 != $name) {
                                $customerValue = $this->getCustomerData($name2);
                            }
                        }
                    }

                    $data[$name] = $customerValue;
                }
            }

            if (isset($data['state'])) {
                $data['state_id'] = $this->getCustomerData('region_id');
            }

            $this->data['data']= isset($this->data['data']) ? $this->data['data'] : [];
            $this->data['data'] = array_merge($data, $this->data['data']);
        }
    }

    private function getCustomerData($key)
    {
        $user = $this->customerSession->getCustomer();
        $customerValue = '';
        $customerValue = empty($customerValue) ? $user->getData($key) : $customerValue;
        $customerValue = (empty($customerValue) && $user->getDefaultBillingAddress())
            ? $user->getDefaultBillingAddress()->getData($key) : $customerValue;
        $customerValue = (empty($customerValue) && $user->getDefaultShippingAddress())
            ? $user->getDefaultShippingAddress()->getData($key) : $customerValue;

        if ($key == 'dob') {
            $customerValue = date('m/d/Y', strtotime($customerValue));
        }

        return $customerValue;
    }
}
