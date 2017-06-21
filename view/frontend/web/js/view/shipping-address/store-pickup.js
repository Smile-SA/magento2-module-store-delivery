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
        'Smile_StorePickup/js/model/store-address',
        'Magento_Customer/js/model/address-list',
        'Magento_Checkout/js/model/shipping-save-processor',
        'Smile_StorePickup/js/model/shipping-save-processor/store-pickup'
    ],
    function (
        Component,
        storePickupAddress,
        addressList,
        shippingSaveProcessor,
        storePickupShippingSaveProcessor
    ) {
        'use strict';

        //Register gift registry address provider
        if(window.checkoutConfig.activeCarriers.indexOf('smile_store_pickup') !== -1) {
            /*if (addressList.length === 0) {
                addressList.push(new storePickupAddress(null, {}));
            }*/
        }

        //Register StorePickup save shipping address processor
        shippingSaveProcessor.registerProcessor('store-pickup', storePickupShippingSaveProcessor);

        return Component.extend({});
    }
);
