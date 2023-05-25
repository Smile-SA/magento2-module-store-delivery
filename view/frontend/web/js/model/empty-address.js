define([], function() {

    /**
     * Returns new address object
     */
    return function () {
        var identifier = Date.now();
        return {
            isDefaultShipping: function () {
                return false;
            },
            isDefaultBilling: function () {
                return false;
            },
            getType: function () {
                return 'new-customer-address';
            },
            getKey: function () {
                return this.getType();
            },
            getCacheKey: function () {
                return this.getType() + identifier;
            },
            isEditable: function () {
                return true;
            },
            canUseForBilling: function () {
                return true;
            }
        }
    }
});
