define([
    'jquery'
], function ($) {
    'use strict';
    return function (Component) {
        return Component.extend({
            defaults:{
                hideTimeout: 15000,
            }
        });
    };

});