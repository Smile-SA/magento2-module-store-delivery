define([], function() {
    /**
     * @param int retailerId
     * Returns new address object
     */
    return function (retailerId, addressData) {
        return {
            extension_attributes : {retailer_id: retailerId},
            countryId: addressData.country_id,
            regionId: (addressData.region && addressData.region.region_id) ? addressData.region.region_id : null,
            regionCode: (addressData.region) ? addressData.region.region_code : null,
            region: (addressData.region) ? addressData.region.region : null,
            customerId: addressData.customer_id,
            street: Array.isArray(addressData.street) ? addressData.street : [addressData.street],
            company: addressData.name ? addressData.name : (addressData.company ? addressData.company : ''),
            telephone: addressData.telephone,
            fax: addressData.fax,
            postcode: addressData.postcode,
            city: addressData.city,
            firstname: addressData.firstname,
            lastname: addressData.lastname,
            middlename: addressData.middlename,
            prefix: addressData.prefix,
            suffix: addressData.suffix,
            vatId: addressData.vat_id,

            isDefaultShipping: function() {
                return false;
            },

            isDefaultBilling: function() {
                return false;
            },

            getType: function() {
                return 'store-delivery';
            },

            getKey: function() {
                return this.getType();
            },

            getCacheKey: function() {
                return this.getKey() + '_' + this.getRetailerId();
            },

            isEditable: function() {
                return false;
            },

            canUseForBilling: function() {
                return false;
            },

            getRetailerId: function() {
                return this.extension_attributes.retailer_id;
            }
        }
    }
});
