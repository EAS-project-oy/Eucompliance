/**
 * Copyright © EAS Project Oy. All rights reserved.
 * PHP version 8
 *
 * @category Module
 * @package  Easproject_Eucompliance
 * @author   EAS Project <magento@easproject.org>
 * @license  https://github.com/EAS-project-oy/eascompliance/ General License
 * @link     https://github.com/EAS-project-oy/eascompliance
 */
var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/shipping': {
                'Easproject_Eucompliance/js/view/shipping-eas-mixin': true
            },
            'Magento_Checkout/js/view/billing-address': {
                'Easproject_Eucompliance/js/view/billing-address-mixin': true
            },
            'Magento_Ui/js/view/messages': {
                'Easproject_Eucompliance/js/view/messages-mixin': true
            }
        }
    }
};
