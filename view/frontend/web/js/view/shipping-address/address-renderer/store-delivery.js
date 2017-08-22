/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/checkout-data',
    'Magento_Ui/js/modal/modal',
    'Magento_Customer/js/customer-data'
], function($, ko, Component, selectShippingAddressAction, quote, checkoutData, modal, customerData) {

    'use strict';

    var popUp = null;
    var countryData = customerData.get('directory-data');

    return Component.extend({
        defaults: {
            template: 'Smile_StoreDelivery/shipping-address/address-renderer/store-delivery'
        },
        isFormPopUpVisible: ko.observable(false),

        initialize: function() {
            var self = this;

            this._super();

            this.isFormPopUpVisible.subscribe(function (value) {
                if (value) {
                    self.getPopUp().openModal();
                    self.requestChild('smile-store-delivery')().renderComponent();
                }
            });
        },

        initObservable: function () {
            this._super();
            this.isSelected = ko.computed(function() {
                var isSelected = false;
                var shippingAddress = quote.shippingAddress();
                if (shippingAddress) {
                    isSelected = shippingAddress.getKey() == this.address().getKey();
                }
                return isSelected;
            }, this);

            return this;
        },

        /**
         * Set selected customer shipping address
         **/
        selectAddress: function() {
            if (this.hasAddress()) {
                selectShippingAddressAction(this.address());
                checkoutData.setSelectedShippingAddress(this.address().getKey());
            } else {
                this.showPopup();
            }
        },

        /**
         * Update current quote address
         */
        updateAddress: function() {
            this.address(quote.shippingAddress());
        },

        /**
         * Checks if has a current address
         *
         * @returns {boolean}
         */
        hasAddress: function() {
            return (this.address().getRetailerId() !== null);
        },

        showPopup: function() {
            this.isFormPopUpVisible(true);
        },

        /**
         * @return {*}
         */
        getPopUp: function () {
            var self = this,
                buttons;

            if (!popUp) {
                buttons = this.popUpForm.options.buttons;
                this.popUpForm.options.buttons = [
                    {
                        text: buttons.save.text ? buttons.save.text : $t('Save Address'),
                        class: buttons.save.class ? buttons.save.class : 'action primary action-save-address',
                        click: function () {
                            self.updateAddress();
                            this.closeModal();
                        }
                    },
                    {
                        text: buttons.cancel.text ? buttons.cancel.text : $t('Cancel'),
                        class: buttons.cancel.class ? buttons.cancel.class : 'action secondary action-hide-popup',
                        click: function () {
                            this.closeModal();
                        }
                    }
                ];
                this.popUpForm.options.closed = function () {
                    self.isFormPopUpVisible(false);
                };
                popUp = modal(this.popUpForm.options, $(this.popUpForm.element));
            }

            return popUp;
        },

        getCountryName: function(countryId) {
            return (countryData()[countryId] != undefined) ? countryData()[countryId].name : "";
        }
    });
});
