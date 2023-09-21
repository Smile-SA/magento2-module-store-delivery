<?php

declare(strict_types=1);

namespace Smile\StoreDelivery\Setup\Patch\Data;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Smile\Retailer\Api\Data\RetailerInterface;
use Smile\Seller\Api\Data\SellerInterface;

/**
 * Smile StoreDelivery Install Data.
 */
class AddStoreDeliveryAttributes implements DataPatchInterface, PatchVersionInterface
{
    /**
     * InstallData constructor.
     *
     * @param EavSetupFactory           $eavSetupFactory   EAV Setup Factory.
     * @param ModuleDataSetupInterface  $moduleDataSetup   Module Data Setup.
     */
    public function __construct(
        private readonly EavSetupFactory   $eavSetupFactory,
        private readonly ModuleDataSetupInterface $moduleDataSetup
    ) {
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function apply(): self
    {
        $this->moduleDataSetup->startSetup();

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $this->addShopAttributes($eavSetup);

        $this->moduleDataSetup->endSetup();

        return $this;
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
            SellerInterface::ENTITY_TYPE_CODE,
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

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion(): string
    {
        return '2.0.1';
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
