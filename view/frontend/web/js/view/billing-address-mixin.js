define(['Magento_Checkout/js/model/quote'],
    function (quote) {
    'use strict';

        function isVirtual(quote) {
            if (window.location.hash == '#eas-billing' || !window.location.hash) {
                // render billing address edit button on those pages
                return false;
            }

            return quote.isVirtual();
        }
    var mixin = {
        defaults:{
            detailsTemplate: 'Eas_Eucompliance/billing-address/details',
        },
        isVirtual : isVirtual(quote)
        };


    return function (target) {
        return target.extend(mixin);
    }

});