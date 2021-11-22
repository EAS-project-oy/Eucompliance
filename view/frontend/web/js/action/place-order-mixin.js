/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
    'Magento_SalesRule/js/model/coupon',
    'Magento_Checkout/js/action/get-totals',
    'mage/url',
    'uiRegistry',
    'mage/translate'
], function (
    $,
    wrapper,
    quote,
    coupon,
    getTotalsAction,
    url,
    registry,
    $t) {
    'use strict';

    return function (placeOrderAction) {
        return wrapper.wrap(placeOrderAction, function (originalAction, paymentData, messageContainer) {
            var result;
            if (!quote.isVirtual()) {
             return originalAction(paymentData, messageContainer);
            } else {
                $.ajax({
                    type: "POST",
                    url: url.build('eas'),
                    cache: true,
                    dataType: 'json',
                    context: this,
                    data: {},
                    success: function (data) {
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else if(data.error) {
                            let messageContainer = registry.get('checkout.errors').messageContainer;
                            messageContainer.addErrorMessage({
                                message: $t(data.error)
                            })
                        }

                    }
                });
            }

        });
    };
});
