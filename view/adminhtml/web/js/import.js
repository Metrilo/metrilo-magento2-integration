/**
 * Import orders widget
 *
 * @author Miroslav Petrov <miro91tn@gmail.com>
 */
define([
    'jquery',
    'mage/translate',
    'jquery/ui'
    ], function($, $t, $ui) {
        "use strict";
        $.widget('metrilo.import', {

            /**
             * Default options
             */
            options : {
                storeId: null,
                totalChunks: 0,
                percentage: 100,
                submitUrl: '',
                loaderImage: '', // TODO: Probably add loader image while importing
                messageSelector: ''
            },

            /**
             * Construtor method for widget
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
                self.element.on('click', function(e) {
                    // Disable the button during the import
                    $(this).addClass('disabled').attr('disabled', 'disabled').text('Importing orders');

                    if(self.options.totalChunks > 0){
                        self.options.percentage = (100 / self.options.totalChunks);
                        self.chunkSync(0);
                    }
                });
            },

            /**
             * Post chunk id to proceed orders to Metrilo
             *
             * @param  {integer} chunkId
             * @return {void}
             */
            chunkSync: function(chunkId) {
                var self = this;
                var progress = Math.round(chunkId * self.options.percentage);
                self.updateImportingMessage($t('Please wait... ' + progress + '% done'), true);

                var data = {
                    'store_id': self.options.storeId,
                    'chunk_id': chunkId,
                    'form_key': window.FORM_KEY
                };
                $.post(self.options.submitUrl, data, function(response) {
                    if (response.success) {
                        var newChunkId = chunkId + 1;
                        if(newChunkId < self.options.totalChunks) {
                            setTimeout(function() {
                                self.chunkSync(newChunkId);
                            }, 100);
                        } else {
                            self.updateImportingMessage("<span style='color: green;'>" + $t('Done! Please expect up to 30 minutes for your historical data to appear in Metrilo.') + "</span>");
                            self.element.removeClass('disabled').addClass('success').text($t('Orders imported'));
                        }
                    } else {
                        self.updateImportingMessage("<span style='color: red;'>" + response.message + "</span>");
                    }
                });
            },

            /**
             * Update progress message
             *
             * @param  {string} message
             * @return {void}
             */
            updateImportingMessage: function(message) {
                $(this.options.messageSelector).html(message);
            }

        });

        return $.metrilo.import;
    }
);
