<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\StorePickup
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2017 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\StorePickup\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;

/**
 * Smile StorePickup Install Data.
 *
 * @category Smile
 * @package  Smile\StorePickup
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var \Magento\Sales\Setup\SalesSetupFactory
     */
    private $salesSetupFactory;

    /**
     * @var \Magento\Quote\Setup\QuoteSetupFactory
     */
    private $quoteSetupFactory;

    /**
     * InstallData constructor.
     *
     * @param \Magento\Sales\Setup\SalesSetupFactory $salesSetupFactory Sales Setup
     * @param \Magento\Quote\Setup\QuoteSetupFactory $quoteSetupFactory Quote Setup
     */
    public function __construct(
        SalesSetupFactory $salesSetupFactory,
        QuoteSetupFactory $quoteSetupFactory
    ) {
        $this->salesSetupFactory = $salesSetupFactory;
        $this->quoteSetupFactory = $quoteSetupFactory;
    }


    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->addAttributes($setup);

        $setup->endSetup();
    }

    /**
     * Add retailer_id attribute to sales_order_address and sales_quote_address
     *
     * @param ModuleDataSetupInterface $setup Data Setup
     */
    private function addAttributes($setup)
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
}
