define(
    [
    'uiComponent',
    'Magento_Checkout/js/model/quote'
    ], function (Component, quote) {
        'use strict';

        function getTemplate()
        {
            if (quote.isVirtual()) {
                if (window.location.hash == '#eas-billing' || !window.location.hash) {
                    return 'Magento_Checkout/form/element/email';
                }
            } else if (window.location.hash == '#payment') {
                return 'Magento_Checkout/form/element/email';
            }
            return 'Easproject_Eucompliance/empty-email';
        }

        return function (Component) {
            return Component.extend(
                {
                    defaults:{
                        template: getTemplate()
                    },
                }
            );
        }
    }
);
