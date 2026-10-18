/*
 * PayPal Braintree 4.6.1 uses Prototype's Array#each from a domReady callback
 * without declaring Prototype as a dependency. Provide the expected method
 * before domReady when Prototype has not loaded yet.
 */
(function () {
    'use strict';

    if (typeof Array.prototype.each === 'function') {
        return;
    }

    Object.defineProperty(Array.prototype, 'each', {
        configurable: true,
        enumerable: false,
        value: function (callback, context) {
            this.forEach(callback, context);

            return this;
        },
        writable: true
    });
}());
