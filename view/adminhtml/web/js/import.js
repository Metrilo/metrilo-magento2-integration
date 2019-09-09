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
                customersChunks: 0,
                categoriesChunks: 0,
                productsChunks: 0,
                ordersChunks: 0,
                importType: 'customers',
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

                    self.chunkSync(0, self.options.importType, false);

                });
            },

            /**
             * Post chunk id to proceed data to Metrilo
             *
             * @param  {integer} chunkId
             * @return {void}
             */
            chunkSync: function(chunkId, importType, retryStatus) {
                var self = this;
                var progress = Math.round(chunkId * self.options.percentage);
                self.updateImportingMessage($t('Please wait... ' + progress + '% done'), true);

                var data = {
                    'storeId': self.options.storeId,
                    'customersChunks': self.options.customersChunks,
                    'categoriesChunks': self.options.categoriesChunks,
                    'productsChunks': self.options.productsChunks,
                    'ordersChunks': self.options.ordersChunks,
                    'importType': self.options.importType,
                    'chunkId': chunkId,
                    'form_key': window.FORM_KEY
                };

                self.ajaxPostWithRetry(self.options.submitUrl, data, 3, function() {
                    var newChunkId = chunkId + 1;
                    if(retryStatus){
                        newChunkId++;
                    }
                    console.log('response.success: newChunkId = ', newChunkId, ' importType = ', importType, ' retryStatus = ', retryStatus);
                    switch (importType) {
                        case 'customers':
                            self.importType(newChunkId, 'customers', 'categories');
                            break;
                        case 'categories':
                            self.importType(newChunkId, 'categories', 'products');
                            break;
                        case 'products':
                            self.importType(newChunkId, 'products', 'deletedProducts');
                            break;
                        case 'deletedProducts':
                            self.importType(newChunkId, 'deletedProducts', 'orders');
                            break;
                        case 'orders':
                            self.importType(newChunkId, 'orders', null);
                            break;
                        default:
                            return false;
                    }
                });
            },

            importType: function(newChunkId, current, next) {
                var self = this;
                if (self.options[`${current}Chunks`] > 0) {
                    self.options.percentage = (100 / self.options[`${current}Chunks`]);
                }

                var hasMoreChunks = newChunkId < self.options[`${current}Chunks`];

                if(hasMoreChunks) {
                    self.chunkSync(newChunkId, self.options.importType, false);
                } else {
                    if(current == 'orders') {
                        self.updateImportingMessage("<span style='color: green;'>" + $t('Done! Please expect up to 30 minutes for your historical data to appear in Metrilo.') + "</span>");
                        self.element.removeClass('disabled').addClass('success').text($t('Finished Import.'));
                    } else {
                        self.element.text($t(`Importing ${next}`));
                        self.options.importType = next;
                        self.chunkSync(0, self.options.importType, false);
                    }
                }
            },

            ajaxPostWithRetry: function(url, data, retryCount, callback) {
                self = this;
                if(retryCount) {
                    $.post(url, data, function(response) {
                        callback();
                    }).fail(function () {
                        console.log('fail!', data, retryCount);
                        setTimeout(function() {
                            self.ajaxPostWithRetry(url, data, retryCount - 1 , callback);
                        }, 5000);
                    })
                } else {
                    self.chunkSync(data.chunkId + 1, data.importType, true);
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
