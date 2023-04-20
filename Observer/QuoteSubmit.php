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
namespace Smile\StoreDelivery\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Shipping\Model\CarrierFactoryInterface;
use Smile\StoreDelivery\Model\Carrier;

/**
 * Observer to ensure Billing Address has required fields when using StoreDelivery shipping Method.
 *
 * @category Smile
 * @package  Smile\StoreDelivery
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class QuoteSubmit implements ObserverInterface
{
    /**
     * @var CarrierFactoryInterface
     */
    private CarrierFactoryInterface $carrierFactory;

    /**
     * QuoteSubmit constructor.
     *
     * @param CarrierFactoryInterface $carrierFactory Carrier Factory
     */
    public function __construct(CarrierFactoryInterface $carrierFactory)
    {
        $this->carrierFactory = $carrierFactory;
    }

    /**
     * Set mandatory fields to shipping address from the billing one, if needed.
     *
     * This can occur when using Store Delivery, since the Shipping Address is set before the Billing.
     * In this case, the shipping address may not have the proper value for FirstName, LastName, and Telephone.
     *
     * @event checkout_submit_before
     *
     * @param Observer $observer The observer
     */
    public function execute(Observer $observer): void
    {
        /** @var CartInterface $quote */
        $quote = $observer->getQuote();

        /** @var AddressInterface $shippingAddress */
        $shippingAddress = $quote->getShippingAddress();
        if ($shippingAddress) {
            $shippingMethod = $shippingAddress->getShippingMethod();
            if ($shippingMethod) {
                $methodCode = Carrier::METHOD_CODE;
                $carrier    = $this->carrierFactory->getIfActive($methodCode);
                if ($carrier && $shippingMethod === sprintf('%s_%s', $methodCode, $carrier->getCarrierCode())) {
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
