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
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Smile\Retailer\Api\RetailerRepositoryInterface
     */
    private $retailerRepository;

    /**
     * @var \Magento\Customer\Api\Data\AddressInterfaceFactory
     */
    private $addressDataFactory;

    /**
     * @param RetailerRepositoryInterface                        $retailerRepository      Retailer Repository
     * @param \Magento\Customer\Model\Session                    $customerSession         Customer session
     * @param \Magento\Customer\Api\Data\AddressInterfaceFactory $addressInterfaceFactory Address Factory
     */
    public function __construct(
        RetailerRepositoryInterface $retailerRepository,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressInterfaceFactory
    ) {
        $this->retailerRepository = $retailerRepository;
        $this->customerSession    = $customerSession;
        $this->addressDataFactory = $addressInterfaceFactory;
    }

    /**
     * Convert Store Address to Shipping Address
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param \Magento\Checkout\Api\ShippingInformationManagementInterface $subject            Shipping Information Management
     * @param int                                                          $cartId             Cart Id
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface      $addressInformation Address Information
     *
     * @return void
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Api\ShippingInformationManagementInterface $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
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
