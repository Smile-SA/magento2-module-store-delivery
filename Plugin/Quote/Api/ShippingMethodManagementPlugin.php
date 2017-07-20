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

use Magento\Quote\Api\ShippingMethodManagementInterface;
use Smile\StorePickup\Model\Carrier;

/**
 * Plugin to remove Store Pickup from available carriers for estimation by addressId.
 *
 * @category Smile
 * @package  Smile\StorePickup
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class ShippingMethodManagementPlugin
{
    /**
     * Remove StorePickup from available methods when estimating by address Id (existing customer addresses).
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param \Magento\Quote\Api\ShippingMethodManagementInterface $subject   Shipping Method Management
     * @param \Closure                                             $proceed   estimateByAddressId() method
     * @param int                                                  $cartId    The shopping cart ID.
     * @param int                                                  $addressId The estimate address id
     *
     * @return mixed
     */
    public function aroundEstimateByAddressId(
        ShippingMethodManagementInterface $subject,
        \Closure $proceed,
        $cartId,
        $addressId
    ) {
        $shippingMethods = $proceed($cartId, $addressId);

        /** @var \Magento\Quote\Api\Data\ShippingMethodInterface $shippingMethod */
        foreach ($shippingMethods as $key => $shippingMethod) {
            if ($shippingMethod->getMethodCode() === Carrier::METHOD_CODE) {
                unset($shippingMethods[$key]);
            }
        }

        return $shippingMethods;
    }
}
