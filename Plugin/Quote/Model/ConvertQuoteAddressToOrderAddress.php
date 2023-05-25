<?php

namespace Smile\StoreDelivery\Plugin\Quote\Model;

use Closure;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\ToOrderAddress;
use Magento\Sales\Api\Data\OrderAddressInterface;

/**
 * Plugin to copy "retailer_id" field from quote_address to order_address.
 * Done via a plugin because fieldset.xml does not seem to work.
 *
 * @see https://magento.stackexchange.com/questions/124712/magento-2-fieldset-xml-copy-fields-from-quote-to-order
 * @see https://github.com/magento/magento2/issues/5823
 */
class ConvertQuoteAddressToOrderAddress
{
    /**
     * Copy retailer_id field from quote_address to order_address.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundConvert(
        ToOrderAddress $subject,
        Closure $proceed,
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
