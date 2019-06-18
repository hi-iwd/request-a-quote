<?php

namespace IWD\CartToQuote\Model\Config\Backend;

use IWD\CartToQuote\Helper\Data;

class Statuses extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->request = $request;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Validate a domain name value
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        $statuses = $this->request->getParam('iwd_c2q_statuses');

        if ($statuses) {
            if (isset($statuses['new'])) {
                $newStatuses = $statuses['new'];
                unset($statuses['new']);
                foreach ($newStatuses as $key => $values) {
                    $maxId = max(array_keys($statuses)) + 1;
                    $statuses[$maxId] = $values;
                    $statuses[$maxId]['id'] = $maxId;
                }
            }

            $statusesList = [];
            foreach ($statuses as $key => $status) {
                if (isset($status['is_deleted']) && $status['is_deleted']) {
                    //TODO check if we have quotes with this status
                    continue;
                } else {
                    unset($statuses[$key]['is_deleted']);
                    $statusesList[Data::prepareCodeFromTitle($status['name'])] = $statuses[$key];
                }
            }

            $this->setValue(($statusesList ? $this->jsonHelper->jsonEncode($statusesList) : ''));
        } else {
            $this->setValue('');
        }
    }
}
