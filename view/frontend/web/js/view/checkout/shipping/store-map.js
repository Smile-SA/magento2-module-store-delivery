define([
    'ko',
    'smile-storelocator-map',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/model/address-list',
    'Magento_Checkout/js/view/shipping',
    'Smile_StorePickup/js/model/store-address',
    'smile-map-markers',
    'smile-storelocator-store-collection'
], function(ko, StoreLocatorMap, quote, addressList, shipping, storePickupAddress){

    return StoreLocatorMap.extend({

        initialize: function () {
            this._super();
            this.currentRetailerId = ko.observable();
            this.currentRetailerId.subscribe(this.setShippingAddress.bind(this));
        },

        /**
         * Init current position, if any.
         */
        initPosition: function () {
            var marker = this.selectedMarker();
            if (marker !== null) {
                this.currentBounds = this.initialBounds;
                this.applyPosition({coords: {latitude: marker.latitude, longitude: marker.longitude}});
            } else if(this.currentBounds) {
                this.map.fitBounds(this.currentBounds);
            } else {
                this.map.fitBounds(this.initialBounds);
            }
        },

        /**
         * Set current window location from Hash
         *
         * @param location
         */
        setHashFromLocation : function (location) {
            return false;
        },

        /**
         * Reset current window location hash
         */
        resetHash : function() {
            return false;
        },

        setShippingAddress : function() {
            var retailerData = false;
            this.markers().forEach(function(marker) {
                if (parseInt(marker.id, 10) === parseInt(this.currentRetailerId(), 10)) {
                    retailerData = marker.addressData;
                    retailerData.name = marker.name;
                }
            }.bind(this));

            var address = new storePickupAddress(this.currentRetailerId(), retailerData);

            quote.shippingAddress(address);
        }
    });
});
