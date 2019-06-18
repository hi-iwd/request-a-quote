define(
    [
        'jquery',
        'jquery/ui'
    ],
    function($) {
        "use strict";

        $.widget('IWD_CartToQuote.countryRegion', {
            options : {
                regions: {},
                countrySelector : '',
                regionContainer : '',
                regionIdContainer : ''
            },

            _create: function() {
                this._bind();
            },

            _bind: function() {
                this.options.regions = iwdC2qRegions;
                this.initUpdateRegion();
            },

            initUpdateRegion: function() {
                var self = this;

                $(document).on('change', self.options.countrySelector, function () {
                    self.changeCountry($(this));
                });

                $(self.options.countrySelector).each(function () {
                    self.changeCountry($(this));
                });
            },

            changeCountry: function (countrySelect) {
                var self = this;
                var regions = self.findRegions($(countrySelect).val());

                var id = $(self.options.countrySelector).index($(countrySelect));
                var regionId = $(self.options.regionIdContainer)[id];
                var region = $(self.options.regionContainer)[id];

                if (regions.length > 1) {
                    $(regionId).show().find('select').removeAttr('disabled');
                    $(region).hide().find('input').attr('disabled', true);
                    var options = '';
                    $.each(regions, function (index, value) {
                        value.value = (value.value == null) ? '' : value.value;
                        options += '<option value="' + value.value + '">' + value.label + '</option>';
                    });
                    $(regionId).find('select').html(options);
                    $(document).trigger("iwdC2QCountryWithRegionsSelected");
                } else {
                    $(regionId).hide().find('select').attr('disabled', true);
                    $(region).show().find('input').removeAttr('disabled');
                    $(document).trigger("iwdC2QCountryWithoutRegionsSelected");
                }
            },

            findRegions: function (countryId) {
                var self = this;
                var source = self.options.regions,
                    value = countryId,
                    result;

                var field = 'country_id';

                result = _.filter(source, function (item) {
                    return item[field] === value || item.value === null || item.value === ' ' || item.value === '';
                });

                return result;
            }
        });

        return $.IWD_CartToQuote.countryRegion;
    }
);