var config = {
    map: {
        '*': {
            iwdC2QModalForm: 'IWD_CartToQuote/js/modal-form',
            iwdC2QCountryRegion: 'IWD_CartToQuote/js/country-region',
            iwdC2QDesignForm: 'IWD_CartToQuote/js/design-form'
        }
    },
    paths: {
        iwdC2QSelect2: 'IWD_CartToQuote/js/select2'
    },
    shim: {
        iwdC2QSelect2: ['jquery']
    },
    config: {
        mixins: {
            'Magento_Catalog/js/catalog-add-to-cart': {
                'IWD_CartToQuote/js/catalog-add-to-cart-mixin': true
            }
        }
    }
};
