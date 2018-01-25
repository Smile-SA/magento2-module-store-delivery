<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\StoreDelivery
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2018 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\StoreDelivery\Plugin\Temando;

/**
 * This observer is here to prevent erratic behavior of Temando module. @see https://github.com/magento/magento2/issues/12921
 *
 * @category Smile
 * @package  Smile\StoreDelivery
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class SaveCheckoutFieldsObserverPlugin
{
    /**
     * Better check than what is done in the Temando Observer which is thinking he is the only one adding extension
     * attributes to the Quote address.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param \Temando\Shipping\Observer\SaveCheckoutFieldsObserver $subject  Base Temando Observer
     * @param \Closure                                              $proceed  execute() method of Temando Observer
     * @param \Magento\Framework\Event\Observer                     $observer Magento Event Observer
     */
    public function aroundExecute(
        \Temando\Shipping\Observer\SaveCheckoutFieldsObserver $subject,
        \Closure $proceed,
        \Magento\Framework\Event\Observer $observer
    ) {
        /** @var \Magento\Quote\Api\Data\AddressInterface|\Magento\Quote\Model\Quote\Address $quoteAddress */
        $quoteAddress = $observer->getData('quote_address');
        if ($quoteAddress->getAddressType() !== \Magento\Quote\Model\Quote\Address::ADDRESS_TYPE_SHIPPING) {
            return;
        }

        if (!$quoteAddress->getExtensionAttributes()) {
            return;
        }

        if (!$quoteAddress->getExtensionAttributes()->getCheckoutFields()) {
            return;
        }

        $proceed($observer);
    }
}
