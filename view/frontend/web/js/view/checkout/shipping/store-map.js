define([
    'ko',
    'smile-storelocator-map',
    'smile-map-markers',
    'smile-storelocator-store-collection'
], function(ko, StoreLocatorMap){

    return StoreLocatorMap.extend({

        initialize: function () {
            this._super();
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
        }
    });
});
