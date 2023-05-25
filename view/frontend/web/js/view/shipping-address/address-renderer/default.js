define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-address/form-popup-state',
    'Magento_Checkout/js/checkout-data',
    'Magento_Customer/js/customer-data',
    'mage-checkout-shipping-address-renderer-default'
], function($, ko, Component, selectShippingAddressAction, quote, formPopUpState, checkoutData, customerData, defaultRenderer) {
    'use strict';

    return defaultRenderer.extend({
        defaults: {
            template: 'Smile_StoreDelivery/shipping-address/address-renderer/default'
        },

        /**
         *  Set selected customer shipping address
         *
         *  Overridden since the new address is now displayed directly in checkout :
         *   - if no address is set, open the popup to create the new address.
         *   - if address has been filled, process the standard action : save it.
         */
        selectAddress: function() {
            if (this.hasAddress()) {
                selectShippingAddressAction(this.address());
                checkoutData.setSelectedShippingAddress(this.address().getKey());
            } else {
                this.editAddress();
            }
        },

        /**
         * Checks if has a current address
         *
         * @returns {boolean}
         */
        hasAddress: function() {
            return this.address().postcode !== undefined && this.address().postcode !== '';
        }
    });
});
