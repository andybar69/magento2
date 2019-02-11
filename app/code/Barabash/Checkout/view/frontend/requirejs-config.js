var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/shipping': {
                'Barabash_Checkout/js/mixin/shipping-mixin': true
            },
            'Magento_Checkout/js/action/set-shipping-information': {
                'Barabash_Checkout/js/action/set-shipping-information-mixin': true
            }
        }
    }
};