<?php

namespace IWD\CartToQuote\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\Json\Helper\Data as JsonData;
use IWD\CartToQuote\Helper\Data as C2QHelper;

/**
 * Class InstallData
 * @package IWD\CartToQuote\Setup
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var \IWD\CartToQuote\Helper\Data
     */
    private $c2qHelper;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * InstallData constructor.
     * @param JsonData $jsonHelper
     * @param C2QHelper $c2qHelper
     * @param ScopeConfigInterface $scopeConfig
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        JsonData $jsonHelper,
        C2QHelper $c2qHelper,
        ScopeConfigInterface $scopeConfig,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->c2qHelper = $c2qHelper;
        $this->scopeConfig = $scopeConfig;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        $this->createSettings($setup);
        $this->updateCustomerAttributes($setup);

        $setup->endSetup();
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    public function createSettings(ModuleDataSetupInterface $setup)
    {
        if (!$this->scopeConfig->getValue(\IWD\CartToQuote\Model\Request\Status::STATUSES_PATH)) {
            $statuses = $this->jsonHelper->jsonEncode(\IWD\CartToQuote\Model\Request\Status::getNativeStatuses());
            $value = [
                [
                    'scope' => \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                    'scope_id' => 0,
                    'path' => \IWD\CartToQuote\Model\Request\Status::STATUSES_PATH,
                    'value' => $statuses,
                ],
            ];

            $setup->getConnection()->insertMultiple($setup->getTable('core_config_data'), $value);
        }

        if (!$this->scopeConfig->getValue(\IWD\CartToQuote\Model\Request\Form\Fields::FIELDS_PATH)) {
            $fields = $this->jsonHelper->jsonEncode(\IWD\CartToQuote\Model\Request\Form\Fields::getNativeFields());
            $value = [
                [
                    'scope' => \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                    'scope_id' => 0,
                    'path' => \IWD\CartToQuote\Model\Request\Form\Fields::FIELDS_PATH,
                    'value' => $fields,
                ],
            ];

            $setup->getConnection()->insertMultiple($setup->getTable('core_config_data'), $value);
        }

        $setup->getConnection()->addColumn(
            $setup->getTable('quote'),
            'editable_items',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                'default' => 1,
                'nullable' => false,
                'comment' => 'Allow Quote Items modification on the frontend',
            ]
        );
    }

    /**
     * Add attribute to customer for save customer data
     *
     * @param ModuleDataSetupInterface $setup
     */
    private function updateCustomerAttributes(ModuleDataSetupInterface $setup)
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            Customer::ENTITY,
            'iwd_c2q_customer_data',
            [
                'type' => 'text',
                'label' => 'Data For Request A Quote Form',
                'input' => 'text',
                'source' => '',
                'visible' => false,
                'required' => false,
                'default' => '{}',
                'frontend' => '',
                'unique' => false,
                'note' => ''
            ]
        );
    }
}
