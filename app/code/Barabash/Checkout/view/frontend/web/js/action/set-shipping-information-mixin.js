define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function ($, wrapper, quote) {
    'use strict';

    return function (setShippingInformationAction) {

        return wrapper.wrap(setShippingInformationAction, function (originalAction) {
            var shippingAddress = quote.shippingAddress();
            if (shippingAddress['extension_attributes'] === undefined) {
                shippingAddress['extension_attributes'] = {};
            }

            var flag = 0;
            var item = null;
            for (var i = 0; i < shippingAddress.customAttributes.length; i++) {
                item = shippingAddress.customAttributes[i];
                if ('comment_checkbox' === item['attribute_code'] && false === item['value']) {
                    flag = 1;
                }
            }

            for (var j = 0; j < shippingAddress.customAttributes.length; j++) {
                item = shippingAddress.customAttributes[j];
                if ('customer_comment' === item['attribute_code'] && flag === 0) {
                    shippingAddress['extension_attributes'][item['attribute_code']] = item['value'];
                } else if ('customer_comment' === item['attribute_code'] && flag > 0) {
                    shippingAddress['extension_attributes'][item['attribute_code']] = '';
                }
            }

            var billingAddress = quote.billingAddress();

            if (billingAddress !== undefined) {

                if (billingAddress['extension_attributes'] === undefined) {
                    billingAddress['extension_attributes'] = {};
                }
                billingAddress.customAttributes = shippingAddress.customAttributes;
                billingAddress['extension_attributes'] = shippingAddress['extension_attributes'];
            }

            return originalAction();
        });
    };
});