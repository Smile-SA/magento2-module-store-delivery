<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\StorePickup
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2017 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\StorePickup\Plugin\Quote\Model;

/**
 * Plugin to copy "retailer_id" field from quote_address to order_address.
 * Done via a plugin because fieldset.xml does not seem to work.
 * see https://magento.stackexchange.com/questions/124712/magento-2-fieldset-xml-copy-fields-from-quote-to-order
 * see https://github.com/magento/magento2/issues/5823
 *
 * @category Smile
 * @package  Smile\StorePickup
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class ConvertQuoteAddressToOrderAddress
{
    /**
     * @param \Magento\Quote\Model\Quote\Address\ToOrderAddress $subject      The converter
     * @param \Closure                                          $proceed      Converter convert() method
     * @param \Magento\Quote\Model\Quote\Address                $quoteAddress Quote Address
     * @param array                                             $data         Data
     *
     * @return \Magento\Sales\Api\Data\OrderAddressInterface Order Address
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundConvert(
        \Magento\Quote\Model\Quote\Address\ToOrderAddress $subject,
        \Closure $proceed,
        \Magento\Quote\Model\Quote\Address $quoteAddress,
        $data = []
    ) {
        $orderAddress = $proceed($quoteAddress, $data);
        if ($quoteAddress->getRetailerId()) {
            $orderAddress->setRetailerId($quoteAddress->getRetailerId());
        }

        return $orderAddress;
    }
}
