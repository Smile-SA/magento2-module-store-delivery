/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 *
 * @category  Smile
 * @package   Smile\StorePickup
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
        'Smile_StorePickup/js/model/store-address',
        'Magento_Customer/js/model/address-list',
        'Magento_Checkout/js/model/shipping-rate-service',
        'Smile_StorePickup/js/model/shipping-rate-processor/store-pickup',
        'Magento_Checkout/js/model/shipping-save-processor',
        'Smile_StorePickup/js/model/shipping-save-processor/store-pickup'
    ],
    function (
        Component,
        customerData,
        storePickupAddress,
        addressList,
        shippingRateService,
        storePickupShippingRateProcessor,
        shippingSaveProcessor,
        storePickupShippingSaveProcessor
    ) {
        'use strict';

        // Register store pickup address provider.
        // Always add it, if the carrier is available.
        // This will by default add a new "empty" address allowing the customer to select a shop.
        if (window.checkoutConfig.activeCarriers.indexOf('smile_store_pickup') !== -1) {
            var address = new storePickupAddress(null, {});
            var currentStore = customerData.get('current-store');
            if (currentStore() && currentStore().entity_id && currentStore().address_data) {
                var addressData = currentStore().address_data;
                if ((addressData.company === undefined) && currentStore().name) {
                    addressData.company = currentStore().name;
                }
                address = new storePickupAddress(currentStore().entity_id, addressData);
            }
            addressList.push(address);
        }

        // Register rate processor
        shippingRateService.registerProcessor('store-pickup', storePickupShippingRateProcessor);

        //Register StorePickup save shipping address processor.
        shippingSaveProcessor.registerProcessor('store-pickup', storePickupShippingSaveProcessor);

        return Component.extend({});
    }
);
