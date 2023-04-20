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
namespace Smile\StoreDelivery\Plugin\Quote\Api;

use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Smile\StoreDelivery\Model\Carrier;

/**
 * Plugin to remove Store Delivery from available carriers for estimation by addressId.
 *
 * @category Smile
 * @package  Smile\StoreDelivery
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class ShippingMethodManagementPlugin
{
    /**
     * Remove StoreDelivery from available methods when estimating by address Id (existing customer addresses).
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param ShippingMethodManagementInterface $subject   Shipping Method Management
     * @param \Closure                          $proceed   estimateByAddressId() method
     * @param mixed                             $cartId    The shopping cart ID.
     * @param mixed                             $addressId The estimate address id
     *
     * @return mixed
     */
    public function aroundEstimateByAddressId(
        ShippingMethodManagementInterface $subject,
        \Closure $proceed,
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
