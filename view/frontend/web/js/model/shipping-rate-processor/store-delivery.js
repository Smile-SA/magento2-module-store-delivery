define(
    [
        'Magento_Checkout/js/model/resource-url-manager',
        'Magento_Checkout/js/model/quote',
        'mage/storage',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/model/shipping-rate-registry',
        'Magento_Checkout/js/model/error-processor'
    ],
    function (resourceUrlManager, quote, storage, shippingService, rateRegistry, errorProcessor) {
        "use strict";
        return {
            getRates: function(address) {
                if (address.getRetailerId() !== null && address.getRetailerId() !== undefined) {
                    shippingService.isLoading(true);
                    var cache = rateRegistry.get(address.getCacheKey());

                    if (cache) {
                        shippingService.setShippingRates(cache);
                        shippingService.isLoading(false);
                    } else {
                        storage.post(
                            resourceUrlManager.getUrlForEstimationShippingMethodsForNewAddress(quote),
                            JSON.stringify({address: {
                                'street': address.street,
                                'city': address.city,
                                'region_id': address.regionId,
                                'region': address.region,
                                'country_id': address.countryId,
                                'postcode': address.postcode,
                                'email': address.email,
                                'customer_id': address.customerId,
                                'firstname': address.firstname,
                                'lastname': address.lastname,
                                'middlename': address.middlename,
                                'prefix': address.prefix,
                                'suffix': address.suffix,
                                'vat_id': address.vatId,
                                'company': address.company,
                                'telephone': address.telephone,
                                'fax': address.fax,
                                'custom_attributes': address.customAttributes,
                                'extension_attributes': address.extension_attributes
                            }}),
                            false
                        ).done(
                            function (result) {
                                rateRegistry.set(address.getCacheKey(), result);
                                shippingService.setShippingRates(result);
                            }
                        ).fail(
                            function (response) {
                                shippingService.setShippingRates([]);
                                errorProcessor.process(response);
                            }
                        ).always(
                            function () {
                                shippingService.isLoading(false);
                            }
                        );
                    }
                } else {
                    shippingService.setShippingRates([]);
                }
            }
        };
    }
);
