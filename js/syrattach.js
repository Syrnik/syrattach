(function($) {
    $.product_syrattachments = {

        /**
         * {Number}
         */
        product_id: 0,

        /**
         * {Jquery object}
         */
        form: null,

        /**
         * Keep track changing of form
         * {String}
         */
        form_serialized_data: '',

        /**
         * {Jquery object}
         */
        container: null,

        button_color: null,
        
        tail: null,

        progressbar: {
            element : null,
            update: function(value) {
                if(this.element.prop("tagName") === 'PROGRESS') {
                    this.element.val(value); this.element.text(value.toString()+'%');
                } else {
                    $(".progressbar-inner", element).css("width", value.toString()+'%');
                }
            }
        },

        /**
         * {Object}
         */
        options: {},

        init: function(options) {
            
            $.shop.trace('$.product_syrattachments.init');
            
            this.options = options;
            this.product_id = parseInt(this.options.product_id, 10) || 0;
            
            var tab=$("#s-product-edit-menu .syrattachments");
            tab.find(".hint").text(options.count || (options.attachments && options.attachments.length) || 0);
            $("#s-product-edit-forms .s-product-form.syrattachments").addClass('ajax');
            
            this.initAttachmentsList(options);
            this.initProgressBar();
            
            $.product.editTabSyrattachmentsBlur = function(path){
                $("#s-plugin-syrattach-fileupload").fileupload('destroy');
            };
            
            $.product.editTabSyrattachmentsAction = function(path) {
                $.shop.trace('$.product_syrattachments.tail', $.product_syrattachments.tail);
                if($.product_syrattachments.tail !== null) {
                    var url = '?plugin=syrattach&module=attachments&id=' + path.id;
                    if(path.tail) {
                        url += '&param[]=' + path.tail;
                    }
                    
                    $.get(url, function(html){
                        $("#s-product-edit-forms .s-product-form.syrattachments").html(html);
                    });
                }
                $.product_syrattachments.tail = path.tail;
            };
        },
        
        initAttachmentsList: function(options) {
            this.attachments_list = $(options.attachments_list || '#s-plugin-syrattach-product-files-list');
            this.attachments_list.html(tmpl('template-syrattach-attachments', {
                attachments: options.attachments,
                formatFileSize: this._formatFileSize,
                placeholder: options.placeholder
            }));
        },
        
        initProgressBar: function() {
            // Modern browser
            console.log(this.progressbar);
            if(document.createElement('progress').max !== undefined) {
                $("#s-plugin-syrattach-upload-progress .progressbar").replaceWith('<progress>0%</progress>');
                this.progressbar.element = $("#s-plugin-syrattach-upload-progress progress");
                this.progressbar.element.attr("max", 100);
            } else {
                this.progressbar.element = $("#s-plugin-syrattach-upload-progress .progressbar");
            }
            console.log(this.progressbar);
            
            this.progressbar.update(0);
        },
        
        _formatFileSize: function (bytes) {
            
            if(typeof bytes === 'string') {
                bytes = parseInt(bytes);
            }
            
            if (typeof bytes !== 'number') {
                return '';
            }
            if (bytes >= 1000000000) {
                return (bytes / 1000000000).toFixed(2) + ' GB';
            }
            if (bytes >= 1000000) {
                return (bytes / 1000000).toFixed(2) + ' MB';
            }
            return (bytes / 1000).toFixed(2) + ' KB';
        }
    };
    
    var syrattachupload = $("#s-plugin-syrattach-fileupload");
    syrattachupload.fileupload({
        formData : $("#s-plugin-syrattach-fileupload input[type=hidden]").serializeArray(),
        start : function(e) {
            $.product_syrattachments.progressbar.update(0);
            $.product_syrattachments.progressbar.element.show();
            $.shop.trace('File upload starts');
        },
        stop : function(e) {
            $.product_syrattachments.progressbar.element.hide();
            $.shop.trace('File upload ends');
        }
    });
})(jQuery);