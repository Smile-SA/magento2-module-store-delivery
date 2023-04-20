<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\StoreDelivery
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2017 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\StoreDelivery\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Smile\Retailer\Api\Data\RetailerInterface;
use Smile\Seller\Api\Data\SellerInterface;

/**
 * Smile StoreDelivery Install Data.
 *
 * @category Smile
 * @package  Smile\StoreDelivery
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var SalesSetupFactory
     */
    private SalesSetupFactory $salesSetupFactory;

    /**
     * @var QuoteSetupFactory
     */
    private QuoteSetupFactory $quoteSetupFactory;

    /**
     * @var EavSetupFactory
     */
    private EavSetupFactory $eavSetupFactory;

    /**
     * InstallData constructor.
     *
     * @param SalesSetupFactory $salesSetupFactory Sales Setup
     * @param QuoteSetupFactory $quoteSetupFactory Quote Setup
     * @param EavSetupFactory   $eavSetupFactory   EAV Setup Factory.
     */
    public function __construct(
        SalesSetupFactory $salesSetupFactory,
        QuoteSetupFactory $quoteSetupFactory,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->salesSetupFactory = $salesSetupFactory;
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->eavSetupFactory   = $eavSetupFactory;
    }


    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context): void
    {
        $setup->startSetup();

        $this->addSalesAttributes($setup);

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $this->addShopAttributes($eavSetup);

        $setup->endSetup();
    }

    /**
     * Add retailer_id attribute to sales_order_address and sales_quote_address
     *
     * @param ModuleDataSetupInterface $setup Data Setup
     */
    private function addSalesAttributes($setup): void
    {
        /** @var \Magento\Sales\Setup\SalesSetup $salesSetup */
        $salesSetup = $this->salesSetupFactory->create(['resourceName' => 'sales_setup', 'setup' => $setup]);

        /** @var \Magento\Quote\Setup\QuoteSetup $quoteSetup */
        $quoteSetup = $this->quoteSetupFactory->create(['resourceName' => 'quote_setup', 'setup' => $setup]);

        $quoteSetup->addAttribute(
            'quote_address',
            'retailer_id',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'visible' => false]
        );

        $salesSetup->addAttribute(
            'order_address',
            'retailer_id',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'visible' => false]
        );
    }

    /**
     * Add allow_store_delivery attribute to Retailer
     *
     * @param \Magento\Eav\Setup\EavSetup $eavSetup EAV module Setup
     */
    private function addShopAttributes($eavSetup): void
    {
        $entityId  = SellerInterface::ENTITY;
        $attrSetId = RetailerInterface::ATTRIBUTE_SET_RETAILER;
        $groupId   = 'Shipping';

        $eavSetup->addAttributeGroup($entityId, $attrSetId, $groupId, 200);

        $eavSetup->addAttribute(
            SellerInterface::ENTITY,
            'allow_store_delivery',
            [
                'type'         => 'int',
                'label'        => 'Allow Store Delivery',
                'input'        => 'boolean',
                'required'     => true,
                'user_defined' => true,
                'sort_order'   => 10,
                'global'       => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'source'       => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
            ]
        );

        $eavSetup->addAttributeToGroup($entityId, $attrSetId, $groupId, 'allow_store_delivery', 10);
    }
}
