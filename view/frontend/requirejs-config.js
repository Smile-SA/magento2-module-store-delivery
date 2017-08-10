/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 *
 * @category  Smile
 * @package   Smile\StorePickup
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2017 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

var config = {
    map: {
        '*': {
            'smile-store-pickup': 'Smile_StorePickup/js/view/checkout/shipping/store-pickup',
            'smile-store-pickup-map': 'Smile_StorePickup/js/view/checkout/shipping/store-map',
            'mage-checkout-shipping-address-renderer-default': 'Magento_Checkout/js/view/shipping-address/address-renderer/default',
            'Magento_Checkout/js/view/shipping-address/address-renderer/default': 'Smile_StorePickup/js/view/shipping-address/address-renderer/default'
        }
    }
};
