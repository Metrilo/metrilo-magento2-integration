/**
 * Import orders widget
 */
define([
    'jquery',
    'mage/translate'
    ], function($, $t) {
        "use strict";
        $.widget('metrilo.import', {

            /**
             * Default options
             */
            options : {
                storeId: '',
                totalChunks: ''
            },

            /**
             * construtor method for widget
             *
             * @return {this}
             */
            _create: function() {
                this._bindSubmit();
                return this;
            },

            /**
             * Prevent default form submition
             *
             * @return {void}
             */
            _bindSubmit: function() {
                var self = this;
                this.element.on('click', function(e) {
                    var percentage = 100;
                    if(self.options.totalChunks > 0){
                        percentage = (100 / self.options.totalChunks);
                    }
                });
            }

        });

        return $.metrilo.import;
    }
);
