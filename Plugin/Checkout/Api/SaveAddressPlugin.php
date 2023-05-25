<?php

namespace Smile\StoreDelivery\Plugin\Checkout\Api;

use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Smile\Retailer\Api\RetailerRepositoryInterface;

/**
 * Plugin to save a Store address as Shipping Address.
 */
class SaveAddressPlugin
{
    private AddressInterfaceFactory $addressDataFactory;

    public function __construct(
        private RetailerRepositoryInterface $retailerRepository,
        AddressInterfaceFactory $addressInterfaceFactory
    ) {
        $this->addressDataFactory = $addressInterfaceFactory;
    }

    /**
     * Convert Store Address to Shipping Address.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSaveAddressInformation(
        ShippingInformationManagementInterface $subject,
        mixed $cartId,
        ShippingInformationInterface $addressInformation
    ): void {
        $shippingAddress = $addressInformation->getShippingAddress();
        $billingAddress  = $addressInformation->getBillingAddress();

        if ($shippingAddress->getExtensionAttributes() && $shippingAddress->getExtensionAttributes()->getRetailerId()) {
            $retailer = $this->retailerRepository->get($shippingAddress->getExtensionAttributes()->getRetailerId());
            if ($retailer->getId()) {
                $address = $this->addressDataFactory->create(
                    ['data' => $retailer->getAddress()->getData()]
                );
                $shippingAddress->importCustomerAddressData($address);
                $shippingAddress->setCompany($retailer->getName());
                $shippingAddress->setRetailerId((int) $retailer->getId());

                // Potentially copy billing fields (if present, this is not the case when customer is not logged in).
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
