define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'jquery'
], function (Component, customerData, $) {
    'use strict';

    return Component.extend({
        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.metrilosection = customerData.get('metrilo-private-data');

            if (this.metrilosection) {
                this.metrilosection.subscribe(function () {
                    $.each(this.metrilosection().events, function(key, val){
                        eval(val);
                    });
                }, this);
            }
        }
    });
});
