<?php

namespace Smile\StoreDelivery\Setup;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Smile StoreDelivery Install Schema.
 * Mandatory for Magento <2.2 to backport a fix on shipping_method field.
 */
class InstallSchema implements InstallSchemaInterface
{
    public function __construct(private ProductMetadataInterface $metadata)
    {
    }

    /**
     * @inheritdoc
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($this->metadata->getVersion(), '2.2.0', '<')) {
            $connection = $setup->getConnection();

            // Set the "shipping_method" column of quote_address and sales_order tables to 120 chars length.
            // This is a fix for a Magento's internal issue.
            // @see https://github.com/magento/magento2/issues/6475
            // This has been fixed in Magento 2.2 only, that's why it's backported here for now.
            // This setup will have no effect on a Magento > 2.2 and is only needed for < 2.2 instances.
            $connection->modifyColumn(
                $setup->getTable('quote_address'),
                'shipping_method',
                [
                    'type'   => Table::TYPE_TEXT,
                    'length' => 120,
                ]
            );

            $connection->modifyColumn(
                $setup->getTable('sales_order'),
                'shipping_method',
                [
                    'type'   => Table::TYPE_TEXT,
                    'length' => 120,
                ]
            );
        }

        $setup->endSetup();
    }
}
