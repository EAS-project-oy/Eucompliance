/**
 * Copyright © EAS Project Oy. All rights reserved.
 */
var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/shipping': {
                'Easproject_Eucompliance/js/view/shipping-eas-mixin': true
            },
            'Magento_Checkout/js/view/form/element/email': {
                'Easproject_Eucompliance/js/view/form/element/email-mixin': true
            },
            'Magento_Checkout/js/view/billing-address': {
                'Easproject_Eucompliance/js/view/billing-address-mixin': true
            },
            'Magento_Ui/js/view/messages': {
                'Easproject_Eucompliance/js/view/messages-mixin': true
            }
        }
    },
    map:
        {
            '*':
                {
                    'Magento_Checkout/js/view/form/element/email':'Easproject_Eucompliance/js/view/form/element/email'
                }
        }
};
