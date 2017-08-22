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

            var self = this;

            this._super();
            this.canRenderMap = ko.observable(false);
            this.canRenderMap.subscribe(function (value) {
                if (value) {
                    self.initMap();
                }
            });

            this.currentRetailerId = ko.observable();
            this.currentRetailerId.subscribe(this.setShippingAddress.bind(this));
        },

        /**
         * Delayed render map function : conditioned to the canRender() function : to avoid display issues if pre-drawing
         * a Google map into an hidden element.
         *
         * Also prevent little browser performance issue by defering the loading only if the popup is opened.
         *
         * @param element
         * @param component
         */
        initMap: function(element, component) {
            if (element !== undefined) {
                this.element = element;
            }
            if (this.canRenderMap()) {
                this._super(this.element, this);
            }
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

        /**
         * Set current shop as shipping address.
         */
        setShippingAddress : function() {
            var retailerData = false;
            this.markers().forEach(function(marker) {
                if (parseInt(marker.id, 10) === parseInt(this.currentRetailerId(), 10)) {
                    retailerData = marker.addressData;
                    retailerData.name = marker.name;
                    this.selectMarker(marker);
                }
            }.bind(this));

            var address = new storePickupAddress(this.currentRetailerId(), retailerData);

            quote.shippingAddress(address);
        }
    });
});
