/**
 * Copyright © EAS Project Oy. All rights reserved.
 */
var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/shipping': {
                'Eas_Eucompliance/js/view/shipping-eas-mixin': true
            },
            'Magento_Checkout/js/action/place-order': {
                'Eas_Eucompliance/js/action/place-order-mixin': true
            }
        }
    }
};
