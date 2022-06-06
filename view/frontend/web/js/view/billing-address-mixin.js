define(['Magento_Checkout/js/model/quote'],
    function (quote) {
        'use strict';

        function isVirtual(quote) {
            if (window.location.hash == '#eas-billing' || !window.location.hash) {
                // render billing address edit button on those pages
                return false;
            }

            // Disable on payment step to avoid address manipulation
            if (window.location.hash == '#payment') {
                return true;
            }

            return quote.isVirtual();
        }

        var mixin = {
            defaults: {
                detailsTemplate: 'Easproject_Eucompliance/billing-address/details',
            },
            isVirtual: isVirtual(quote)
        };

        return function (target) {
            return target.extend(mixin);
        }

    });
