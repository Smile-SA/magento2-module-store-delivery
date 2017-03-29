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

define([
    'uiComponent',
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/customer-data'
], function (Component, $, quote, storage) {
    'use strict';

    var retailer = storage.get('current-store');

    return Component.extend({
        defaults: {
            template: 'Smile_StorePickup/checkout/shipping/store-pickup',
            methodCode: 'smile_store_pickup',
            carrierCode: 'smile_store_pickup',
            retailerId : retailer().entity_id || 0,
            init: true
        },

        initialize: function() {
            this._super();
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
            var shippingMethod = quote.shippingMethod();
            var isStorePickup  = shippingMethod && shippingMethod.carrier_code && (shippingMethod.carrier_code === this.carrierCode);
            return !quote.isVirtual() && isStorePickup;
        },

        initComponent: function() {
            this.moveComponent();
            setTimeout(this.init(false), 10000);
        },

        getCurrentStore: function() {
            return this.retailerId();
        },

        getMethodCode: function() {
            return this.methodCode;
        },

        getCarrierCode: function() {
            return this.carrierCode;
        },

        moveComponent: function() {
            var shippingMethodContainer = $('#checkout-shipping-method-load');
            var storePickupAdditionalContainer = $('#smile-store-pickup-container');
            if (shippingMethodContainer && storePickupAdditionalContainer) {
                var storePickupId = 'label_carrier_' + this.methodCode + '_' + this.carrierCode;
                shippingMethodContainer.find('td#' + storePickupId).closest('table').after(storePickupAdditionalContainer);
            }
        }
    });
});
