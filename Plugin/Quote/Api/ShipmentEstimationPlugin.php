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
namespace Smile\StorePickup\Plugin\Quote\Api;

use Magento\Quote\Api\ShipmentEstimationInterface;

/**
 * Shipment Estimation Plugin.
 * Allow only Store Pickup
 *
 * @category Smile
 * @package  Smile\StorePickup
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class ShipmentEstimationPlugin
{
    /**
     * Ensure StorePickup is the only available shipping method for store pickup addresses.
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param \Magento\Quote\Api\ShipmentEstimationInterface $subject Shipment Estimation Interface
     * @param \Closure                                       $proceed The estimateByExtendedAddress method
     * @param mixed                                          $cartId  The cart Id
     * @param \Magento\Quote\Api\Data\AddressInterface       $address The Shipping Address
     *
     * @return mixed
     */
    public function aroundEstimateByExtendedAddress(
        ShipmentEstimationInterface $subject,
        \Closure $proceed,
        $cartId,
        \Magento\Quote\Api\Data\AddressInterface $address
    ) {
        $shippingMethods = $proceed($cartId, $address);

        // If shipping address is linked to a retailer, remove all methods except Store Pickup.
        /** @var \Magento\Quote\Api\Data\ShippingMethodInterface $shippingMethod */
        foreach ($shippingMethods as $key => $shippingMethod) {
            if (($address->getExtensionAttributes() && $address->getExtensionAttributes()->getRetailerId()
                    && ($shippingMethod->getMethodCode() !== \Smile\StorePickup\Model\Carrier::METHOD_CODE)) ||
                ((!$address->getExtensionAttributes() || (null === $address->getExtensionAttributes()->getRetailerId()))
                    && ($shippingMethod->getMethodCode() === \Smile\StorePickup\Model\Carrier::METHOD_CODE))
            ) {
                unset($shippingMethods[$key]);
            }
        }

        return $shippingMethods;
    }
}
