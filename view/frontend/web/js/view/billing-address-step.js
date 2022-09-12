define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'underscore',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/step-navigator',
        'mage/translate',
        'uiRegistry',
        'mage/url'
    ],
    function (
        $,
        ko,
        Component,
        _,
        quote,
        stepNavigator,
        $t,
        registry,
        url
    ) {
        'use strict';

        return Component.extend(
            {
                defaults: {
                    template: 'Easproject_Eucompliance/billing-step'
                },

                isVisible: ko.observable(quote.isVirtual()),
                isVirtual: quote.isVirtual(),

                /**
                 *
                 * @returns {*}
                 */
                initialize: function () {
                    this._super();
                    if (quote.isVirtual()) {
                        stepNavigator.registerStep(
                            'eas-billing',
                            null,
                            $t('Billing Address'),
                            this.isVisible, _.bind(this.navigate, this),
                            1
                        );
                    }

                },

                /**
                 * The navigate() method is responsible for navigation between checkout step
                 * during checkout. You can add custom logic, for example some conditions
                 * for switching to your custom step
                 */
                navigate: function () {

                },

                /**
                 * @returns void
                 */
                navigateToNextStep: function () {
                    this.disabled = true;
                    if (!quote.billingAddress()) {
                        let messageContainer = registry.get('checkout.errors').messageContainer;
                        messageContainer.addErrorMessage(
                            {
                                message: $t('Please enter billing address')
                            }
                        )
                    } else {
                        $.ajax(
                            {
                                type: "POST",
                                url: url.build('eas'),
                                cache: true,
                                dataType: 'json',
                                context: this,
                                data: {},
                                success: function (data) {
                                    if (data.redirect) {
                                        window.location.href = data.redirect;
                                    } else if (data.error) {
                                        let messageContainer = registry.get('checkout.errors').messageContainer;
                                        messageContainer.addErrorMessage(
                                            {
                                                message: $t(data.error)
                                            }
                                        )
                                    } else if (data.disabled) {
                                        $('.form-login').css("display", "none");
                                        stepNavigator.next();
                                    }
                                }
                            }
                        );
                    }
                }
            }
        );
    }
);
