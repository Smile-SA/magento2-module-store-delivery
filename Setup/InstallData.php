<?php

namespace Smile\StoreDelivery\Setup;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Quote\Setup\QuoteSetup;
use Magento\Sales\Setup\SalesSetup;
use Smile\Retailer\Api\Data\RetailerInterface;
use Smile\Seller\Api\Data\SellerInterface;

/**
 * Smile StoreDelivery Install Data.
 */
class InstallData implements InstallDataInterface
{
    /**
     * @inheritdoc
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->addSalesAttributes($setup);

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $this->addShopAttributes($eavSetup);

        $setup->endSetup();
    }

    /**
     * Add retailer_id attribute to sales_order_address and sales_quote_address.
     */
    private function addSalesAttributes(ModuleDataSetupInterface $setup): void
    {
        /** @var SalesSetup $salesSetup */
        $salesSetup = $this->salesSetupFactory->create(['resourceName' => 'sales_setup', 'setup' => $setup]);

        /** @var QuoteSetup $quoteSetup */
        $quoteSetup = $this->quoteSetupFactory->create(['resourceName' => 'quote_setup', 'setup' => $setup]);

        $quoteSetup->addAttribute(
            'quote_address',
            'retailer_id',
            ['type' => Table::TYPE_INTEGER, 'visible' => false]
        );

        $salesSetup->addAttribute(
            'order_address',
            'retailer_id',
            ['type' => Table::TYPE_INTEGER, 'visible' => false]
        );
    }

    /**
     * Add allow_store_delivery attribute to Retailer.
     */
    private function addShopAttributes(EavSetup $eavSetup): void
    {
        $entityId  = SellerInterface::ENTITY;
        $attrSetId = RetailerInterface::ATTRIBUTE_SET_RETAILER;
        $groupId   = 'Shipping';

        $eavSetup->addAttributeGroup($entityId, $attrSetId, $groupId, 200);

        $eavSetup->addAttribute(
            SellerInterface::ENTITY,
            'allow_store_delivery',
            [
                'type' => 'int',
                'label' => 'Allow Store Delivery',
                'input' => 'boolean',
                'required' => true,
                'user_defined' => true,
                'sort_order' => 10,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'source' => Boolean::class,
            ]
        );

        $eavSetup->addAttributeToGroup($entityId, $attrSetId, $groupId, 'allow_store_delivery', 10);
    }
}
