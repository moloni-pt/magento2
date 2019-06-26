define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/single-checkbox',
    'Magento_Ui/js/modal/modal',
    'ko'
], function (_, uiRegistry, select, modal, ko) {
    'use strict';
    return select.extend({
        initialize: function () {
            this._super();
            this.fieldDepend(this.value());

            return this;

        },

        onUpdate: function (value) {
            this.toggleShippingLabels(value);
            return this._super();

        },

        fieldDepend: function (value) {
            var _this = this;
            setTimeout(function () {
                _this.toggleShippingLabels(value);
            });
        },

        toggleShippingLabels: function (value) {
            var shippingCountry = uiRegistry.get('index = delivery_departure_country');
            var shippingAddress = uiRegistry.get('index = delivery_departure_address');
            var shippingCity = uiRegistry.get('index = delivery_departure_city');
            var shippingZip = uiRegistry.get('index = delivery_departure_zip_code');

            if (value === '1') {
                shippingCountry.show();
                shippingAddress.show();
                shippingCity.show();
                shippingZip.show();
            } else {
                shippingCountry.hide();
                shippingAddress.hide();
                shippingCity.hide();
                shippingZip.hide();
            }
        }
    });
});
