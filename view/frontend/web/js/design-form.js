define(
    [
        'jquery',
        'iwdC2QSelect2'
    ],
    function($) {
        "use strict";

        $.widget('IWD_CartToQuote.designForm', {
            options: {
                modalClass: '.iwd-c2q-request-modal-popup',
                modalForm: '#iwd_c2q_request_dialog',
                enabledCustomDesign: true
            },

            _create: function() {
                this._bind();
            },

            _bind: function() {
                var self = this;
                self.applyCustomDesignForForm();

                $(document).on("reinitCustomDesign", function () {
                    self.customizeSelects();
                });
            },

            applyCustomDesignForForm: function () {
                if (this.options.enabledCustomDesign) {
                    this.initCustomSelects();
                    //...
                }
            },

            initCustomSelects: function () {
                var self = this;

                self.customizeSelects();

                $(document).on("iwdC2QCountryWithRegionsSelected", function () {

                });

                $(window).resize(function () {
                    self.customizeSelects();
                });
            },

            customizeSelects: function () {
                $(this.options.modalForm + ' select').each(function () {
                    $(this).select2({
                        minimumResultsForSearch: Infinity,
                        dropdownParent: $(this).closest('.field'),
                        theme: "iwd-c2q"
                    });
                });
            }
        });

        return $.IWD_CartToQuote.designForm;
    }
);
