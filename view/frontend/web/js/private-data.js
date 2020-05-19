define([
    'uiComponent',
    'Magento_Customer/js/customer-data'
], function (Component, customerData) {
    'use strict';

    return Component.extend({
        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.metrilosection = customerData.get('metrilo-private-data');

            if (this.metrilosection) {
                this.metrilosection.subscribe(function () {
                    this.metrilosection().events.forEach(function (event) {
                        eval(event);
                    });
                }, this);
            }
        }
    });
});
