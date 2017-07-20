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
namespace Smile\StorePickup\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Shipping\Model\CarrierFactoryInterface;

/**
 * Observer to ensure Billing Address has required fields when using StorePickup shipping Method.
 *
 * @category Smile
 * @package  Smile\StorePickup
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class QuoteSubmit implements ObserverInterface
{
    /**
     * @var \Magento\Shipping\Model\CarrierFactoryInterface
     */
    private $carrierFactory;

    /**
     * QuoteSubmit constructor.
     *
     * @param \Magento\Shipping\Model\CarrierFactoryInterface $carrierFactory Carrier Factory
     */
    public function __construct(CarrierFactoryInterface $carrierFactory)
    {
        $this->carrierFactory = $carrierFactory;
    }

    /**
     * Set mandatory fields to shipping address from the billing one, if needed.
     *
     * This can occur when using Store Pickup, since the Shipping Address is set before the Billing.
     * In this case, the shipping address may not have the proper value for FirstName, LastName, and Telephone.
     *
     * @event checkout_submit_before
     *
     * @param \Magento\Framework\Event\Observer $observer The observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Quote\Api\Data\CartInterface $quote */
        $quote = $observer->getQuote();

        /** @var \Magento\Quote\Api\Data\AddressInterface $shippingAddress */
        $shippingAddress = $quote->getShippingAddress();
        if ($shippingAddress) {
            $shippingMethod = $shippingAddress->getShippingMethod();
            if ($shippingMethod) {
                $methodCode = \Smile\StorePickup\Model\Carrier::METHOD_CODE;
                $carrier    = $this->carrierFactory->getIfActive($methodCode);
                if ($shippingMethod === sprintf('%s_%s', $methodCode, $carrier->getCarrierCode())) {
                    $billingAddress = $quote->getBillingAddress();

                    if (!$shippingAddress->getFirstname()) {
                        $shippingAddress->setFirstname($billingAddress->getFirstname());
                    }
                    if (!$shippingAddress->getLastname()) {
                        $shippingAddress->setLastname($billingAddress->getLastname());
                    }
                    if (!$shippingAddress->getTelephone()) {
                        $shippingAddress->setTelephone($billingAddress->getTelephone());
                    }
                }
            }
        }
    }
}
