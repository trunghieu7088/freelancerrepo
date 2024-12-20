// declare everything inside this object
window.AE = window.AE || {};
window.Filter_Args = {};
(function(AE, $, Backbone) {
    AE.Models = AE.Models || {};
    AE.Collections = AE.Collections || {};
    AE.Views = AE.Views || {};
    AE.Routers = AE.Routers || {};
    // the pub/sub object for managing event throughout the app
    AE.pubsub = AE.pubsub || {};
    _.extend(AE.pubsub, Backbone.Events);
    AE.globals = ae_globals;
    /**
     * override backbone sync function
     */
    Backbone.Model.prototype.sync = function(method, model, options) {
        var data = model.attributes;
        data.action = model.action || 'ae-sync';
        switch (method) {
            case 'create':
                data.method = 'create';
                break;
            case 'update':
                data.method = 'update';
                break;
            case 'delete':
                data.method = 'remove';
                break;
            case 'read':
                data.method = 'read';
                break;
        }
        var ajaxParams = {
            type: 'POST',
            dataType: 'json',
            data: data,
            url: ae_globals.ajaxURL,
            contentType: 'application/x-www-form-urlencoded;charset=UTF-8'
        };
        ajaxParams = _.extend(ajaxParams, options);
        if (options.beforeSend !== 'undefined') ajaxParams.beforeSend = options.beforeSend;
        ajaxParams.success = function(result, status, jqXHR) {
            AE.pubsub.trigger('ae:success', result, status, jqXHR);
            options.success(result, status, jqXHR);
        };
        ajaxParams.error = function(jqXHR, status, errorThrown) {
            AE.pubsub.trigger('ae:error', jqXHR, status, errorThrown);
            options.error(jqXHR, status, errorThrown);
        };
        $.ajax(ajaxParams);
    };
    /**
     * override backbone collection sync
     */
    Backbone.Collection.prototype.sync = function(method, collection, options) {
        var ajaxParams = {
            type: 'POST',
            dataType: 'json',
            data: {},
            url: ae_globals.ajaxURL,
            contentType: 'application/x-www-form-urlencoded;charset=UTF-8'
        };
        ajaxParams.data = _.extend(ajaxParams.data, options.data);
        if (typeof collection.action !== 'undefined') {
            ajaxParams.data.action = collection.action;
        }
        /**
         * add beforsend function
         */
        if (options.beforeSend !== 'undefined') ajaxParams.beforeSend = options.beforeSend;
        /**
         * success function
         */
        ajaxParams.success = function(result, status, jqXHR) {
            AE.pubsub.trigger('ae:success', result, status, jqXHR);
            options.success(result, status, jqXHR);
        };
        ajaxParams.error = function(jqXHR, status, errorThrown) {
            AE.pubsub.trigger('ae:error', jqXHR, status, errorThrown);
            options.error(jqXHR, status, errorThrown);
        };
        $.ajax(ajaxParams);
    };
    /**
     * override backbone model parse function
     */
    Backbone.Model.prototype.parse = function(result) {
        if (_.isObject(result.data)) {
            return result.data;
        } else {
            return result;
        }
    };
    /**
     * override backbone model parse function
     */
    Backbone.Collection.prototype.parse = function(result) {
        if (_.isObject(result) && _.isObject(result.data)) {
            return result.data;
        } else {
            return [];
        }
    };
    // create a shorthand for our pubsub
})(window.AE, jQuery, Backbone);

// override underscore template tag
_.templateSettings = {
    evaluate: /\<\#(.+?)\#\>/g,
    interpolate: /\{\{=(.+?)\}\}/g,
    escape: /\{\{-(.+?)\}\}/g
};

// build basic view
(function(AE, $, Backbone, Views, Models, Collections) {
    // create a shorthand for the params used in most ajax request
    AE.ajaxParams = {
        type: 'POST',
        dataType: 'json',
        url: AE.globals.ajaxURL,
        contentType: 'application/x-www-form-urlencoded;charset=UTF-8'
    };
    var ajaxParams = AE.ajaxParams;
    /**
     * loading effec view
     */
    AE.Views.LoadingEffect = Backbone.View.extend({
        initialize: function() {},
        render: function() {
            this.$el.html(AE.globals.loadingImg);
            return this;
        },
        finish: function() {
            this.$el.html(AE.globals.loadingFinish);
            var view = this;
            setTimeout(function() {
                view.$el.fadeOut(500, function() {
                    $(this).remove();
                });
            }, 1000);
        },
        remove: function() {
            view.$el.remove();
        }
    });
    /**
     * blockui view
     * block an Dom Element with loading image
     */
    AE.Views.BlockUi = Backbone.View.extend({
        defaults: {
            image: AE.globals.imgURL + '/loading.gif',
            opacity: '0.5',
            background_position: 'center center',
            background_color: '#ffffff'
        },
        isLoading: false,
        initialize: function(options) {
            //var defaults = _.clone(this.defaults);
            options = _.extend(_.clone(this.defaults), options);
            var loadingImg = options.image;
            this.overlay = $('<div class="loading-blur loading"><div class="loading-overlay"></div><div class="loading-img"></div></div>');
            this.overlay.find('.loading-img').css({
                'background-image': 'url(' + options.image + ')',
                'background-position': options.background_position
            });
            this.overlay.find('.loading-overlay').css({
                'opacity': options.opacity,
                'filter': 'alpha(opacity=' + options.opacity * 100 + ')',
                'background-color': options.background_color
            });
            this.$el.html(this.overlay);
            this.isLoading = false;
        },
        render: function() {
            this.$el.html(this.overlay);
            return this;
        },
        block: function(element, caption) {
            var $ele = $(element);
            // if ( $ele.css('position') !== 'absolute' || $ele.css('position') !== 'relative'){
            //         $ele.css('position', 'relative');
            // }
            this.overlay.css({
                'position': 'absolute',
                'z-index': 999999999,
                'top': $ele.offset().top,
                'left': $ele.offset().left,
                'width': $ele.outerWidth(),
                'height': $ele.outerHeight()
            });
            this.isLoading = true;
            this.render().$el.show().appendTo($('body'));
            if(caption){
                this.$el.find('.loading-img' ).text(caption);
            }
        },
	    setMessage:function(message){
			if(this.$el){
				this.$el.find('.loading-img' ).text(message);
			}
	    },
        unblock: function() {
             this.$el.remove();
             this.isLoading = false;
        },
        finish: function() {
            this.$el.fadeOut(500, function() {
                $(this).remove();
            });
            this.isLoading = false;
        }
    });
    AE.Views.LoadingButton = Backbone.View.extend({
        dotCount: 3,
        isLoading: false,
        initialize: function() {
            if (this.$el.length <= 0) return false;
            var dom = this.$el[0];
            //if ( this.$el[0].tagName != 'BUTTON' && (this.$el[0].tagName != 'INPUT') ) return false;
            if (this.$el[0].tagName == 'INPUT') {
                this.title = this.$el.val();
            } else {
                this.title = this.$el.html();
            }
            this.isLoading = false;
        },
        loopFunc: function(view) {
            var dots = '';
            for (i = 0; i < view.dotCount; i++) dots = dots + '.';
            view.dotCount = (view.dotCount + 1) % 3;
            view.setTitle(AE.globals.loading + dots);
        },
        setTitle: function(title) {
            if (this.$el[0].tagName === 'INPUT') {
                this.$el.val(title);
            } else {
                this.$el.html(title);
            }
        },
        loading: function() {
            //if ( this.$el[0].tagName != 'BUTTON' && this.$el[0].tagName != 'A' && (this.$el[0].tagName != 'INPUT') ) return false;
            this.setTitle(AE.globals.loading);
            this.$el.addClass('disabled');
            var view = this;
            view.isLoading = true;
            view.dots = '...';
            view.setTitle(AE.globals.loading + view.dots);
            this.loop = setInterval(function() {
                if (view.dots === '...') view.dots = '';
                else if (view.dots === '..') view.dots = '...';
                else if (view.dots === '.') view.dots = '..';
                else view.dots = '.';
                view.setTitle(AE.globals.loading + view.dots);
            }, 500);
        },
        finish: function() {
            var dom = this.$el[0];
            this.isLoading = false;
            clearInterval(this.loop);
            this.setTitle(this.title);
            this.$el.removeClass('disabled');
        }
    });
    // View: Modal Box
    AE.Views.Modal_Box = Backbone.View.extend({
        defaults: {
            top: 100,
            overlay: 0.5
        },
        $overlay: null,
        initialize: function() {
            // bind all functions of this object to itself
            //_.bindAll(this.openModal);
            // update custom options if having any
            this.options = $.extend(this.defaults, this.options);
        },
        /**
         * open modal
         */
        openModal: function() {
            var view = this;
            this.$el.modal('show');
        },
        /**
         * close modal
         */
        closeModal: function(time, callback) {
            var modal = this;
            modal.$el.modal('hide');
            return false;
        },
        /**
         * add block ui, block loading
         */
        loading: function() {
            if (typeof this.blockUi === 'undefined') {
                this.blockUi = new AE.Views.BlockUi();
            }
            this.blockUi.block(this.$el.find('input[type="submit"]'));
        },
        /**
         * finish ajax
         */
        finish: function() {
            this.blockUi.unblock();
        },
        // trigger pubsub error
        error: function(res) {
            AE.pubsub.trigger('ae:notification', {
                msg: res.msg,
                notice_type: 'error',
            });
        },
        // trigger pubsub notification success
        success: function(res) {
            AE.pubsub.trigger('ae:notification', {
                msg: res.msg,
                notice_type: 'success',
            });
        }
    });
    plupload.addFileFilter('resolution_limit', function(limit, file, cb) {
        var self = this;
        var reader = new FileReader();
        var image  = new Image();
        var blodFile = file.getNative();
        reader.readAsDataURL(blodFile);
        reader.onload = function(_file) {
            image.src    = _file.target.result;
            image.onload = function() {
                var width = this.width;
                var height = this.height;
                var isValid = true;
                var errorMesage = '';
                if(typeof limit.min != 'undefined'){
                    if(typeof limit.min.width != 'undefined'){
                        if(width < limit.min.width){
                            isValid = false;
                            errorMesage = plupload.translate('The width of the image must be greater than ') + limit.min.width +'px';
                        }
                    }
                    if(typeof limit.min.height != 'undefined'){
                        if(height < limit.min.height){
                            isValid = false;
                            errorMesage = plupload.translate('The height of the image must be greater than ') + limit.min.height +'px';
                        }
                    }
                }

                if(typeof  limit.max != 'undefined'){
                    if(typeof limit.max.width != 'undefined'){
                        if(width > limit.max.width){
                            isValid = false;
                            errorMesage = plupload.translate('The height of the image must be less than ') + limit.max.width +'px';
                        }
                    }
                    if(typeof limit.max.height != 'undefined'){
                        if(height > limit.max.height){
                            isValid = false;
                            errorMesage = plupload.translate('The height of the image must be less than ') + limit.max.height +'px';
                        }
                    }
                }

                if (!isValid) {
                    self.trigger('Error', {
                        code : plupload.IMAGE_DIMENSIONS_ERROR,
                        message : errorMesage,
                        file : file
                    });

                }
                cb(isValid);
            };

            image.onerror= function(e) {
                self.trigger('Error', {
                    code : plupload.IMAGE_FORMAT_ERROR,
                    message : plupload.translate('Image format error.'),
                    file : file
                });
                cb(false);
            };
        };
    });
    /*
    /*AE File uploader
    */
    AE.Views.File_Uploader = Backbone.View.extend({
        //options            : [],
        events: {
            'click .btn-remove-option-image': 'removeOptionImage'
        },
        initialize: function(options) {
            _.bindAll(this, 'onFileUploaded', 'onFileAdded', 'onFilesBeforeSend', 'onUploadComplete', 'onUploadErrorResponse');
            this.options = options;
            this.uploaderID = (this.options.uploaderID) ? this.options.uploaderID : 'et_uploader';
	        this.processEl = $("#" + this.uploaderID + '_progress');
            this.config = {
                runtimes: 'html5,gears,flash,silverlight,browserplus,html4',
                multiple_queues: true,
                multipart: true,
                urlstream_upload: true,
                multi_selection: false,
                upload_later: false,
                container: this.uploaderID + '_container',
	            drop_element : this.uploaderID + '_container',
                browse_button: this.uploaderID + '_browse_button',
                remove_button: this.uploaderID + '_remove_button',
                thumbnail: this.uploaderID + '_thumbnail',
                thumbsize: 'thumbnail',
                file_data_name: this.uploaderID,
                max_file_size: '1mb',
                //chunk_size                         : '1mb',
                // this filters is an array so if we declare it when init Uploader View, this filters will be replaced instead of extend
                filters: {
                    mime_types: [{
                        title: 'Image Files',
                        extensions: (this.options.extensions) ? this.options.extensions : 'pdf,jpg,jpeg,gif,png,ico'
                    }]
                },
                multipart_params: {
                    fileID: this.uploaderID,
                    action: 'et-upload-image'
                },
	            onProgress: function(up, file) {
		            if(this.processEl)
		            {
			            this.processEl.text(file.percent + "%");
		            }
                       //custom code seynou
                   
                       $(".custom-progress-bar").css('display','block');
                       $(".custom-progress-bar").css('width',file.percent + '%');
                       $(".custom-progress-bar").html(file.percent + '%');
                       //end custom
	            },
                // prevent_duplicates: true,
                Error: function(up, error) {
                    alert(error.message);
                }
            };

            if(typeof this.options.filters !='undefined'){
                jQuery.extend(true, this.options.filters, this.config.filters);
            }
            jQuery.extend(true, this.config, AE.globals.plupload_config, this.options);
            this.controller = new plupload.Uploader(this.config);
            this.controller.init();
            this.controller.bind('FileUploaded', this.onFileUploaded);
            this.controller.bind('FilesAdded', this.onFileAdded);
            this.controller.bind('BeforeUpload', this.onFilesBeforeSend);
            this.bind('UploadSuccessfully', this.onUploadComplete);
            this.bind('UploadErrorResponse', this.onUploadErrorResponse);
            if (typeof this.controller.settings.onProgress === 'function') {
                this.controller.bind('UploadProgress', this.controller.settings.onProgress);
            }
            if (typeof this.controller.settings.onError === 'function') {
                this.controller.bind('Error', this.controller.settings.onError);
            } else {
                this.controller.bind('Error', this.errorLog);
            }
            if (typeof this.controller.settings.cbRemoved === 'function') {
                this.controller.bind('FilesRemoved', this.controller.settings.cbRemoved);
            }
             this.blockUi = new AE.Views.BlockUi();
             if( this.$el.find('#'+ this.config.remove_button).length > 0 && this.$el.find('#'+ this.config.remove_button).attr('data-id') != ''){
                 this.$el.find('#'+ this.config.remove_button).show();
            }
        },
	    getControler: function(){
		    return this.controller;
	    },
        errorLog: function(e, b) {
            AE.pubsub.trigger('ae:notification', {
                notice_type: 'error',
                msg: b.message
            });
        },
        onFileAdded: function(up, files) {
            if (typeof this.controller.settings.cbAdded === 'function') {
                this.controller.settings.cbAdded(up, files);
            }
            if (!this.controller.settings.upload_later) {
                up.refresh();
                up.start();
            }
        },
        onFileUploaded: function(up, file, res) {
            res = JSON.parse(res.response);
            if (typeof this.controller.settings.cbUploaded === 'function') {
                this.controller.settings.cbUploaded(up, file, res);
            }
            if (res.success) {
                this.updateThumbnail(res.data);
                this.trigger('UploadSuccessfully', res);
                AE.pubsub.trigger('Upload:Success', res);
            }
            else{
                this.trigger('UploadErrorResponse', res);
                AE.pubsub.trigger('Upload:Error', res);
            }
        },
        updateThumbnail: function(res) {
            var that = this,
                $thumb_div = this.$('#' + this.controller.settings['thumbnail']),
                $existing_imgs, thumbsize;
            if ($thumb_div.length > 0) {
                $existing_imgs = $thumb_div.find('img'),
                thumbsize = (res.file_id == "default_mjob")?'mjob_detail_slider':this.controller.settings['thumbsize'];
                if ($existing_imgs.length > 0) {
                    $existing_imgs.fadeOut(100, function() {
                        $existing_imgs.remove();
                        if (_.isArray(res[thumbsize])) {
                            that.insertThumb(res[thumbsize][0], $thumb_div);
                        }
                    });
                } else if (_.isArray(res[thumbsize])) {
                    this.insertThumb(res[thumbsize][0], $thumb_div);
                }
            }
        },
        insertThumb: function(src, target) {
            $('<img>').attr({
                'id': this.uploaderID + '_thumb',
                'src': src
            })
            // .hide()
            .appendTo(target).fadeIn(300);
        },
        updateConfig: function(options) {
            if ('updateThumbnail' in options && 'data' in options) {
                this.updateThumbnail(options.data);
            }
            $.extend(true, this.controller.settings, options);
            this.controller.refresh();
        },
        onFilesBeforeSend: function() {
            if ('beforeSend' in this.options && typeof this.options.beforeSend === 'function') {
                this.options.beforeSend(this.$el);
            }
            //custom code seynou
            $(".custom-overlay").css('display','block');
            //end
        },
        onUploadComplete: function(res) {
            var view = this;
            if ('success' in this.options && typeof this.options.success === 'function') {
                this.options.success(res);
                $('#'+ this.config.remove_button).attr('data-id', res.data.attach_id);
                $('#'+ this.config.remove_button).show();
            }
              //custom code seynou
              $(".custom-overlay").css('display','none');
              //end
        },
        onUploadErrorResponse:function(res){
            if ('errorResponsed' in this.options && typeof this.options.errorResponsed === 'function') {
                this.options.errorResponsed(res);
            }
            else{
                if(res.hasOwnProperty('msg')){
                    alert(res.msg);
                }
            }
        },
        removeOptionImage: function(e){
            var view = this;
            e.preventDefault();
            $target = $(e.currentTarget);
            var param = {
                url: ae_globals.ajaxURL,
                type: 'POST',
                data: {
                    ID: $target.attr('data-id'),
                    name: $target.attr('data-name'),
                    action: 'ae-remove-option-image',
                },
                beforeSend: function() {
                    view.blockUi.block( $target );
                },
                success: function( resp ) {
                    if( resp.success ) {
                        view.$el.find('.image img').attr('src','');
                        AE.pubsub.trigger('ae:notification', {
                            msg: resp.msg,
                            notice_type: 'success'
                        });
                        $target.hide();
                    }
                },
                complete: function() {
                    view.blockUi.unblock();
                }
            };
            $.ajax(param);
        }
    });
    /**
     * USER VIEW
     */
    /**
     * User item
     */
    AE.Views.UserItem = Backbone.View.extend({
        tagName: 'li',
        className: 'et-member',
        template: '',
        /**
         * this view content model user
         */
        model: [],
        /**
         * initialize view
         */
        events: {
            /**
             * trigger action on model, link should contain attribute data-name and data-value
             * name value pair for model example model.set(a.attr('data-name') , a.attr('data-value')) then a.save();
             */
            'click a.action': 'acting',
            /**
             * input regular change update model
             */
            'change .regular-input': 'change',
            /**
             * ban & unbar a user
             */
            'click .et-act-ban': 'renderBanForm',
            'click .et-act-unban': 'unbanUser',
            /**
             * change user role, this option should be use in admin setting
             */
            // 'change select.role-change' : 'changeRole'
        },
        /**
         * initialize view
         */
        initialize: function() {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);
            /**
             * can override template by change template content, but should keep the template id
             */
            if ($('#user-item-template').length > 0) {
                this.template = _.template($('#user-item-template').html());
            }
            this.blockUi = new AE.Views.BlockUi();
        },
        /**
         * render view fill template with model data
         */
        render: function() {
            if (this.template) {
                this.$el.html(this.template(this.model.toJSON()));
            }
            return this;
        },
        /**
         * action on model
         */
        acting: function(e) {
            e.preventDefault();
            var target = $(e.currentTarget),
                action = target.attr('data-act'),
                view = this;
            if (action == "confirm") {
                if( ! confirm(ae_globals.confirm_user)) return false;
                this.model.save('register_status', '', {
                    beforeSend: function() {
                        view.blockUi.block(view.$el);
                    },
                    success: function(result, status, xhr) {
                        view.blockUi.unblock();
                        view.$el.find('a.et-act-confirm').fadeOut();
                    }
                });
            }
        },
        /**
         * update user role
         */
        change: function(e) {
            var $target = $(e.currentTarget);
            name = $target.attr('name'),
            val = $target.val(),
            view = this;
            this.model.save(name, val, {
                beforeSend: function() {
                    view.blockUi.block(view.$el);
                },
                success: function(result, status, xhr) {
                    view.blockUi.unblock();
                }
            });
        },
        /**
         * render ban modal
         */
        renderBanForm: function(e) {
            var form = $('#form_ban_user');
            $('#ban_modal .modal-header .display-name').text(this.model.get('display_name'));
            $('#form_ban_user input[name=id]').val( this.model.get('id') );
        },

        /**
         * unban a user
         */
        unbanUser: function(e) {
            var view = this;

            var params = {
                url: ae_globals.ajaxURL,
                type: 'POST',
                data: {
                    ID: this.model.get('id'),
                    action: 'ae-sync-user',
                    method: 'update',
                    do: 'unban',
                },
                beforeSend: function() {
                    view.blockUi.block( view.$el );
                },
                success: function( resp ) {
                    if( resp.success ) {
                        // Reset model
                        view.model.set( resp.data );
                        // Re-render model
                        view.render();
                    }
                },
                complete: function() {
                    view.blockUi.unblock();
                }
            };

            $.ajax( params );

            return false;
        }
    });
    /**
     * view of users list
     */
    AE.Views.ListUsers = Backbone.View.extend({});
    // USER VIEW
    /**
     * POST VIEW
     */
    /**
     * view of post item
     */
    // AE.Views.PostItem = Backbone.Marionette.ItemView.extend({});
    /**
     * post item extend Marionette item view
     */
    Views.PostItem = Backbone.Marionette.ItemView.extend({
        // view html tag
        tagName: "li",
        // view class
        className: 'col-md-3 col-xs-6 place-item ae-item',
        /**
         * view events
         */
        events: {
            // user click on action button such as edit, archive, reject
            'click .action': 'acting'
        },
        /**
         * list all model events
         */
        modelEvents: {
            "change": "modelChanged",
            "change:post_status": "statusChange"
        },
        /**
         * model in view change callback function
         * update model data to database
         */
        modelChanged: function(model) {
            this.render();
        },
        statusChange: function(model) {
            AE.pubsub.trigger('ae:model:statuschange', model);
        },

        /**
         * event callback when user click on action button
         * edit
         * archive
         * reject
         * toggleFeatured
         * approve
         */
        acting: function(e) {
            e.preventDefault();
            var target = $(e.currentTarget),
                action = target.attr('data-action'),
                view = this;
            switch (action) {
                case 'edit':
                    //trigger an event will be catch by AE.App to open modal edit
                    AE.pubsub.trigger('ae:model:onEdit', this.model);
                    break;
                case 'reject':
                    //trigger an event will be catch by AE.App to open modal reject
                    AE.pubsub.trigger('ae:model:onReject', this.model);
                    break;
                case 'archive':
                    if (confirm(ae_globals.confirm_message)) {
                        // archive a model
                        this.model.set('archive', 1);
                        this.model.save('archive', '1', {
                            beforeSend: function() {
                                view.blockItem();
                            },
                            success: function(result, res, xhr) {
                                AE.pubsub.trigger('ae:post:archiveSuccess', result, res, xhr);
                                view.unblockItem();
                            }
                        });
                    }
                    break;
                case 'toggleFeature':
                    // toggle featured
                    this.model.save('et_featured', 1);
                    break;
                case 'approve':
                    if( ! confirm(ae_globals.alert_confirm_approve)) return false;
                    this.model.save('publish', '1', {
                        beforeSend: function() {
                            view.blockItem();
                        },
                        success: function(result, res, xhr) {
                            view.triggerMethod("before:approve", view, res);
                            view.unblockItem();
                        }
                    });
                    break;
                case 'delete':
                    if (confirm(ae_globals.confirm_delete_message)) {
                        // archive a model
                        this.model.save('delete', '1', {
                            beforeSend: function() {
                                view.blockItem();
                            },
                            success: function(result, res, xhr) {
                                view.unblockItem();
                                if(res.success){
                                    AE.pubsub.trigger("ae:delete:success", result, res, xhr);
                                    view.model.destroy();
                                }
                            }
                        });
                    }
                    break;
                default:
                    //trigger an event will be catch by AE.App to open modal edit
                    AE.pubsub.trigger('ae:model:on' + action, this.model, target);
                    break;
            }
        },
        /**
         * load block item
         */
        blockItem: function() {
            if (typeof this.blockUi === 'undefined') {
                this.blockUi = new Views.BlockUi();
            }
            this.blockUi.block(this.$el);
        },
        /**
         * unblock loading
         */
        unblockItem: function() {
            this.blockUi.unblock();
        }
    });
    /**
     * view of posts list
     */
    Views.ListPost = Backbone.Marionette.CollectionView.extend({
        // tagName: 'ul',
        // itemView: PostItem,
        // itemClass: 'li'
        constructor: function(options) {
            var view = this;
            Marionette.CollectionView.prototype.constructor.apply(this, arguments);
            if (typeof this.collection !== 'undefined') {
                this.collection.each(function(pack, index, col) {
                    var el = view.$('.' + view.itemClass).eq(index);
                    itemView = view.getItemView(pack);
                    // this view is about to be added
                    view.triggerMethod("before:item:added", view);
                    view.children.add(new itemView({
                        el: el,
                        model: pack
                    }));
                    // this view was added
                    view.triggerMethod("after:item:added", view);
                });
            }
        }
    });
    // view control composite listviewitem
    Views.Index = Backbone.View.extend({
        initialize: function(options) {
            // bind event to view
            _.bindAll(this, 'onModelChange');
            var view = this;
            // view collections list
            view.collections = {};
            // list of listViewItem
            view.list = {};
            this.options = _.extend(this, options);
            /**
             * init list view control
             */
            // list of collection associate with list container data list
            if (this.pending.length > 0) {
                view.list['pending'] = new ListView({
                    itemView: PostItem,
                    collection: this.pending,
                    el: '#pending-places',
                    thumb: $('#pending-places').attr('data-thumb')
                });
            }
            if (this.publish.length > 0) {
                view.list['publish'] = new ListView({
                    itemView: PostItem,
                    collection: this.publish,
                    el: '#publish-places',
                    thumb: $('#publish-places').attr('data-thumb')
                });
            }
            // catch event when a model change status
            AE.pubsub.on('ae:model:statuschange', view.onModelChange, this);
        },
        /**
         * this function trigger when a model change status
         * call this function to add model to the list associate with its status
         */
        onModelChange: function(model) {
            var status = model.get('post_status'),
                view = this;
            // remove model from pending collection
            if (status == 'publish' || status == 'reject' || status == 'trash') {
                if (typeof view.pending !== 'undefined') {
                    view.pending.remove(model);
                    view.publish.add(model);
                }
            }
            // remove model from publish collection
            if (status == 'archive') {
                if (typeof view.publish !== 'undefined') {
                    view.publish.remove(model);
                }
            }
        }
    });
    /*
     *
     * S K I L L  I T E M  V I E W S
     *
     */
    Views.Skill_Item = Backbone.View.extend({
        'tagName': 'li',
        'className': 'skill-item',
        events: {
            'click a.delete': 'deleteItem'
        },
        //template  : _.template( $('#tag_item').html() ),
        initialize: function() {
            /* get skill item template */
            if ($('#skill_item').length > 0) {
                this.template = _.template($('#skill_item').html());
            } else {
                alert('Hi dev, you forgot add item view skill_item');
            }
            this.model.on('remove', this.removeView, this);
            this.model.on('destroy', this.removeView, this);
        },
        render: function() {
            this.$el.html(this.template(this.model.toJSON()));
            return this;
        },
        removeView: function() {
            this.$el.fadeOut('normal', function() {
                $(this).remove();
            });
        },
        deleteItem: function(event) {
            event.preventDefault();
            this.model.destroy();
        }
    });
    /*
     *
     * S K I L L S  C O N T R O L  V I E W S
     *
     */
    Views.Skill_Control = Backbone.View.extend({
        // el: '.skill-control',
        events: {
            'keydown input.skill': 'onAddSkill', // replace of'keypress input.skill': 'onAddSkill', ver 1.3.9.8
            'click .add-skill': 'onClickAddSkill', // v1.3.9.8
            'keydown textarea.skill': 'onAddSkillMobile', // v1.3.9.8

        },
        initialize: function(options) {
            _.bindAll(this, 'addOne', 'removeOne');
            var view = this;
            view.maxSkill = 0;
            this.options = _.extend(this, options);
            if(typeof this.options.name === 'undefined') {
                this.options.name = 'skill';
            }

            // v1.3.9.8 add
            $(document).on('input', 'textarea.skill' , function(event){
                var val = $(event.currentTarget).val();
                if( val !== '' ){
                    $(".add-skill").addClass('active');
                } else {
                    $(".add-skill").removeClass('active');
                }
            });
            // v1.3.9.8 end.


            /**
             * skills list container
             */
            if (typeof this.collection === "undefined") {
                this.collection = new Collections.Skills();
            }
            // this.options = _.extend(this, options);
            this.skill_list = this.$('ul.skills-list');
            view.skills = {};
            this.$el.find('.et-suggest-skill').typeahead({
                minLength: 0,
                items: 99,
                source: function(query, process) {
                    if (view.skills.length > 0) return view.skills;
                    return $.getJSON(ae_globals.ajaxURL, {
                        action: 'fre_get_skills',
                        query: query
                    }, function(data) {
                        view.skills = data;
                        return process(data);
                    });
                },
                updater: function(item) {
                    var filter = $('#filter_skill').val();
                    view.addSkill(item);
                }
            });
            this.collection.on('add', this.addOne, this);
            this.collection.on('remove', this.removeOne, this);
            if (typeof this.model !== 'undefined') {
                this.render();
            }
        },
        // set a model to view
        setModel: function(model) {
            this.model = model;
            this.render();
        },
        render: function() {
            var tax_input = this.model.get('tax_input'),
                skills = (typeof tax_input !== 'undefined') ? tax_input[this.options.name] : [],
                skill = [];
            this.collection.reset();
            for (var i = skills.length - 1; i >= 0; i--) {
                var model = new Models.Skill(skills[i]);
                this.collection.add(model);
            };
        },
        /*add model to skill callback
         *  handle to render item view and append to list
         */
        addOne: function(model) {
            var skillView = new Views.Skill_Item({
                model: model
            });
            this.model.set(this.options.name, this.collection.toJSON());
            this.skill_list.append(skillView.render().$el);
        },
        // remove skill from collection
        removeOne: function(model) {
            this.model.set(this.options.name, this.collection.toJSON());
        },
        /**
         * add tag to modal, render tagItem base on in put tag
         */
        addSkill: function(skill) {
            var view = this;
            // this.model.set('skill', []);
            //var duplicates = this.skill_list.find('input[type=hidden][value="' + skill + '"]');
            var count = this.skill_list.find('li');
            var max_length = 5;
            if( typeof this.options.max_length !== 'undefined' ){
                max_length = parseInt( this.options.max_length );
            }

            var duplicates = this.collection.where({name: skill});
            if (duplicates.length == 0 && skill != '' && count.length < max_length) {
                var data = {
                    'name': skill
                };
                this.collection.add(new Models.Skill(data));
                $('#skill').val('');
            }
        },
        onClickAddSkill: function(e) {

            e.preventDefault();
            var val = this.$("textarea#skill").val(); // textarea for mobile.
            if( val === ''  ){
                $(".add-skill").removeClass('active');
                return false;
            } else {
                this.splitSkill(val);
                this.$("textarea#skill").val('');
                $(".add-skill").removeClass('active');
            }

        },
        onAddSkillMobile: function(event){
            var val = $(event.currentTarget).val();
            var filter = $('#filter_skill').val();
            if (event.which == 13) {
                $(".add-skill").removeClass('active');
                this.splitSkill(val);
            }
            return event.which != 13;
        },
        /**
         * catch event user enter in skill input, call function addSkill to render tag item
         */
        onAddSkill: function(event) {

            var val = $(event.currentTarget).val();
            var filter = $('#filter_skill').val();
            if (event.which == 13) {
                $(".add-skill").removeClass('active');
                this.splitSkill(val);
            }
            return event.which != 13;
        },

        splitSkill: function(val) {
            var skills = val.split(",");
            for(i = 0; i < skills.length; i++) {
                // Remove first and last space of a string
                var skill = skills[i].replace(/^\s+|\s+$/g, '');
                if(skill != "") {
                    this.addSkill(skill);
                }
            }
        },
        /**
         * list profile page filter follow skill
         */
        filterSkill: function() {}
    });
    FilterRouter = Backbone.Router.extend({
        routes: {
            '!filter/:query': 'filter'
        },
        filter: function(query) {
            this.trigger('filter', query);
        },
    });
    Views.BlockControl = Backbone.Marionette.View.extend({
        initialize: function(options) {
            _.bindAll(this, 'addPost', 'onModelChange');
            var view = this;
            this.page = 1;
            this.options = _.extend(this, options);
            this.blockUi = new Views.BlockUi();
            if (this.$('.ae_query').length > 0) {
                this.query = JSON.parse(this.$('.ae_query').html());
            } else {
                this.$('.paginations').remove();
            }
            // bind event add to collection
            this.collection.on('add', this.addPost, this);
            // bind event when model change
            AE.pubsub.on('ae:model:statuschange', this.onModelChange, this);
            // init grid view
            this.grid = (options.grid) ? options.grid : 'grid';
            this.searchDebounce = _.debounce(this.onSearchDebounce, 500);
            if (this.$('.skill-control').length > 0 && this.$('.skill_filter').val() != '') {
                // init collection skill
                this.post = new Models.Post();
                this.skill_view = new Views.Skill_Control({
                    collection: this.skills,
                    model: this.post,
                    el: view.$('.skill-control')
                });
                // bind event collection skill change, add, remove filter
                this.skills.on('add', this.filterSkill, this);
                this.skills.on('remove', this.filterSkill, this);
            }

            // Get blocked el
            if(typeof this.options.blockedEl !== 'undefined') {
              this.blockedEl = this.options.blockedEl
            } else {
              this.blockedEl = '';
            }

            view.triggerMethod("after:init");
             //custom code seynou
             view.setupCustomFilterLib();             
             //end
        },
        filterSkill: function() {
            var skill = this.skills.toJSON();
            var view = this;
            var input_skill = $('.skill');
            skill = _.map(skill, function(element) {
                return element['name'];
            });
            view.query['skill'] = skill;
            view.page = 1;
            view.fetch(input_skill);
        },
        events: {
            // ajax load more
            'click a.load-more-post': 'loadMore',
            // select a page in pagination list
            'click .paginations a.page-numbers': 'selectPage',
            // previous page
            'click .paginations a.prev': 'prev',
            // next page
            'click .paginations a.next': 'next',
            // filter
            'change select ': 'selectFilter',
             //custom code seynou
             'change #recruit_name_filter': 'selectFilter',
             'change #budget_range1': 'selectFilter',
             'change #budget_range2': 'selectFilter',
             'click #clear-custom-filters-btn': 'clearRecruitCustomFilter',
             //end
            // order post list by date/rating
            'click a.orderby': 'order',
            // filter post_status
            'click a.click-type' : 'clickType',
            // switch view between grid and list
            'click .icon-view': 'changeView',
            // search post
            'keyup input.search': 'search',
            // Slider range drag
            'slideStop .slider-ranger': 'filterRange',
            'change .slider-ranger': 'filterRange',
            // Change date filer
            'changeDate .datepicker': 'filterDate',

            // Custom filter
            // @todo Chua tim ra cach extend event tren backbone version moi
            'click .custom-filter-query a': 'customFilter',
            //filter search advance
            'click button.filter-price': 'filterPrice',
            'change input[type=checkbox]': 'changedCheckbox',
            'click .reset_term_link': 'resetTermLink',
            //custom code search textbox in all mjobs page 13th april
            'click .custom-btn-search': 'customSearchQuery',
            //end custom code
        },
        resetTermLink: function (event){
            window.location.href = ae_globals.term_link;
        },
        customFilter: function(event) {
            event.preventDefault();
            var view = this;
            this.customFilter = true;
            var $target = $(event.currentTarget),
                name = $target.attr('data-name'),
                value = $target.attr('data-value'),
                liveUpdateEl = view.$el.find('.block-title .block-title-text');

            // Add class active
            $('.custom-filter-query a').removeClass('active');
            $target.addClass('active');

            // Add class active for parents
            $('#accordion li').removeClass('active');
            $target.parents('.open').addClass('active');

            if (name !== 'undefined') {
                //view.router.navigate($target.attr('href'));
                if(ae_globals.is_search) {

                    var name_cat = $target.attr('data-name'),
                    value_cat = $target.attr('data-value');
                    AE.pubsub.trigger('mje:getParamSearch', {
                        name: name_cat,
                        val: value_cat,
                        sort: '',
                    });
                } else {
                    ae_globals.term_link = $target.attr('href');
                    var term_link = $target.attr('href');
                    //window.history.pushState('', '', $target.attr('href'));
                    window.history.pushState('', '', term_link );
                }

                // update title
                //liveUpdateEl.text($target.text());

                view.query[name] = value;
                view.page = 1;
                // fetch page
                view.query['s'] = '';
                view.fetch($target);
            }
            return false;
        },
        /**
         * handle on change search field
         */
        search: function(event) {
            var target = $(event.currentTarget);
            this.searchDebounce(target);
        },
        /**
         * handle ajax search
         */
        onSearchDebounce: function($target) {
            var name = $target.attr('name'),
                view = this;
            if (name !== 'undefined') {
                view.query[name] = $target.val();
                view.page = 1;
                // fetch page
                view.fetch($target);
            }
        },
        /**
         * catch event add post to collection and add current page to model
         */
        addPost: function(post, col, options) {
            post.set('page', this.page);
        },
        /**
         * load more places
         */
        loadMore: function(e) {
            e.preventDefault();
            var view = this,
                $target = $(e.currentTarget);
            view.page++;
            // collection fetch
            view.collection.fetch({
                remove: false,
                data: {
                    query: view.query,
                    page: view.page,
                    paged: view.page,
                    action: 'ae-fetch-posts',
                    paginate: true,
                    thumbnail: view.thumbnail,
                    text: $target.text()
                },
                // get the thumbnail size of post and send to server
                thumbnail: view.thumbnail,
                beforeSend: function() {
                    view.blockUi.block($target);
                    view.triggerMethod("before:loadMore");
                },
                success: function(result, res, xhr) {
                    view.blockUi.unblock();
                    view.$('.paginations-wrapper').html(res.paginate);
                    AE.pubsub.trigger('aeBlockControl:after:loadMore', result, res);
                    if (res.max_num_pages == view.page || !res.success) {
                        $target.parents('.paginations').hide();
                        $target.remove();
                    }
                    view.switchTo();
                    view.triggerMethod("after:loadMore", result, res);
                }
            });
        },
        //custom code seynou
        setupCustomFilterLib: function()
        {   
            var view=this;
            $(".custom-recruit-filter").prepend("<option value=''>Please select</option");  
            $(".custom-recruit-filter").val($(".custom-recruit-filter option:first").val());
            let settings = 
            {                
                create:true,          
                sortField: {
                    field: "text",
                    direction: "asc"
                },            
               
            };    
            view.tomSelectInstances = [];            
           $('.custom-filters').each(function(index, element) {   
                
                let tomSelectFilters = new TomSelect(element, settings);
                view.tomSelectInstances.push(tomSelectFilters);
            
          });


            let settings_recruit = {
                maxItems: 1,
                copyClassesToDropdown: true,
                render: {
                    option: function(data, escape) {
                        return '<div class="' +escape(data.$option.className) +'">' + escape(data.text) + '</div>';
                    },
                }
            };
            if($(".custom-recruit-filter").length > 0)
            {
                view.customRecruitFilter = new TomSelect('.custom-recruit-filter',settings_recruit);
            }
            
        },
        //end
        selectFilter: function(event) {
            var $target = $(event.currentTarget),
                name = $target.attr('name'),
                order = $target.find(':selected').attr('data-order');
                view = this;
            if( name == 'download' ){
                var v1 = $target.find(':selected').val();
                if( ! v1 || v1 == '0')
                 return 0;
                var form    = $target.closest('form');
                var btnSubmit = form.find("button[type='submit']");
                btnSubmit.trigger('click');
                return 0;
            }
            if (name !== 'undefined') {
                view.query[name] = $target.val();
                if(typeof order != "undefined")
                {
                    view.query['order'] = order;
                }
                //custom code seynou
                view.query['custom_filters']=$("#custom_filters_option").val();
                if(view.query['post_type']=='recruit' && view.query['custom_filters']=='filter-at-recruit-page')
                {                                                            
                    view.query['budget_min']=$("#budget_range1").val();
                    view.query['budget_max']=$("#budget_range2").val();
                    view.query['orderby']='date';
                    view.query['order']=$("#sortdate").val();
                    view.query['delivery_date_number']=$("#delivery_date_number").val();
                    view.query['s']=$("#recruit_name_filter").val();                                                                             
                }
                //end
                AE.pubsub.trigger('mje:getParamSearch', {
                    name: name,
                    val: $target.val(),
                    sort: typeof order != "undefined" ? order:'',
                });
                view.page = 1;
                view.fetch($target);
            }
        },
         //custom code seynou
         clearRecruitCustomFilter: function(event)
         {
             var view=this;
             view.tomSelectInstances.forEach(function(instance) {
                 instance.clear();
                 let inputId = instance.input.id;
                 if(inputId=='sortdate')
                 {
                   instance.setValue('DESC');
                 }                
     
             });
             $("#recruit_name_filter").val('');                        
             $("#budget_range1").val(1);   
             $("#budget_range2").val(2000);   
             $("#mjob_category option:first").prop("selected", true);
             view.customRecruitFilter.clear();
             setTimeout(function()
             { 
                 view.selectFilter(event);                            
             }, 1000);
             //view.selectFilter(event);                            
         },
         //end
        filterPrice: function(event)
        {
            var view = this;
            var $target = $(event.currentTarget);
            var min_budget = $('.'+ event.target.parentNode.className.split(" ")[0]).find("input[name='min_budget']").val();
            var max_budget = $('.'+ event.target.parentNode.className.split(" ")[0]).find("input[name='max_budget']").val();


            if(min_budget || max_budget)
            {
                view.query['et_budget'] = min_budget + ',' + max_budget;
                if(min_budget && max_budget && parseInt(min_budget) > parseInt(max_budget))
                {
                    $('.'+ event.target.parentNode.className.split(" ")[0]).find("input[name='min_budget']").val(max_budget);
                    $('.'+ event.target.parentNode.className.split(" ")[0]).find("input[name='max_budget']").val(min_budget)
                     view.query['et_budget'] = max_budget + ',' + min_budget;
                }
            }
            else
                view.query['et_budget']= '';

            AE.pubsub.trigger('mje:getParamSearch', {
                    name: 'et_budget',
                    val: view.query['et_budget'],
                    sort: '',
            });
            view.page = 1;
            view.fetch($target);
        },
        //custom code for textbox search in all mjobs page 13th april 2024
        customSearchQuery: function(event)
        {
            console.log('ssss test');
            var view = this;
            var $target = $(event.currentTarget);
            let custom_search_query=$("#custom_search_query").val();
            if(custom_search_query.length > 0)
            {
                view.query['s']=custom_search_query;
                AE.pubsub.trigger('mje:getParamSearch', {
                    name: 'custom_search_query',
                    val: view.query['s'],
                    sort: '',
                });
                view.page = 1;
                view.fetch($target);
            }
        },
        //end custom code
        changedCheckbox: function(event) {
            var $target = $(event.currentTarget),
                target_parent = $target.closest('label');
             name = $target.attr('name'),
             view = this;
            if (typeof name !== 'undefined' && typeof view.query !== 'undefined') {
                AE.pubsub.trigger('mje:getParamSearch', {
                    name: name,
                    val: $target.is(':checked'),
                    sort: '',
                });
                view.query[name] = $target.is(':checked');
                view.page = 1;
                view.fetch(target_parent);
            }
        },
        clickType : function(event){
            event.preventDefault();
            var $target = $(event.currentTarget),
                name = $target.attr('data-name'),
                type = $target.attr('data-type'),
                view = this;
            if ($target.hasClass('active')) return;

            if(name !== 'undefined'){
                view.$el.find('.click-type').parent().removeClass('active');
                $target.parent().addClass('active');
                /**
                 * set post_status arg to query
                 */
                view.query[name] = type;
                view.page = 1;
                //fetch post
                view.fetch($target);
            }
        },
        order: function(event) {
            event.preventDefault();
            var $target = $(event.currentTarget),
                name = $target.attr('data-sort'),
                order = $target.attr('data-order'),
                view = this;
            if (name !== 'undefined') {
                view.$('.orderby').removeClass('active');
                $target.addClass('active');
                /**
                 * set orderby arg to query
                 */
                view.query['orderby'] = name;
                view.query['order'] = 'DESC';
                if(typeof order !== 'undefined') {
                    view.query['order'] = order.toUpperCase();
                }

                view.page = 1;
                // fetch post
                view.fetch($target);
            }
        },
        /**
         * toggle view between grid and list
         */
        changeView: function(event) {
            var $target = $(event.currentTarget),
                view = this;
            // return if target is active
            if ($target.hasClass('active')) return;
            // add class active to current targets
            this.$('.icon-view').removeClass('active');
            $target.addClass('active');
            // update view grid
            if ($target.hasClass('grid-style')) {
                view.grid = 'grid';
            } else {
                view.grid = 'list';
            }
            // switch
            view.triggerMethod("after:changeView", $target );
            view.switchTo();
        },
        /**
         * filer range for budget
         */
        filterRange: function(event) {
            event.preventDefault();
            var view = this,
                $target = $(event.currentTarget),
                name = $target.attr('name');
            view.query[name] = event.value; //$target.val();
            view.page = 1;

            AE.pubsub.trigger('mje:changedRanger',{
                    name: name,
                    val: event.value,

            }, view);
            view.fetch($target);
        },
        /**
         *
         */
        filterDate: function(event) {
            event.preventDefault();
            var view = this,
                $target = $(event.currentTarget),
                name = $target.attr('name');
            view.query[name] = $target.val();
            $(event.currentTarget).datepicker('hide');
            view.fetch($target);
        },
        /**
         * select a page in paginate
         */
        selectPage: function(event) {
            event.preventDefault();
            var $target = $(event.currentTarget),
                page = parseInt($target.text().replace(/,/g, '')),
                view = this;
            if ($target.hasClass('current') || $target.hasClass('next') || $target.hasClass('prev')) return;
            view.page = page;
            // fetch posts
            view.fetch($target);
            //scroll to block control id
            $('html, body').animate({
                scrollTop: view.$el.offset().top - 180
            }, 800);
        },
        // prev page
        prev: function(event) {
            event.preventDefault();
            var $target = $(event.currentTarget),
                view = this;
            // descrease page
            view.page--;
            // fetch posts
            view.fetch($target);

            //scroll to block control id
            $('html, body').animate({
                scrollTop: view.$el.offset().top - 180
            }, 800);
        },
        // next page
        next: function(event) {
            event.preventDefault();
            var $target = $(event.currentTarget),
                view = this;
            // increase page
            view.page = view.page + 1;
            view.fetch($target);

            //scroll to block control id
            $('html, body').animate({
                scrollTop: view.$el.offset().top - 180
            }, 800);
        },
        // fetch post
        fetch: function($target) {
            var view = this,
                page = view.page;
            view.collection.fetch({
                wait: true,
                remove: true,
                reset: true,
                data: {
                    query: view.query,
                    page: view.page,
                    paged: view.page,
                    paginate: view.query.paginate,
                    thumbnail: view.thumbnail,
                    is_tax_mjob_category:  ae_globals.is_tax_mjob_category,
                },
                beforeSend: function() {
                    var blockedTarget = $target;

                    if(view.blockedEl != '') {
                      blockedTarget = view.blockedEl;
                    } else if($target.hasClass('multi-tax-item') || $target.hasClass('is-chosen')) {
                        if ($target.next('.chosen-container').length > 0) {
                          blockedTarget = $target.next('.chosen-container');
                        }
                    }


                    view.blockUi.block(blockedTarget);

                    view.triggerMethod("before:fetch");
                },
                success: function(result, res, xhr) {
                    view.blockUi.unblock();
                    // view.collection.reset();
                    if (res && !res.success) {
                        //view.$('.paginations').remove();
                        view.$('.paginations-wrapper').hide();
                        view.$('.paginations').remove();
                        view.$('.found_post').html(0);
                        view.$('.plural').addClass('hide');
                        view.$('.singular').removeClass('hide');
                    } else {

                        view.$('.paginations-wrapper').show();
                        view.$('.paginations-wrapper').html(res.paginate);
                        $('#place-status').html(res.status);
                        view.$('.found_post').html(res.total);
                        if (res.total > 1) {
                            view.$('.plural').removeClass('hide');
                            view.$('.singular').addClass('hide');
                        } else {
                            view.$('.plural').addClass('hide');
                            view.$('.singular').removeClass('hide');
                        }
                        view.switchTo();
                    }

                    if( ae_globals.is_tax_mjob_category > 0 || ae_globals.is_archive_mjob == 1 ){
                        var default_url = new_url =  '';
                        if( ae_globals.term_link  != '' ){
                            default_url = new_url =  ae_globals.term_link;
                        } else{
                            default_url = new_url = ae_globals.is_archive_mjob_link;
                        }


                        if( view.query){

                            if( view.query.skill && view.query.skill != ''  ){
                                var str  = view.query.skill.join();
                                new_url = default_url+'?skil_ids=' + str;
                            }
                            if( view.query.language && view.query.language != ''  ){

                                var str  = view.query.language.join();
                                if( new_url == default_url){
                                    new_url = new_url+'?language_ids=' + str;
                                } else{
                                    new_url = new_url+'&language_ids=' + str;
                                }
                            }
                            if( view.query.location && view.query.location != ''  ){

                                var str  = view.query.location.join();
                                if( new_url == default_url){
                                    new_url = new_url+'?location=' + str;
                                } else{
                                    new_url = new_url+'&location=' + str;
                                }
                            }

                            if(view.query.et_budget & view.query.et_budget != ''  ){
                                var budget = view.query.et_budget.split(",");
                                min = budget[0],
                                max = budget[1],
                                 str = '';
                                if(min != '')  {
                                    if( new_url == default_url){
                                        new_url = new_url+'?price_min=' + min;
                                    } else{
                                        new_url = new_url+'&price_min=' + min;
                                    }
                                }
                                if(max != '') {
                                     if( new_url == default_url){
                                        new_url += '?price_max=' + max;
                                    } else{
                                        new_url+='&price_max=' + max;
                                    }
                                }
                            }
                        }
                        window.history.pushState('', '',new_url );
                    }

                    // Focus out
                    if($target.hasClass('multi-tax-item') || $target.hasClass('is-chosen')) {
                        var chosenContainer = $target.next('.chosen-container');
                        chosenContainer.find('.search-field input').blur();
                    }

                    AE.pubsub.trigger('ae:on:after:fetch', result, res, $target);
                    view.triggerMethod("after:fetch", result, res, $target);

                }
            });
        },
        /**
         * on model change update collection
         */
        onModelChange: function(model) {
            var post_status = model.get('post_status');
            if (post_status === 'archive' || post_status === 'reject' || 'post_status' == 'trash') {
                if( typeof model.get('is_author') === 'undefined' || !model.get('is_author') ) {
                    this.collection.remove(model);
                }
            }
        },
        /**
         * switch between grid and list
         */
        switchTo: function() {
            if (this.$('.list-option-filter').length == 0) return;
            var view = this;
            if (view.grid == 'grid') {
                view.$('ul > li').addClass('col-md-3 col-xs-6').removeClass('col-md-12');
                // view.$('ul > li').addClass('col-md-4').removeClass('col-md-12');
                view.$('ul').removeClass('fullwidth');
            } else {
                view.$('ul > li').removeClass('col-md-3 col-xs-6').addClass('col-md-12');
                // view.$('ul > li').removeClass('col-md-4').addClass('col-md-12');
                view.$('ul').addClass('fullwidth');
            }
        }
    });
    /**
     * modal reject : render reject ad view help admin can reject an ad, and send seller a message
     */
    Views.RejectPostModal = Views.Modal_Box.extend({
        events: {
            'submit form.reject-ad': 'submitReject'
        },
        initialize: function(options) {
            this.options = _.extend(this, options);
            AE.Views.Modal_Box.prototype.initialize.call();
            this.blockUi = new Views.BlockUi();
        },
        /**
         * set model to modal view
         */
        onReject: function(model) {
            this.model = model;
            this.openModal();
            this.$el.find('input[name=id]').val(model.get('ID'));
            this.$el.find('span.post_name').text(model.get('post_title'));
            AE.pubsub.trigger('ae:openRejectModal', model);
        },
        /**
         * submit reject and send message to owner
         */
        submitReject: function(event) {
            event.preventDefault();
            var view = this,
                form = $(event.target),
                message = $(form).find('textarea[name=reject_message]').val();
            if(!message.length)
            {
                AE.pubsub.trigger('ae:notification', {
                           notice_type: 'error',
                           msg: ae_globals.requiredField
                });
                return;
            }
            this.model.set('reject_message', message);
            this.model.save('post_status', 'reject', {
                beforeSend: function() {
                    if( typeof view.options.target !== 'undefined' ) {
                        view.blockUi.block(view.options.target);
                    }
                    else{
                        view.blockUi.block(form);
                    }
                },
                success: function(model, res) {
                    view.blockUi.unblock();
                    var type = 'error';
                    if (res.success) {
                        type = 'success';
                        view.closeModal();
                    }
                    // for remove ad from list pending ad
                    AE.pubsub.trigger('ae:post:afterReject', model, res);
                    // for render header in single-job
                    AE.pubsub.trigger('ae:afterRejectPost', model, res);
                    AE.pubsub.trigger('ae:notification', {
                        msg: res.msg,
                        notice_type: type
                    });
                }
            });
        }
    });
    /**
     * Carousel View control view insert carousel for a model
     * author Dakachi
     */
    Views.Carousel = Backbone.View.extend({
        action: 'ae_request_thumb',
        events: {
            'hover .catelory-img-upload': 'hoverCarousel',
            'mouseleave .catelory-img-upload': 'unhoverCarousel',
            'click  .delete ': 'removeCarousel',
            'click .catelory-img-upload img': 'setFeatured'
        },
        // template: _.template($('#carousels-item-template').html()),
        initialize: function(options) {
            this.maxFileUpload = ae_globals.max_images;
            this.options = options;
            if(typeof this.options.min_images === 'undefined' ){
                this.minFileUpload = 0;
            }
            else{
                this.minFileUpload = this.options.min_images;
            }
            this.setupView();
            // catch event handle auth to update ajax nonce
            AE.pubsub.on('ae:user:auth', this.handleAuth, this);
            /**
             * setup ae carousel template
             */
            var carouselTemplate = "";
            if(typeof this.options.carouselTemplate !== "undefined" && this.options.carouselTemplate != "") {
                carouselTemplate = this.options.carouselTemplate;
            } else {
                carouselTemplate = "#ae_carousel_template";
            }

            if ($(carouselTemplate).length > 0) {
                this.template = _.template($(carouselTemplate).html());
            } else {
				alert('Hi dev, to user ad carousels you have to add a template for image item ae_carousel_template ');
            }
        },
        /**
         * handle authentication to update ajax nonce
         */
        handleAuth: function(model, resp, jqXHR) {
            if (resp.success) {
                this.carousel_uploader.config.multipart_params._ajax_nonce = resp.data.ajaxnonce;
            }
        },
        /**
         * bind a model to view
         */
        setModel: function(model) {
            this.model = model;
            // this.resetUploader();
        },
        /**
         *
         */
        setupView: function() {
            var that = this,
                $carousel = this.$el,
                i = 0,
                j = 0;
            this.carousels = this.model.get('et_carousels') || [];
            this.featured_image = this.model.get('featured_image') || '';

            this.blockUi = new Views.BlockUi();
            that.numberOfFile = this.carousels.length;
            /**
             * clear the list
             */
            this.$('#image-list').find('li.image-item').remove();
            /**
             * get model image and init view
             */
            var items = [];
            $.each(this.carousels, function(index, item) {
                items.push(item);
            });
            if (items.length > 0) {
                $.ajax({
                    url: ae_globals.ajaxURL,
                    type: 'get',
                    data: {
                        item: items,
                        action: that.action
                    },
                    beforeSend: function() {},
                    success: function(res) {
                        if (res.success) {
                            $.each(res.data, function(index, item) {
                                if (typeof item.thumbnail !== 'undefined') {
                                    var $ul = $('#image-list');
                                    if (item.attach_id == that.model.get('featured_image')) item.is_feature = true;
                                    var li = that.template(item);
                                    $ul.prepend(li);
                                }
                            });
                            AE.pubsub.trigger('ae:after:setup:carousels', res);
                        }
                    }
                });
            }
            if( typeof that.options.uploaderID !== 'undefined' ){
                uploaderID = that.options.uploaderID;
            }
            else {
                var uploaderID = 'et_carousel';
            }
            //add multi option for upload photo
			var multi = (typeof this.options.multiple == "undefined" || this.options.multiple == null)?true:this.options.multiple;
            if (typeof this.carousel_uploader === 'undefined') this.carousel_uploader = new Views.File_Uploader({
			   el: $carousel,
                extensions : (this.options.extensions) ? this.options.extensions : 'jpg,jpeg,gif,png,ico',
                uploaderID: uploaderID,
                thumbsize: 'thumbnail',
                multi_selection: multi,
                multipart_params: {
                    _ajax_nonce: $carousel.find('.et_ajaxnonce').attr('id'),
                    // action: 'et-carousel-upload',
                    imgType: 'ad_carousels',
                    author: that.model.get('post_author'),
                    data: uploaderID
                },
                filters: (this.options.filters) ? this.options.filters : {},
                cbUploaded: function(up, file, res) {
                    if (res.success) {
                        if( $carousel.find('.'+that.options.uploaderID+'-image-list').length > 0 ){
                            var $ul = $carousel.find('.'+that.options.uploaderID+'-image-list');
                        }
                        else {
                            var $ul = $('#image-list');
                        }
                        var li = that.template(res.data);
                        $ul.prepend(li);
                        // update carousel list item
                        //carousel_list = carousel_list+','+res.data.attach_id;
                        that.carousels.push(res.data.attach_id);
                        //$('.carousel-list').find('#carousels').val(carousel_list);
                        that.model.set('et_carousels', that.carousels);
                        that.numberOfFile = that.carousels.length;

                        if(that.numberOfFile >= that.maxFileUpload) {
							up.disableBrowse();
                            $('#'+that.options.uploaderID+'_browse_button').addClass('disable-browse');
                        }
                    }
                },
                cbAdded: function(up, files) {
                    var max_files = that.maxFileUpload;
                    //var carousels     =   that.model.get('et_carousels') || [];
                    that.numberOfFile = that.$('.image-item').length;
                    j = that.numberOfFile;
                    i = that.numberOfFile;
                    if (files.length > (max_files - that.numberOfFile)) {
                        AE.pubsub.trigger('ae:notification', {
                            notice_type: 'error',
                            msg: 'You are allowed to add only ' + (max_files - that.numberOfFile) + ' files.'
                        })
                    }

                    plupload.each(files, function(file) {
                        if (files.length > (max_files - that.numberOfFile)) {
                            //alert('You are allowed to add only ' + max_files + ' files.');
                            up.removeFile(file);
                            //alert('You are allowed to add only ' + max_files - that.numberOfFile + ' files.');
                        } else {
                            i++;
                        }
                    });
                },
                beforeSend: function(element) {
                    // pubsub.trigger ('ce:carousels:uploading');
                    that.model.set('uploadingCarousel', true);
                    that.blockUi.block($('#' + that.options.uploaderID + '_container'));
                },
                success: function() {
                    that.model.set('uploadingCarousel', false);

                    var featured = that.$el.find('span.featured');
                    if (featured.length == 0) {
                        var last = that.$el.find('.catelory-img-upload:last');
                        last.addClass('featured');
                        that.model.set('featured_image', last.attr('id'));
                    }
                    that.blockUi.unblock();
                    // pubsub.trigger ('ce:carousels:finished');
                    j++;
                }
            });

            // Enable upload button
            this.carousel_uploader.controller.disableBrowse(false);
            $('#'+that.options.uploaderID+'_browse_button').removeClass('disable-browse');
        },
        resetUploader: function() {
            if (typeof this.carousel_uploader === 'undefined') return;
            this.carousel_uploader.controller.splice();
            this.carousel_uploader.controller.refresh();
            this.carousel_uploader.controller.destroy();
        },
        removeCarousel: function(event) {
            event.preventDefault();
            var $target = $(event.currentTarget),
                $span = $target.parents('.image-item'),
                id = $span.attr('id'),
                that = this,
                max_files = that.maxFileUpload;

            var view = this;

            var carousels = this.carousels;
            carousels = $.grep(carousels, function(a) {
                return a != id;
            });
            if( this.numberOfFile > this.minFileUpload ) {
                this.model.set('et_carousels', carousels);
                this.carousels = carousels;
                $.ajax({
                    type: 'post',
                    url: ae_globals.ajaxURL,
                    data: {
                        action: 'ae_remove_carousel',
                        id: id
                    },
                    beforeSend: function () {
                    },
                    success: function () {
                        view.carousel_uploader.controller.disableBrowse(false);
                        $('#'+that.options.uploaderID+'_browse_button').removeClass('disable-browse');
                    }
                });
                AE.pubsub.trigger('ae:carousel:before:remove', this.model, $span);
                $span.remove();
                this.numberOfFile = this.numberOfFile - 1;
                AE.pubsub.trigger('ae:carousel:after:remove', this.model);
            }
            else {
                AE.pubsub.trigger('ae:carousel:cannot:remove', this.model);
            }
        },
        setFeatured: function(event) {
            var $target = $(event.currentTarget);
            this.model.set('featured_image', $target.attr('data-id'));
            $('.catelory-img-upload').removeClass('featured');
            $target.parents('.catelory-img-upload').addClass('featured');
        },
        hoverCarousel: function(event) {
            var $target = $(event.currentTarget);
            $target.find('img').animate({
                'opacity': '0.5'
            }, 200);
            $target.find('.delete').animate({
                'opacity': '1'
            }, 200);
        },
        unhoverCarousel: function(event) {
            var $target = $(event.currentTarget);
            $target.find('img').animate({
                'opacity': '1'
            }, 200);
            $target.find('.delete').animate({
                'opacity': '0'
            }, 200);
        }
    });
    /**
     * submit post view
     */
    Views.SubmitPost = Backbone.Marionette.View.extend({
        events: {
            // select plan, you should add class select-plan to select button
            'click .select-plan': 'selectPlan',
            // submit authentication form
            'submit form.auth': 'submitAuth',
            // submit post form
            'submit form.post': 'submitPost',
            // select a payment gateway
            'click .select-payment': 'selectPayment',
            'click .other-payment': 'extendPayment',
            // user select a step
            'click .step-heading': 'selectStep',
            // update map lat long
            // 'keyup input#et_full_location': 'gecodeMap'
              //custom code audio upload
              'dragover #audio-upload': 'showEffectDragover',
              'dragleave #audio-upload': 'showEffectDragleave',
              'drop #audio-upload': 'showEffectDropleave',
              //end
        },
        // model event
        modelEvents: {
            // when user change payment plan sync with server
            'change:et_payment_package': 'updatePayment'
        },
        /**
         * update model payment plan when change it
         */
        updatePayment: function() {
            if (!this.model.isNew()) {
                this.model.save();
            }
        },
        /**
         * init submit post view
         * @params array options
         * - step : number of step
         * - steps : array of step name
         * - use_plan : submit post with payment plan or not
         * - limit_free_plan : the maximum number of free plan user can use
         * - free_plan_used : current user free plan used
         */
        initialize: function(options) {
            _.bindAll(this, 'userLogin');
            this.step = 4;
            this.steps = ['plan', 'auth', 'post', 'payment'];
            this.options = _.extend(this, options);
            if ($('#edit_postdata').length > 0) {
                var postdata = JSON.parse($('#edit_postdata').html());
                this.model = new Models.Post(postdata);
                this.model.set('renew', 1);
                this.setupFields();
            } else {
                this.model = new Models.Post();
            }
            this.user = AE.App.user;
            // init block ui
            this.blockUi = new Views.BlockUi();
            this.formValidate();
            this.finishStep = [];
            // trigger method before init
            this.redirect = true;
            this.triggerMethod("after:init", this);
            // handle current user
            this.user.on('change:id', this.userLogin);
            if (parseInt(this.step) == 2) this.currentStep = 'auth';
            if (parseInt(this.step) == 4) this.currentStep = 'plan';
            // this.initMap();
            this.setupFirstStep();
             //custom code for audio upload
             var audio_uploader;    
             this.setupAudioUpload();        
             this.audio_attach_ids=[];  
             this.submitable=true;           
             //end
        },
         //custom code for audio upload
        showEffectDragover: function()
        {
            $('#audio-upload').addClass('upload-dragging');
        },
        showEffectDragleave: function()
        {
            $('#audio-upload').removeClass('upload-dragging');
        },
        showEffectDropleave: function()
        {
            $('#audio-upload').removeClass('upload-dragging');
        },    
        setupAudioUpload: function(){
            var self=this;  
                           
        if (typeof plupload !== 'undefined') {
            // Set the Plupload settings
            var pluploadSettings = {
                runtimes: 'html5,flash,silverlight,html4',
                browse_button: 'audio-btn-upload', // ID of the custom button
                container: 'audio-upload', // ID of the container for the uploader
                drop_element: 'audio-upload',
                dragdrop: true,                
                url: ajaxurl, // WordPress AJAX handler   
                chunk_size: '10mb',   
                multipart: true,      
                multipart_params: {
                    action: 'recruit_audio_upload_file', // Custom AJAX action for handling the upload
                    _ajax_nonce: $("#audio_upload_nonce").val(), // Nonce for security   
                    custom_file_name: "defaultname",
                    custom_file_type: "defaulttype",
                    custom_file_id: "defaultid",
                   // post_id:"default",                                         
                },
                filters: {
                    prevent_duplicates: true,
                    max_file_size: '200mb',
                    mime_types: [                        
                        { title: 'Sound files', extensions: 'mp3,wav' },                        
                    ],
                },
                multi_selection: true, // Allow multiple file selection
                max_file_count: 1,
                init: {
                  
                    FilesAdded: function(up, files) {                         
                        var selectedFiles = [];
                        for (var i = 0; i < Math.min(up.settings.max_file_count, files.length); i++) {
                            selectedFiles.push(files[i]);
                        }
                        self.displaySelectedFiles(selectedFiles);
                        self.audio_uploader = up; // Store the uploader object in a variable
                        self.audio_uploader.start();
                    },
                    BeforeUpload: function(up,file)
                    {          
                        //do not allow submit while uploading         
                        self.submitable=false;
                        //hide remove file button while uploading
                        $("[datacustomID='"+file.id+"']:first-child").css('display','none');

                        up.settings.multipart_params.custom_file_name = file.name;
                        up.settings.multipart_params.custom_file_type = file.type;
                        up.settings.multipart_params.custom_file_id = file.id;
                    },
                    UploadProgress: function(up, file) {
                        // Handle upload progress updates                          
                        $(".uploadprogressBar").css('display','block');
                        $(".uploadprogressBar").css('width',file.percent + '%');
                        $(".uploadprogressBar").html(file.percent + '%');
                        
                    },
                    FileUploaded: function(up, file, response) {
                        // Handle the server response after successful upload
                      // You can do something with the response data
                      
                      //allow submit when complete uploading
                      self.submitable=true;                 

                      //display remove file button when complete uploading
                      $("[datacustomID='"+file.id+"']:first-child").css('display','inline');               

                      var responseObject = JSON.parse(response.response);
                      var uploadResult = responseObject.success;                    
                      if(uploadResult==true || uploadResult =='true')
                      {
                        //self.audio_attach_ids.push(responseObject.attach_id);
                        self.audio_attach_ids.push({file_id: file.id, attach_id:responseObject.attach_id });
                        $("[datacustomID='"+file.id+"']").attr('attach_file_id',responseObject.attach_id);
                      }                           
                    },                   
                    ChunkUploaded: function(up,file,info) 
                    {
						
                    },
                    UploadComplete: function(up,file){
                       
                    },
                    Error: function(up, error) {
                        // Handle errors during the upload process
                       // console.log(error);
                       AE.pubsub.trigger('ae:notification', {
                        msg: error.message,
                        notice_type: 'error',
                     });
                       
                    },
                },
            };
    
            // Initialize the Plupload uploader without starting the upload
            audio_uploader = new plupload.Uploader(pluploadSettings);              
            audio_uploader.init();           
        }        
        },              
        displaySelectedFiles: function(files, remove = false){
            var self=this;
            var selectedFileNames = [];
            var fileListElement = $(".choosen-files-area");      
            if (remove == true)
            {
                fileListElement.empty(); 
            }
            // Clear the existing list of files
           

            files.forEach(function(file) {
                var fileName = file.name;
                var fileElement = $("<div>").addClass("chosen-audio-item").text(fileName);
                var removeButton = $("<a>", {
                    class: "remove-icon",
                    href:"javascript:void(0)",
                    html: "<i class='fa fa-remove'></i>",
                    datacustomID: file.id,
                    click: function() {
                        //hide progress bar
                        $(".uploadprogressBar").css('display','none');
                        
                        // Remove the selected file when the remove button is clicked
                        var idToRemove=file.id;
                        var indexToRemove = -1;
                        for (var i = 0; i < self.audio_attach_ids.length; i++) {
                            if (self.audio_attach_ids[i].file_id === idToRemove) {
                              indexToRemove = i;
                              break;
                            }
                        }
                        if (indexToRemove !== -1) {
                            self.audio_attach_ids.splice(indexToRemove, 1);
                          }                        
                        self.audio_uploader.removeFile(file);
                        self.displaySelectedFiles(audio_uploader.files,true);
                        console.log($(this).attr('attach_file_id'));     
                        console.log(ae_globals.ajaxURL);
                        Backbone.ajax({
                                type: "post",
                                url: ae_globals.ajaxURL,
                                dataType: 'json',
                                data: {
                                        action:'delete_attach_file_on_server',
                                        attach_file_id_delete:$(this).attr('attach_file_id'),                                        
                                    },                                
                                success: function (response) {
                                    console.log(response);
                                }
                            });

                    }
                });
                fileElement.append(removeButton);
                fileListElement.append(fileElement);
            });
        },
        //end custom   
        setupFirstStep : function() {
            var $target = $('.auto-select');
            if($target.length == 0) return;
            if($('.auto-select.publish').length > 0) {
                $target = $($('.auto-select.publish').get(0)).find('.select-plan');
            }else {
                $target = $($('.auto-select.pending').get(0)).find('.select-plan');
            }
            this.choosePlan($target);
        },
        /**
         *
         */
        setupFields: function() {
            var view = this,
                form_field = view.$('#step-post'),
                location = this.model.get('location');
            /**
             * update form value for input, textarea select
             */
            form_field.find('input.input-item,input[type="text"],input[type="hidden"], textarea,select').each(function() {
                var $input = $(this);
                $input.val(view.model.get($input.attr('name')));
                // trigger chosen update if is select
                if ($input.get(0).nodeName === "SELECT") $input.get(0).tomselect.addItem(view.model.get($input.attr('name')));
            });
            form_field.find('input[type="radio"]').each(function() {
                var $input = $(this),
                    name = $input.attr('name');
                if ($input.val() == view.model.get(name)) {
                    $input.attr('checked', true);
                }
            });
        },
        /**
         * add validator to check form validation
         */
        formValidate: function() {
            /**
             * auth form validate
             */
            $("form.auth").validate({
                rules: {
                    user_login: 'required',
                    user_pass: "required",
                    repeat_password: {
                        equalTo: "#user_pass"
                    },
                    user_email: {
                        required: true,
                        email: true
                        // remote: ae_globals.ajaxURL + '?action=et_email_check_used'
                    }
                }
            });
            /**
             * post form validate
             */
            $("form.post").validate({
                ignore: "",
                rules: {
                    post_title: "required",
                    et_full_location: "required",
                    place_category: "required",
                    post_content: "required",
                    location: "required"
                },
                errorPlacement: function(label, element) {
                    // position error label after generated textarea
                    if (element.is("textarea")) {
                        label.insertAfter(element.next());
                    } else {
                        $(element).closest('div').append(label);
                    }
                }
            });
        },
        customValidate: function(){
            return true;
        },
        /**
         * catch event select plan when user select plan
         * @author Dakachi
         */
        selectPlan: function(event) {
            event.preventDefault();
            this.choosePlan($(event.currentTarget));
        },
        // call when user select a payment plan
        choosePlan : function($target) {
            var $li = $target.closest('li'),
                amount = $li.attr('data-price'),
                $step = $li.closest('div.step-wrapper'),
                view = this;
            this.currentStep = 'plan';
            /**
             * call function beforeSelectPlan
             */
            this.triggerMethod("before:selectPlan", $step, $li);
            /**
             * set payment package to model
             */
            // set the job package of job model & free status
            if (parseFloat(amount) === 0) { // check selected plan price
                /**
                 * check free plan
                 */
                if (parseInt(this.limit_free_plan) > 0) { // check limit free plan
                    var used = view.free_plan_used;
                    // user have reached the limit free plan
                    if (parseInt(used) >= parseInt(view.limit_free_plan)) {
                        /**
                         * trigger method limit free, you can add a function onLimitFree to control your purpose
                         */
                        view.triggerMethod("limit:free", view);
                        return false;
                    }
                }
                // set post model free
                view.model.set({
                    is_free: 1
                });
            } else { // not free
                view.model.set({
                    is_free: 0
                });
            }
            this.model.set('et_payment_package', $li.attr('data-sku'));
            this.model.set('et_package_price_plan', $li.attr('data-price'));
            if( typeof $li.attr('data-package-type') !== 'undefined' ){
                this.model.set('et_package_type', $li.attr('data-package-type'));
            }
            /**
             * control button view by add class selected
             */
            this.$('.list-price li').removeClass('selected');
            $target.parents('li').addClass('selected');
            // hide all content step
            $li.closest('div.step-wrapper').addClass('complete');
            // add step plan to finish array
            if(ae_globals.user_ID == 0){
                this.addFinishStep('step-plan');
            }
            // this.$('.step-auth .content').slideDown();
            // show next step
            this.showNextStep();
            /**
             * trigger method onAfterSelectPlan for extended view
             */
            this.triggerMethod("after:selectPlan", $step, $li);
        },
        /**
         * user submit auth form to login or register acount
         */
        submitAuth: function(event) {
            event.preventDefault();
            var $target = $(event.currentTarget),
                view = this;
            $target.find('.input-item').each(function() {
                view.user.set($(this).attr('name'), $(this).val());
            });
            // trigger method before submit Auth
            view.triggerMethod('before:submitAuth', view.user, view);
            view.user.save('do', 'register', {
                beforeSend: function() {
                    view.blockUi.block($target);
                },
                success: function(model, res, jqXHR) {
                    view.blockUi.unblock($target);
                    if (res.success) {
                        view.currentStep = 'auth';
                        // add step auth to finish step
                        view.addFinishStep('step-auth');
                        // set user login is true
                        view.user_login = true;
                        // show nex step
                        view.showNextStep();
                        /*trigger event user authentication sucess*/
                        AE.pubsub.trigger('ae:user:auth', model, res, jqXHR);
                        // trigger method onSubmitAuthSuccess with params are model user and res
                        view.triggerMethod('after:authSuccess', model, res);
                    } else {
                        view.user_login = false;
                        // trigger method onSubmitAuthFail with params are model user and res
                        view.triggerMethod('after:authFail', model, res);
                    }
                }
            });
        },
        /*
         * catch event user change and update authentication step
         */
        userLogin: function(model) {
            var view = this;

            // if user have selected plan
            if (this.finishStep.length > 0 || view.currentStep == 'auth') {
                view.addFinishStep('step-auth');
            }

            if(parseInt(this.step) == 2 || this.finishStep.length > 1) {
                view.showNextStep();
            }

            // set user login is true
            view.user_login = true;
            // remove content of step auth
            this.$('.step-auth .content').remove();
            // update step auth heading text
            this.$('.step-auth .text-heading-step').html(model.get('label'));
            view.triggerMethod("after:showNextStep", 'post', view.currentStep);
        },
        /**
         * user submit form.post to submit a post
         */
        submitPost: function(event) {
            event.preventDefault();
            var $target = $(event.currentTarget),
                view = this,
                temp = [];
            if (view.model.get('uploadingCarousel')) return false;
            /**
             * update model data
             */
            $target.find('.input-item, .wp-editor-area').each(function() {
                view.model.set($(this).attr('name'), $(this).val());
            });
            $target.find('.tax-item').each(function() {
                view.model.set($(this).attr('name'), $(this).val());
            });
            // trigger method before SubmitPost
            view.triggerMethod('before:submitPost', view.model, view);
            /**
             * update input check box to model
             */
            view.$el.find('input[type=checkbox]').each(function() {
                var name = $(this).attr('name');
                view.model.set(name, []);
            });
            view.$el.find('input[type=checkbox]:checked').each(function() {
                var name = $(this).attr('name');
                if (name == "et_claimable_check") return false;
                if (typeof temp[name] !== 'object') {
                    temp[name] = new Array();
                }
                temp[name].push($(this).val());
                view.model.set(name, temp[name]);
            });
            /**
             * update input radio to model
             */
            view.$el.find('input[type=radio]:checked').each(function() {
                view.model.set($(this).attr('name'), $(this).val());
            });
            /**
             * save model
             */
            view.model.set('post_author', view.user.get('id'));
              //custom code audio here            
              view.model.set('audio_of_post',view.audio_attach_ids);             
              //end
            if (!view.model.get('uploadingCarousel')) {
                if( $("form.post").valid() && view.customValidate()) {
                    view.model.save('', '', {
                        beforeSend: function () {
                            if (typeof view.options.blockFormTarget !== "undefined" && view.options.blockFormTarget != "") {
                                var blockEl = $(view.options.blockFormTarget);
                                view.blockUi.block(blockEl);
                            } else {
                                view.blockUi.block($target);
                            }
                        },
                        success: function (model, res) {
                            view.blockUi.unblock();
                            if (res.success) {
                                //custom code seynou here                                                     
                                AE.pubsub.trigger('ae:notification', {
                                    msg: 'Submit successfully',
                                    notice_type: 'success',
                               });
                                                                                

                                setTimeout(function(){  view.triggerMethod('at:postSuccess', model, res);}, 1000);  
                                //view.triggerMethod('at:postSuccess', model, res);
                                //end 
                                // redirect to process payment if exist redirect url
                                if (typeof res.data.redirect_url !== 'undefined') {
                                    if (view.redirect) {
                                        //window.location.href = res.data.redirect_url;
                                    }
                                } else {
                                    view.currentStep = 'post';
                                    // add step auth to finish step
                                    view.addFinishStep('step-post');
                                    // show nex step
                                    view.showNextStep();
                                    // trigger method onSubmitPostSuccess with params are model user and res
                                    view.triggerMethod('after:postSuccess', model, res);
                                }
                                AE.pubsub.trigger('ae:submitPostSuccess', model, res); //mje-v1.3.9.3
                            } else {
                                // trigger method onSubmitPostFail with params are model user and res
                                view.triggerMethod('after:postFail', model, res);
                            }
                        }
                    });
                }
                //custom code seynou 
                if(view.submitable==false)
                {
                        AE.pubsub.trigger('ae:notification', {
                            msg: 'Please wait for uploading progress completed',
                            notice_type: 'error',
                       });
                }
                //end
            }
        },
        // user use payment which is not supported in theme
        extendPayment: function(event) {
            event.preventDefault();
            var view = this,
                $target = $(event.currentTarget),
                paymentType = $target.attr('data-type'),
                data = {
                    action: 'et-setup-payment',
                    // post id
                    ID: this.model.id,
                    // author
                    author: this.model.get('post_author'),
                    // package sku id
                    packageID: this.model.get('et_payment_package'),
                     //package type
                    packageType: this.model.get('et_package_type'),
                    // payment gateway
                    paymentType: paymentType,
                    // send coupon code if exist
                    coupon_code: view.$('#coupon_code').val(),
                    paymentTotal: this.model.get('et_package_price_plan')
                };
            AE.pubsub.trigger('ae:submitPost:extendGateway', data, event);
        },
        /**
         * user select a payment gateway to submit post
         */
        selectPayment: function(event) {
            event.preventDefault();
            var $target = $(event.currentTarget),
                paymentType = $target.attr('data-type'),
                $button = $target.find('button'),
                view = this;
                setupData = {
                    action: 'et-setup-payment',
                    // post id
                    ID: view.model.id,
                    // author
                    author: view.model.get('post_author'),
                    // package sku id
                    packageID: view.model.get('et_payment_package'),
                     //package type
                    packageType: view.model.get('et_package_type'),
                    // payment gateway
                    paymentType: paymentType,
                    // send coupon code if exist
                    coupon_code: view.$('#coupon_code').val()
                };
            $.ajax({
                url: ae_globals.ajaxURL,
                type: 'post',
                contentType: 'application/x-www-form-urlencoded;charset=UTF-8',
                // build data and send
                data: setupData,
                beforeSend: function() {
                    
                    // trigger method onBeforeSetupPayment
                    view.triggerMethod("before:setupPayment", setupData, $target);
                    
                    if(typeof view.blockPaymentGateways != "undefined") {
                        view.blockUi.block(view.blockPaymentGateways);
                    } else {
                        view.blockUi.block($target);
                    }
                },
                success: function(response) {
                    //view.blockUi.unblock();
                    if (typeof response.success !== 'undefined' && !response.success){

                        // add this to check the case stripe key is expired
                        // view.triggerMethod('submit:paymentFail', response);
                        // AE.pubsub.trigger('ae:notification', {
                        //         msg: response.msg,
                        //         notice_type: 'fail',
                        //     });
                        // return ;
                    }

                    if (response.data.ACK) {
                        // call method onSubmitPaymentSuccess
                        view.triggerMethod('submit:paymentSuccess', response);
                        // update form check out and submit
                        $('#checkout_form').attr('action', response.data.url);
                        if( $('#checkout_form .packageType').length >0 ){
                            $('#checkout_form .packageType').val(view.model.get('et_package_type'));
                        }
                        if (typeof response.data.extend !== "undefined") {
                            $('#checkout_form .payment_info').html('').append(response.data.extend.extend_fields);
                        }
                        // trigger click on submit button
                        $('#payment_submit').trigger('click');
                    } else {
                        // call method onSubmitPaymentFail
                        view.triggerMethod('submit:paymentFail', response);
                    }
                }
            });
        },
        initMap: function() {
            var view = this;
            if ($('#map').length > 0) {
                view.map = new GMaps({
                    div: '#map',
                    lat: ae_globals.map_center.latitude,
                    lng: ae_globals.map_center.latitude,
                    zoom: 1,
                    panControl: false,
                    zoomControl: true,
                    mapTypeControl: false
                });
                if ($('#et_location_lat').val() !== '' && $('#et_location_lng').val() !== '') {
                    var lat = $('#et_location_lat').val(),
                        lng = $('#et_location_lat').val();
                    view.map.setCenter(lat, lng);
                    view.map.addMarker({
                        lat: lat,
                        lng: lng,
                        draggable: true,
                        dragend: function(e) {
                            var location = e.latLng;
                            $('#et_location_lat').val(location.lat());
                            $('#et_location_lng').val(location.lng());
                            view.model.set('et_location_lat', location.lat());
                            view.model.set('et_location_lng', location.lng());
                        }
                    });
                }
            }
        },
        /**
         * init map gecode an address
         */
        gecodeMap: function(event) {
            var address = $(event.currentTarget).val(),
                view = this;
            //gmaps = new GMaps
            if (typeof(GMaps) !== 'undefined') GMaps.geocode({
                address: address,
                callback: function(results, status) {
                    if (status == 'OK') {
                        var latlng = results[0].geometry.location;
                        $('#et_location_lat').val(latlng.lat());
                        $('#et_location_lng').val(latlng.lng());
                        // set value to model
                        view.model.set('et_location_lng', latlng.lng());
                        view.model.set('et_location_lat', latlng.lat());
                        view.map.setZoom(15);
                        view.map.setCenter(latlng.lat(), latlng.lng());
                        view.map.removeMarkers();
                        view.map.addMarker({
                            lat: latlng.lat(),
                            lng: latlng.lng(),
                            draggable: true,
                            dragend: function(e) {
                                var location = e.latLng;
                                $('#et_location_lat').val(location.lat());
                                $('#et_location_lng').val(location.lng());
                                view.model.set('et_location_lat', location.lat());
                                view.model.set('et_location_lng', location.lng());
                            }
                        });
                    }
                }
            });
        },
        /**
         * show next step
         */
        showNextStep: function() {
            this.triggerMethod("before:showNextStep", this);
            var next = 'auth',
                view = this;
            view.$('.step-wrapper').removeClass('current');
            this.$('.content').slideUp(500, 'easeOutExpo', function() {
                // this.$('.content').;
                // current step is plan
                if (view.currentStep === 'plan') {
                    if (view.user_login) { // user login skip step auth
                        next = 'post';
                    }
                }
                // current step is auth
                if (view.currentStep == 'auth') {
                    // update user_login
                    view.user_login = true;
                    next = 'post';
                }
                // current step is post
                if (view.currentStep == 'post') {
                    view.user_login = true;
                    next = 'payment';
                }

                view.$('.step-' + view.currentStep + '  .content').closest('div.step-wrapper').addClass('complete');
                // show next step
                view.$('.step-' + next + '  .content').slideDown(10, 'easeOutExpo').end();
                view.$('.step-' + next).addClass('current');
            });
            /**
             * refresh map
             */
            if (typeof this.map !== 'undefined') {
                this.map.refresh();
            }
            // trigger onAfterShowNextStep
            view.triggerMethod("after:showNextStep", next, view.currentStep);
        },
        /**
         * user select a step
         */
        selectStep: function(event) {
            event.preventDefault();
            var $target = $(event.currentTarget),
                $wrapper = $target.parents('.step-wrapper'),
                view = this,
                select = $wrapper.attr('id');
            // step authentication
            if (select == 'step-auth') {
                if (this.finishStep.length < 1) return;
            }
            // step post
            if (select == 'step-post') {
                if ($('#step-auth').length > 0 && this.finishStep.length < 2) return;
                if ($('#step-auth').length == 0 && this.finishStep.length < 1) return;
            }
            // step payment
            if (select == 'step-payment') {
                if ($('#step-auth').length > 0 && this.finishStep.length < 3) return;
                if ($('#step-auth').length == 0 && this.finishStep.length < 2) return;
            }
            if (!$target.closest('div').hasClass('current')) {
                // trigger to call view beforeSelectStep
                this.triggerMethod('before:selectStep', $target);
                // toggle content of selected step
                view.$('.step-wrapper').removeClass('current');
                this.$('.content').slideUp(500, 'easeOutExpo');
                $target.closest('div').addClass('current').find('.content').slideDown(500, 'easeOutExpo');
                // trigger to call view afterSelectStep
                this.triggerMethod('after:selectStep', $target, this);
            }
        },
        /**
         * add a step to finish array
         */
        addFinishStep: function(step) {
            if (typeof this.finishStep === 'undefined') {
                this.finishStep = [];
            }
            $('#'+step).find('.number-step').html('<span class="fa fa-check"></span>');
            this.$('.' + step ).addClass('complete');
            this.finishStep.push(step);
        }
    });
    /**
     * modal contact message
     */
    Views.ContactModal = AE.Views.Modal_Box.extend({
        events: {
            'submit form#submit_contact': 'sendMessage',
        },
        initialize: function(options) {
            AE.Views.Modal_Box.prototype.initialize.call();
            this.blockUi = new AE.Views.BlockUi();
            this.options = _.extend(this, options);
            this.user = this.model;
        },
        sendMessage: function(event) {
            event.preventDefault();
            this.submit_validator = $("form#submit_contact").validate({
                rules: {
                    message: "required"
                }
            });
            var form = $(event.currentTarget),
                $button = form.find(".btn-submit"),
                data = form.serializeObject(),
                view = this;
            /**
             * scan all fields in form and set the value to model user
             */
            form.find('input, textarea, select').each(function() {
                view.user.set($(this).attr('name'), $(this).val());
            });
            this.model.set('send_to', view.user_id);
            if (this.submit_validator.form() && !form.hasClass("processing")) {
                this.user.set('do', 'inbox');
                this.user.request('fetch', {
                    beforeSend: function() {
                        view.blockUi.block($button);
                        form.addClass('processing');
                    },
                    success: function(result, status, jqXHR) {
                        form.removeClass('processing');
                        if (status.success) {
                            AE.pubsub.trigger('ae:notification', {
                                msg: status.msg,
                                notice_type: 'success',
                            });
                            view.closeModal();
                            form.trigger('reset');
                        } else {
                            AE.pubsub.trigger('ae:notification', {
                                msg: status.msg,
                                notice_type: 'error',
                            });
                            view.closeModal();
                        }
                        view.blockUi.unblock();
                    }
                });
            }
        }
    });
    // POST VIEW
})(window.AE, jQuery, Backbone, window.AE.Views, window.AE.Models, window.AE.Collections);
// build basic model
(function(AE, $, Backbone) {
    AE.Models.User = Backbone.Model.extend({
        action: 'ae-sync-user',
        initialize: function() {},
        request: function(method, options) {
            if (this.get('do') == "register" || method == "update") {
                this.save('', '', options);
            } else {
                this.fetch(options);
            }
        },
        resetpass: function(options) {
            this.save('do', 'resetpass', options);
        },
        confirmMail: function(options) {
            this.set('do', 'confirm_mail');
            this.fetch(options);
        }
    });
    AE.Models.Post = Backbone.Model.extend({
        action: 'ae-sync-post',
        initialize: function() {}
    });
    AE.Models.Comment = Backbone.Model.extend({
        action: 'ae-sync-comment',
        initialize: function() {}
    });
    /**
     * model favorite
     */
    AE.Models.Favorite = Backbone.Model.extend({
        action: 'ae-sync-favorite',
        initialize: function() {}
    });
    /*
     *
     * S K I L L  M O D E L
     *
     */
    AE.Models.Skill = Backbone.Model.extend({
        action: 'ae-skill-sync',
        initialize: function() {}
    });
})(window.AE, jQuery, Backbone);
// build basic collection
(function(AE, $, Backbone) {
    AE.Collections.Users = Backbone.Collection.extend({
        model: AE.Models.User,
        action: 'ae-fetch-users',
        initialize: function() {
            this.paged = 1;
        }
    });
    AE.Collections.Posts = Backbone.Collection.extend({
        model: AE.Models.Post,
        action: 'ae-fetch-posts',
        initialize: function() {
            this.paged = 1;
        }
    });
    AE.Collections.Comments = Backbone.Collection.extend({
        model: AE.Models.Comment,
        action: 'ae-fetch-comments',
        initialize: function() {
            this.paged = 1;
        }
    });
    AE.Collections.Blogs = Backbone.Collection.extend({
        model: AE.Models.Comment,
        action: 'ae-fetch-blogs',
        initialize: function() {
            this.paged = 1;
        }
    });
    /*
     *
     * S K I L L  C O L L E C T I O N S
     *
     */
    AE.Collections.Skills = Backbone.Collection.extend({
        model: AE.Models.Skill,
        action: 'ae-fetch-skills',
        initialize: function() {}
    });
})(window.AE, jQuery, Backbone, window.AE.Views);
