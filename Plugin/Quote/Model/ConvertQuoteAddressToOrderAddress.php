<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\StoreDelivery
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2017 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\StoreDelivery\Plugin\Quote\Model;

use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\ToOrderAddress;
use Magento\Sales\Api\Data\OrderAddressInterface;

/**
 * Plugin to copy "retailer_id" field from quote_address to order_address.
 * Done via a plugin because fieldset.xml does not seem to work.
 * see https://magento.stackexchange.com/questions/124712/magento-2-fieldset-xml-copy-fields-from-quote-to-order
 * see https://github.com/magento/magento2/issues/5823
 *
 * @category Smile
 * @package  Smile\StoreDelivery
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class ConvertQuoteAddressToOrderAddress
{
    /**
     * @param ToOrderAddress    $subject      The converter
     * @param \Closure          $proceed      Converter convert() method
     * @param Address           $quoteAddress Quote Address
     * @param array             $data         Data
     *
     * @return OrderAddressInterface Order Address
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundConvert(
        ToOrderAddress $subject,
        \Closure $proceed,
        Address $quoteAddress,
        array $data = []
    ): OrderAddressInterface {
        $orderAddress = $proceed($quoteAddress, $data);
        if ($quoteAddress->getRetailerId()) {
            $orderAddress->setRetailerId($quoteAddress->getRetailerId());
        }

        return $orderAddress;
    }
}
