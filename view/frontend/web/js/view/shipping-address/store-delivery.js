/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 *
 * @category  Smile
 * @package   Smile\StoreDelivery
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2016 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

/*jshint browser:true jquery:true*/
/*global alert*/

define(
    [
        'uiComponent',
        'Magento_Customer/js/customer-data',
        'Smile_StoreDelivery/js/model/empty-address',
        'Smile_StoreDelivery/js/model/store-address',
        'Magento_Customer/js/model/address-list',
        'Magento_Checkout/js/model/shipping-rate-service',
        'Smile_StoreDelivery/js/model/shipping-rate-processor/store-delivery',
        'Magento_Checkout/js/model/shipping-save-processor',
        'Smile_StoreDelivery/js/model/shipping-save-processor/store-delivery'
    ],
    function (
        Component,
        customerData,
        customerAddress,
        storeDeliveryAddress,
        addressList,
        shippingRateService,
        storeDeliveryShippingRateProcessor,
        shippingSaveProcessor,
        storeDeliveryShippingSaveProcessor
    ) {
        'use strict';

        // Register store delivery address provider.
        // Always add it, if the carrier is available.
        // This will by default add a new "empty" address allowing the customer to select a shop.
        if (window.checkoutConfig.activeCarriers.indexOf('smilestoredelivery') !== -1) {
            if (addressList().length === 0) {
                addressList.push(new customerAddress([]));
            }

            var address = new storeDeliveryAddress(null, {});
            var currentStore = customerData.get('current-store');
            if (currentStore() && currentStore().entity_id && currentStore().address_data) {
                var addressData = currentStore().address_data;
                if ((addressData.company === undefined) && currentStore().name) {
                    addressData.company = currentStore().name;
                }
                address = new storeDeliveryAddress(currentStore().entity_id, addressData);
            }
            addressList.push(address);
        }

        // Register rate processor
        shippingRateService.registerProcessor('store-delivery', storeDeliveryShippingRateProcessor);

        //Register StoreDelivery save shipping address processor.
        shippingSaveProcessor.registerProcessor('store-delivery', storeDeliveryShippingSaveProcessor);

        return Component.extend({});
    }
);
