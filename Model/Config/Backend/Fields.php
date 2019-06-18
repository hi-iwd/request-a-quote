<?php

namespace IWD\CartToQuote\Model\Config\Backend;

class Fields extends \Magento\Framework\App\Config\Value
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
        $fields = $this->request->getParam('iwd_c2q_fields');
        if ($fields) {
            if (isset($fields['new'])) {
                $newFields = $fields['new'];
                unset($fields['new']);
                foreach ($newFields as $key => $values) {
                    $maxId = max(array_keys($fields)) + 1;
                    $fields[$maxId] = $values;
                    $fields[$maxId]['id'] = $maxId;
                }
            }

            foreach ($fields as $key => $field) {
                if (isset($field['is_deleted']) && $field['is_deleted']) {
                    unset($fields[$key]);
                    continue;
                }

                unset($fields[$key]['is_deleted']);
            }

            $this->setValue(($fields ? $this->jsonHelper->jsonEncode($fields) : ''));
        } else {
            $this->setValue('');
        }
    }
}
