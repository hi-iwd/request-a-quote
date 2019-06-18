define(
    [
        'jquery',
        'mage/translate',
        'Magento_Customer/js/customer-data',
        'Magento_Customer/js/model/authentication-popup',
        'Magento_Customer/js/section-config',
        'Magento_Ui/js/modal/modal'
    ],
    function ($, $t, customerData, authenticationPopup, sectionConfig) {
        "use strict";

        $.widget('IWD_CartToQuote.modalForm', {
            options: {
                modalClass: '.iwd-c2q-request-modal-popup',

                modalForm: '#iwd_c2q_request_dialog',
                guestForm: '#iwd_c2q_guest_form',
                requestForm: '#iwd_c2q_request_quote_form',
                errorMessageBlock: '#iwd_c2q_error_message',
                emailInput: '#iwd_c2q_email',

                openModalButton: '.iwd-request-quote-button',

                openGuestFormButton: '.iwd_c2q_back_to_guest_form',
                openLoginFormButton: '.iwd_c2q_open_login_form',
                openRequestFormButton: '.iwd_c2q_request_as_guest',

                userInfoUrl: '#',
                checkUserEmailUrl: '#',
                userData: {},
                userDataFilled: false,
                emailExists: false,

                modalOption: {
                    type: 'popup',
                    responsive: true,
                    title: 'request a quote',
                    modalClass: 'iwd-c2q-request-modal-popup',
                    buttons: []
                }
            },

            _create: function () {
                this._bind();
            },

            _bind: function () {
                this.initOpenModalButton();
                this.initModal();
                this.initButtonActions();
                this.submitRequestForm();
                this.ajaxCompleteEvent();
                this.ajaxIwdAddProductCompleteEvent();
                this.checkEmailInput();
            },

            initOpenModalButton: function () {
                if (this.isCartEmpty() && !$(this.options.openModalButton).hasClass('btn-c2q')) {
                    $(this.options.openModalButton).hide();
                } else {
                    $(this.options.openModalButton).show();
                }
            },

            hide: function () {
                $('#top-cart-btn-checkout').show();
            },

            initModal: function () {
                var self = this;

                if (self.isUserLoggedId()) {
                    $(self.options.openGuestFormButton).remove();
                }
                $(self.options.modalForm).modal(self.options.modalOption);
            },

            initButtonActions: function () {
                var self = this;

                $(document).on('click touchstart', self.options.openModalButton, function () {
                    self.openModal();
                    self.customizeDialog();
                });

                $(document).on('click touchstart', self.options.openGuestFormButton, function () {
                    self.hideAllForms();
                    self.showGuestForm();
                    self.customizeDialog();
                });

                $(document).on('click touchstart', self.options.openLoginFormButton, function () {
                    authenticationPopup.showModal();
                });

                $(document).on('click touchstart', self.options.openRequestFormButton, function () {
                    self.hideAllForms();
                    self.showRequestForm();
                    self.customizeDialog();
                });
            },

            openModal: function () {
                this.hideAllForms();

                if (this.isUserLoggedId()) {
                    this.showRequestForm();
                    $(this.options.openGuestFormButton).remove();
                } else {
                    this.showGuestForm();
                }

                $(this.options.modalForm).trigger('openModal');
            },

            hideAllForms: function () {
                this.clearErrorMessage();

                $(this.options.guestForm).removeClass('active');
                $(this.options.requestForm).removeClass('active');
                $(this.options.modalClass).removeClass('current-form-guest current-form-login current-form-request');
            },

            showGuestForm: function () {
                $(this.options.guestForm).addClass('active');
                $(this.options.modalClass).addClass('current-form-guest');
                this.setModalHeader('Request a quote');
            },

            showRequestForm: function () {
                $(this.options.requestForm).addClass('active');
                $(this.options.modalClass).addClass('current-form-request');
                this.fillRequestForm();
            },

            fillRequestForm: function () {
                var self = this;

                if (self.isUserLoggedId() && !self.options.userDataFilled) {
                    this.setModalHeader('Request Form');
                    if (!_.isEmpty(self.options.userData)) {
                        self.fillRequestFormFields();
                    } else {
                        if (self.options.userInfoUrl) {
                            self.ajaxCall(self.options.userInfoUrl, {}, function (result) {
                                self.options.userData = (result.data && typeof (result.data) !== 'undefined') ? result.data : {};
                                self.fillRequestFormFields();
                                self.hideLoader();
                            });
                        }
                    }
                }

                if (!self.isUserLoggedId()) {
                    this.setModalHeader('Guest Request Form');
                }
            },

            fillRequestFormFields: function () {
                var self = this;
                $.each(self.options.userData, function (i, j) {
                    $(self.options.requestForm + ' [name="iwd_c2q_data[' + i + ']"]').val(j).change();
                });
                self.options.userDataFilled = true;
                $(self.options.emailInput).attr('readonly', 'readonly');
            },

            isUserLoggedId: function () {
                var customer = customerData.get('customer');
                return (typeof (customer().firstname) !== 'undefined');
            },

            isCartEmpty: function () {
                var cartData = customerData.get('cart');
                return (_.isEmpty(cartData()) || cartData().summary_count == 0);
            },

            submitRequestForm: function () {
                var self = this;
                $(document).on('submit', self.options.requestForm, function (e) {
                    e.preventDefault();
                    if (self.options.emailExists) {
                        $(self.options.emailInput).focus();
                    } else {
                        self.ajaxCall($(this).attr('action'), $(this).serializeArray(), function (result) {
                            self.hideLoader();
                            $(this.options.modalForm).trigger('closeModal');
                            if (result != null) {
                                if (typeof (result.redirect) !== 'undefined' && result.redirect) {
                                    location.href = result.redirect;
                                } else {
                                    customerData.invalidate(['customer', 'cart']);
                                    if (window.location.pathname == '/checkout/cart/' || window.location.pathname == '/iwdc2q/customer/quotes/') {
                                        location.reload();
                                        throw 'Throw exception and stop events =)';
                                    }
                                }
                            }
                        });
                    }
                    return false;
                });
            },

            ajaxCompleteEvent: function () {
                var self = this;
                $(document).on('ajaxComplete', function () {
                    self.initOpenModalButton();
                });
            },
            ajaxIwdAddProductCompleteEvent: function () {
                var self = this;
                $(document).on('ajaxIwdAddProductComplete', function (event, params) {
                    if (!params.response.iwd_quote_id) {
                        return;
                    }

                    if ($('#iwd_quote_id').length) {
                        $('#iwd_quote_id').val(params.response.iwd_quote_id);
                    }
                    else {
                        $(self.options.requestForm).append('<input type="hidden" id="iwd_quote_id" name="iwd_c2q_data[iwd_quote_id]" value="' + params.response.iwd_quote_id + '">')
                    }
                    self.openModal();
                    self.customizeDialog();
                });
            },

            checkEmailInput:

                function () {
                    var self = this;
                    $(document).on('change', this.options.emailInput, function () {
                        if (self.isUserLoggedId()) {
                            return;
                        }
                        var emailInput = $(this);
                        var email = emailInput.val().trim();

                        $(emailInput).removeClass('email-registered').closest('.field').find('.email-registered').remove();

                        if (email && self.validateEmail(email)) {
                            $(emailInput).removeClass('mage-error').closest('.field').find('.mage-error').remove();

                            self.ajaxCall(self.options.checkUserEmailUrl, {'email': email}, function (result) {
                                self.hideLoader();
                                if (result.exists) {
                                    var id = $(emailInput).attr('id');
                                    $(emailInput).after(
                                        '<div for="' + id + '" generated="true" class="email-registered" id="' + id + '-error">'
                                        + $t('Email already registered.')
                                        + ' <a href="#" class="iwd_c2q_open_login_form">Login</a> to continue.'
                                        + '</div>'
                                    ).addClass('email-registered');
                                    self.options.emailExists = true;
                                } else {
                                    self.options.emailExists = false;
                                }
                            });
                        }
                    });
                }

            ,

            validateEmail: function (email) {
                var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                return re.test(email);
            }
            ,

            ajaxCall: function (url, data, onSuccess, onComplete, onError) {
                var self = this;
                self.showLoader();
                self.clearErrorMessage();
                $.ajax({
                    url: url,
                    type: 'post',
                    dataType: 'json',
                    data: data,
                    complete: function (result) {
                        if (typeof(onComplete) === 'function') {
                            if (result.status && typeof (result.status) !== 'undefined' && result.status != null) {
                                onComplete.call(self, result);
                            } else {
                                self.hideLoader();
                                if (result.message) {
                                    self.addErrorMessage(result.message);
                                }
                            }
                        }
                    },
                    success: function (result) {
                        if (typeof(onSuccess) === 'function') {
                            onSuccess.call(self, result)
                        }
                    },
                    error: function (result) {
                        if (typeof(onError) === 'function') {
                            onError.call(self, result)
                        }
                    },
                    ajaxComplete: function () {

                    }
                });
            }
            ,

            showLoader: function () {
                $('#iwd_c2q_loader_wrapper').addClass('active');
            }
            ,

            hideLoader: function () {
                $('#iwd_c2q_loader_wrapper').removeClass('active');
            }
            ,

            customizeDialog: function () {
                $(this.options.modalClass).trigger("reinitCustomDesign");
            }
            ,

            addErrorMessage: function (message) {
                $(this.options.errorMessageBlock + ' .message-text').html(message);
                $(this.options.errorMessageBlock).show();
            }
            ,

            clearErrorMessage: function () {
                $(this.options.errorMessageBlock + ' .message-text').html('');
                $(this.options.errorMessageBlock).hide();
            }
            ,

            setModalHeader: function (header) {
                $(this.options.modalClass + ' .modal-title').html($t(header));
            }
        })
        ;

        return $.IWD_CartToQuote.modalForm;
    }
)
;
