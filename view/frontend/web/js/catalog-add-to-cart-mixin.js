/*
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
define([
        'jquery',
        'mage/translate',
        'jquery/ui'
    ],
    function ($, $t) {
        'use strict';

        $(document).on("click", "form button[type=submit]", function (e) {
            if ($(this).hasClass('toquote')) {
                $(this).addClass('active');
            }
            else {
                $('button.toquote').removeClass('active');
            }
        });

        return function (target) {
            $.widget('mage.catalogAddToCart', target, {
                options: {
                    requestQuoteButtonSelector: '.action.toquote',
                    requestQuoteButtonTextWhileAdding: $t('Adding To Quote...'),
                    form: "#product_addtocart_form"
                },

                _create: function () {
                    this.options.requestQuoteButtonSelector = ".action.toquote";
                    this.options.requestQuoteButtonTextWhileAdding = $t('Adding To Quote...');
                    this.options.form = "#product_addtocart_form";
                    if(!event.target.id) {
                        this._bindSubmit();
                    }
                },

                _bindSubmit: function() {
                    var self = this;
                    this.element.mage('validation');
                    this.element.on('submit', function(e) {
                        e.preventDefault();
                        if(self.element.valid()) {
                            self.submitForm($(this));
                        }
                    });
                },

                /**
                 * Handler for the form 'submit' event
                 *
                 * @param {Object} form
                 */
                submitForm: function (form) {
                    var self = this,
                        requestQuoteButton = $(form).find(self.options.requestQuoteButtonSelector);

                    if (requestQuoteButton.length && requestQuoteButton.hasClass('active')) {
                        requestQuoteButton.prop('disabled', true);
                        requestQuoteButton.addClass(self.options.requestQuoteButtonTextWhileAdding);
                        self.iwdAjaxSubmit(form);
                    } else {
                        this._super(form);
                    }
                },
                iwdAjaxSubmit: function (form) {
                    var self = this;
                    var formData = form.serialize() + '&iwd_request_only=1';
                    $.ajax({
                        url: form.attr('action'),
                        data: formData,
                        type: 'post',
                        dataType: 'json',

                        success: function (response) {
                            var requestQuoteButton = $(form).find(self.options.requestQuoteButtonSelector);
                            requestQuoteButton.prop('disabled', false);
                            $(document).trigger('ajaxIwdAddProductComplete', {
                                'response': response
                            });
                        },
                        error: function (response) {
                            var requestQuoteButton = $(form).find(self.options.requestQuoteButtonSelector);
                            requestQuoteButton.prop('disabled', false);
                        }
                    });
                },

            });

            return $.mage.catalogAddToCart;
        };
    });