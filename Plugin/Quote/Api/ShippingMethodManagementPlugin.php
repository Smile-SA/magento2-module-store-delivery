<?php

declare(strict_types=1);

namespace Smile\StoreDelivery\Plugin\Quote\Api;

use Closure;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Smile\StoreDelivery\Model\Carrier;

/**
 * Plugin to remove Store Delivery from available carriers for estimation by addressId.
 */
class ShippingMethodManagementPlugin
{
    /**
     * Remove StoreDelivery from available methods when estimating by address Id (existing customer addresses).
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundEstimateByAddressId(
        ShippingMethodManagementInterface $subject,
        Closure $proceed,
        mixed $cartId,
        mixed $addressId
    ): mixed {
        $shippingMethods = $proceed($cartId, $addressId);

        /** @var ShippingMethodInterface $shippingMethod */
        foreach ($shippingMethods as $key => $shippingMethod) {
            if ($shippingMethod->getMethodCode() === Carrier::METHOD_CODE) {
                unset($shippingMethods[$key]);
            }
        }

        return $shippingMethods;
    }
}
