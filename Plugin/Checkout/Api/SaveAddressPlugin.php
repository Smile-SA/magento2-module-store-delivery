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
namespace Smile\StoreDelivery\Plugin\Checkout\Api;

use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Model\Session;
use Smile\Retailer\Api\RetailerRepositoryInterface;

/**
 * Plugin to save a Store address as Shipping Address
 *
 * @category Smile
 * @package  Smile\StoreDelivery
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class SaveAddressPlugin
{
    /**
     * @var Session
     */
    private Session $customerSession;

    /**
     * @var RetailerRepositoryInterface
     */
    private RetailerRepositoryInterface $retailerRepository;

    /**
     * @var AddressInterfaceFactory
     */
    private AddressInterfaceFactory $addressDataFactory;

    /**
     * @param RetailerRepositoryInterface   $retailerRepository      Retailer Repository
     * @param Session                       $customerSession         Customer session
     * @param AddressInterfaceFactory       $addressInterfaceFactory Address Factory
     */
    public function __construct(
        RetailerRepositoryInterface $retailerRepository,
        Session $customerSession,
        AddressInterfaceFactory $addressInterfaceFactory
    ) {
        $this->retailerRepository = $retailerRepository;
        $this->customerSession    = $customerSession;
        $this->addressDataFactory = $addressInterfaceFactory;
    }

    /**
     * Convert Store Address to Shipping Address
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param ShippingInformationManagementInterface    $subject            Shipping Information Management
     * @param mixed                                     $cartId             Cart Id
     * @param ShippingInformationInterface              $addressInformation Address Information
     *
     * @return void
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
