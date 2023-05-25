<?php

namespace Smile\StoreDelivery\Plugin\Quote\Api;

use Closure;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Api\ShipmentEstimationInterface;
use Smile\StoreDelivery\Model\Carrier;

/**
 * Shipment Estimation Plugin. Allows only Store Delivery.
 */
class ShipmentEstimationPlugin
{
    /**
     * Ensure StoreDelivery is the only available shipping method for store delivery addresses.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundEstimateByExtendedAddress(
        ShipmentEstimationInterface $subject,
        Closure $proceed,
        mixed $cartId,
        AddressInterface $address
    ): mixed {
        $shippingMethods = $proceed($cartId, $address);

        // If shipping address is linked to a retailer, remove all methods except Store Delivery.
        /** @var ShippingMethodInterface $shippingMethod */
        foreach ($shippingMethods as $key => $shippingMethod) {
            if (
                ($address->getExtensionAttributes()
                && $address->getExtensionAttributes()->getRetailerId()
                    && ($shippingMethod->getMethodCode() !== Carrier::METHOD_CODE))
                || ((!$address->getExtensionAttributes()
                || (null === $address->getExtensionAttributes()->getRetailerId()))
                    && ($shippingMethod->getMethodCode() === Carrier::METHOD_CODE))
            ) {
                unset($shippingMethods[$key]);
            }
        }

        return $shippingMethods;
    }
}
