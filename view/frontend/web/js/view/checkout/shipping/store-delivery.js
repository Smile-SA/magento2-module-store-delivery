define([
    'uiComponent',
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/select-shipping-method',
    'Magento_Checkout/js/checkout-data',
    'Magento_Customer/js/customer-data'
], function (Component, $, quote, selectShippingMethodAction, checkoutData, storage) {
    'use strict';

    var retailer = storage.get('current-store');

    return Component.extend({
        defaults: {
            template: 'Smile_StoreDelivery/checkout/shipping/store-delivery',
            methodCode: 'smilestoredelivery',
            carrierCode: 'smilestoredelivery',
            methodTitle: 'Store Delivery',
            carrierTitle: 'Store Delivery',
            retailerId : retailer().entity_id || 0,
            init: true
        },

        initialize: function() {
            this._super();

            quote.shippingAddress.subscribe(function () {
                var type = quote.shippingAddress().getType();
                if (type === 'store-delivery') {
                    selectShippingMethodAction({
			            carrier_code: this.getCarrierCode(),
                        carrier_title: this.getCarrierTitle(),
                        method_code: this.getMethodCode(),
                        method_title: this.getMethodTitle(),
                    });
                    checkoutData.setSelectedShippingRate(this.getCarrierCode() + '_' + this.getMethodCode());
                }
            }.bind(this));

            this.observe(['retailerId', 'init']);
        },

        isActive: function() {
            return window.checkoutConfig
                && window.checkoutConfig.activeCarriers
                && (window.checkoutConfig.activeCarriers.indexOf(this.carrierCode) !== -1)  ;
        },

        isVisible: function() {
            if (this.init === true) {
                return true;
            }

            return true;
        },

        initComponent: function() {
            let self = this;
            setTimeout(function() {self.init(false);}, 10000);
        },

        getCurrentStore: function() {
            return this.retailerId();
        },

        getMethodCode: function() {
            return this.methodCode;
        },

	    getMethodTitle: function() {
            return this.methodTitle;
        },

        getCarrierCode: function() {
            return this.carrierCode;
        },

	    getCarrierTitle: function() {
            return this.carrierTitle;
        },

        renderComponent: function() {
            this.requestChild('store-delivery')().canRenderMap(true);
        }

    });
});
