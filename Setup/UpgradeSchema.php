<?php
/**
 * Created by PhpStorm.
 * User: vlad
 * Date: 12.07.2018
 * Time: 11:30
 */

namespace IWD\CartToQuote\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class UpgradeSchema
 * @package IWD\CartToQuote\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    )
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->addExpiredAtColumnToQuote($setup);
        }
        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $this->addRaQFlagToQuote($setup);
        }
        $setup->endSetup();
    }

    private function addRaQFlagToQuote($setup)
    {
        $setup->getConnection()
            ->addColumn(
                $setup->getTable('quote'),
                'is_iwd_disabled_reorder',
                [
                    'type' => Table::TYPE_BOOLEAN,
                    'nullable' => true,
                    'default' => '0',
                    'comment' => 'Is Disabled Reorder',
                ]
            );
    }

    /**
     * @param $setup
     */
    private function addExpiredAtColumnToQuote($setup)
    {
        $setup->getConnection()
            ->addColumn(
                $setup->getTable('quote'),
                'expired_at',
                [
                    'type' => Table::TYPE_DATE,
                    'nullable' => true,
                    'comment' => 'Quote Expiration Date',
                ]
            );
        $setup->getConnection()
            ->addColumn(
                $setup->getTable('quote'),
                'is_quote_expired',
                [
                    'type' => Table::TYPE_BOOLEAN,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Is Quote Expired',
                ]
            );
    }
}
