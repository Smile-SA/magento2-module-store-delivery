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
        'Magento_Customer/js/model/customer',
        'mage-checkout-shipping',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/select-billing-address',
        'Magento_Checkout/js/model/address-converter'
    ],
    function (
        customer,
        Shipping,
        quote,
        selectBillingAddress,
        addressConverter
    ) {
        'use strict';

        return Shipping.extend({

            /**
             * @return {Boolean}
             */
            validateShippingInformation: function () {

                var shippingAddress = quote.shippingAddress();

                if (!quote.billingAddress() && this.isFormInline) {
                    this.setShippingAddressAsBilling();
                }

                if (shippingAddress.retailerId || shippingAddress.extension_attributes.retailer_id) {
                    return true;
                }

                return this._super();
            },

            setShippingAddressAsBilling: function() {

                var billingAddress,
                    addressData;

                if (this.isFormInline) {
                    this.source.set('params.invalid', false);
                    this.source.trigger('shippingAddress.data.validate');

                    if (this.source.get('shippingAddress.custom_attributes')) {
                        this.source.trigger('shippingAddress.custom_attributes.data.validate');
                    }

                    if (this.source.get('params.invalid')) {
                        return false;
                    }

                    billingAddress = quote.billingAddress() || {};
                    addressData = addressConverter.formAddressDataToQuoteAddress(
                        this.source.get('shippingAddress')
                    );

                    //Copy form data to quote billing address object
                    for (var field in addressData) {

                        if (addressData.hasOwnProperty(field) &&
                            billingAddress.hasOwnProperty(field) &&
                            typeof addressData[field] != 'function' &&
                            _.isEqual(billingAddress[field], addressData[field])
                        ) {
                            billingAddress[field] = addressData[field];
                        } else if (typeof addressData[field] != 'function' &&
                            !_.isEqual(billingAddress[field], addressData[field])) {
                            billingAddress = addressData;
                            break;
                        }
                    }

                    quote.billingAddress(billingAddress);
                }
            }
        })
    }
);
