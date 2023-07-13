<?php

declare(strict_types=1);

namespace Smile\StoreDelivery\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Shipping\Model\CarrierFactoryInterface;
use Smile\StoreDelivery\Model\Carrier;

/**
 * Observer to ensure Billing Address has required fields when using StoreDelivery shipping Method.
 */
class QuoteSubmit implements ObserverInterface
{
    public function __construct(private CarrierFactoryInterface $carrierFactory)
    {
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        // Set mandatory fields to shipping address from the billing one, if needed.
        // This can occur when using Store Delivery, since the Shipping Address is set before the Billing.
        // In this case, the shipping address may not have the proper value for FirstName, LastName, and Telephone.

        /** @var CartInterface|Quote $quote */
        $quote = $observer->getQuote();

        /** @var AddressInterface $shippingAddress */
        // @phpstan-ignore-next-line : correct reference to interface
        $shippingAddress = $quote->getShippingAddress();
        if ($shippingAddress) {
            // @phpstan-ignore-next-line : correct reference to interface
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
