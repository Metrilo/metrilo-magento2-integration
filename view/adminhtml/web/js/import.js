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
                customerChunks: 0,
                categoryChunks: 0,
                productChunks: 0,
                orderChunks: 0,
                importStatus: 'customer',
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
                    $(this).addClass('disabled').attr('disabled', 'disabled').text('Importing Customers');

                    self.chunkSync(0, self.options.importStatus, false);

                });
            },

            /**
             * Post chunk id to proceed data to Metrilo
             *
             * @param  {integer} chunkId
             * @return {void}
             */
            chunkSync: function(chunkId, importStatus, retry) {
                var self = this;
                var progress = Math.round(chunkId * self.options.percentage);
                self.updateImportingMessage($t('Please wait... ' + progress + '% done'), true);

                var data = {
                    'storeId': self.options.storeId,
                    'customerChunks': self.options.customerChunks,
                    'categoryChunks': self.options.categoryChunks,
                    'productChunks': self.options.productChunks,
                    'orderChunks': self.options.orderChunks,
                    'importStatus': self.options.importStatus,
                    'chunkId': chunkId,
                    'form_key': window.FORM_KEY
                };

                self.ajaxPostWithRetry(self.options.submitUrl, data, 3, function(response) {
                    var newChunkId = chunkId + 1;
                    if(retry){
                        newChunkId++;
                    }
                    console.log('response.success: newChunkId = ', newChunkId, ' importStatus = ', importStatus, retry);
                    switch (importStatus) {
                        case 'customer':
                            self.chunkType(newChunkId, 'customer', 'category');
                            break;
                        case 'category':
                            self.chunkType(newChunkId, 'category', 'product');
                            break;
                        case 'product':
                            self.chunkType(newChunkId, 'product', 'order');
                            break;
                        case 'order':
                            if(newChunkId < self.options.orderChunks) {
                                setTimeout(function() {
                                    self.chunkSync(newChunkId, self.options.importStatus, false);
                                }, 100);
                            } else {
                                self.updateImportingMessage("<span style='color: green;'>" + $t('Done! Please expect up to 30 minutes for your historical data to appear in Metrilo.') + "</span>");
                                self.element.removeClass('disabled').addClass('success').text($t('Finished Import.'));
                            }
                            break;
                        default:
                            return false;
                    }
                });
            },

            chunkType: function(newChunkId, current, next) {
                var self = this;
                if (self.options[`${current}Chunks`] > 0) {
                    self.options.percentage = (100 / self.options[`${current}Chunks`]);
                }

                if(newChunkId < self.options[`${current}Chunks`]) {
                    setTimeout(function() {
                        self.chunkSync(newChunkId, self.options.importStatus, false);
                    }, 100);
                } else {
                    self.element.text($t(`Importing ${next}`));
                    self.updateImportingMessage("<span style='color: orange;'>" + $t(`${current} import is done! Commencing ${next} import.`) + "</span>");
                    self.options.importStatus = next;
                    setTimeout(function() {
                        self.chunkSync(0, self.options.importStatus, false);
                    }, 2000);
                }
            },

            ajaxPostWithRetry: function(url, data, retryCount, callback) {
                self = this;
                if(retryCount) {
                    $.post(url, data, function(response) {
                        callback(response);
                    }).fail(function () {
                        console.log('fail!', data, retryCount);
                        setTimeout(function() {
                            self.ajaxPostWithRetry(url, data, retryCount - 1 , callback);
                        }, 5000);
                    })
                } else {
                    self.chunkSync(data.chunkId + 1, data.importStatus, true);
                }
            },

            /**
             * Update progress message
             *
             * @param  {string} message
             * @return {void}
             */
            updateImportingMessage: function(message) {
                $(this.options.messageSelector).html(message);
            },

        });

        return $.metrilo.import;
    }
);
