/**
 * Created by Jack Bui on 1/12/2016.
 */
(function ($, Models, Collections, Views) {
    //Scroll modal in step 2
    $('.modal').on('shown.bs.modal', function (e) {
        $('body').addClass('modal-open');
    });
    $(function () {
        //Add field timezone in form social login
        $('.social-auth form#form_username .social-form').append('<input type="hidden" name="timezone_local" class="timezone-local" value="">');
        var tz = jstz.determine();
        var timeZoneLocal = tz.name();
        $('.timezone-local').attr('value', timeZoneLocal);

        //popover-opening-message
        $('.popover-opening-message').popover({
            title: '<span class="pull-left icon-message"><i class="fa fa-info-circle" aria-hidden="true"></i></span><span class="pull-right text-message">' + ae_globals.title_popover_opening_message + '</span><span class="clearfix"></span>',
            content: '<img src="' + ae_globals.assetImg + 'opening-message-help.jpg" src="Opening Message" width="100%" height="auto">',
            trigger: 'hover',
            html: true,
            placement: 'auto top',
        });

        /**
         * MODELS
         */
        $('.et-pull-bottom').css("display", "block");
        /*Show tooltip*/
        $('[data-toggle="tooltip"]').tooltip();

        /*Show white space two line*/
        $(".trimmed").dotdotdot({
            watch: true,
            ellipsis: "...",
            height: "watch",
            wrap: 'letter'
        });
        $(".user-public-profile .list-job ul li .info-items h2").dotdotdot({
            watch: true,
            ellipsis: "...",
            height: "watch",
            wrap: 'letter'
        });

        /*button reject*/
        $('.btn-reject').click(function () {
            $('#reject').css("display", "block");
            $('.content-custom-order').css("display", "none");
            $('.outer-detail-custom-order').css("display", "none");
        });
        /** END CUSTOM ORDER **/

        /*Validation input text time*/
        $('.field-positive-int, .time-delivery, #from-budget, #from_deadline, #budget, #etd').on('keypress', function (event) {
            var keypressed = null;
            if (window.event) {
                keypressed = window.event.keyCode;
            }
            else {
                keypressed = event.which;
            }

            if (keypressed < 48 || keypressed > 57) {
                if (keypressed == 8 || keypressed == 127) {
                    return;
                }
                return false;
            }
        });

        jQuery(".order_person").change(function () {
            if (jQuery(this).is(':checked') && jQuery(this).hasClass('order_seller')) {
                jQuery(".select_order_change").fadeOut();
            }
            else {
                jQuery(".select_order_change").fadeIn();
            }
        })

        /*Focus searh form*/
        $(".new-search-link").click(function () {
            $("#input-search").select();
            $('html, body').animate({
                scrollTop: $("html, body").offset().top
            }, 1000);
        });

        /*Scroll link statistic job home*/
        $(".link-last-job").click(function () {
            $('html, body').animate({
                scrollTop: $(".block-items").offset().top
            }, 1000);
        });

        $(".et-pull-top ,#content, .slider").click(function () {
            $('.navbar-collapse').removeClass('in');
            $('.navbar-collapse').addClass('collapsed');
        });
        /*Hover dropdown menu*/
        var windowSize = $(window).width();
        if (windowSize > 992) {
            $('#et-nav .dropdown').hover(function () {
                $('.dropdown-toggle', this).trigger('click');
            });
        }
        else {
            // remove dropdown if small screen.
            $('.message-icon a.dropdown-toggle').removeAttr('data-toggle');
        }
        /*Scroll header home*/
        $(window).on('scroll', function () {
            //scroll header
            var form_search = $(".form-search");
            if (form_search.length) {
                var form_search_top = form_search.offset().top;
                if ($(window).scrollTop() > form_search_top) {
                    $('.search-bar').css("display", "inline-block");
                } else {
                    $('.search-bar').css("display", "none");
                }
            }
        });
        /*Check show arrows if two image*/
        var count = $(".carousel-indicators li").length;
        if (count <= 1) {
            $('.carousel-indicators').hide();
            $('.carousel-control').hide();
        } else {
            $('.carousel-indicators').show();
            $('.carousel-control').show();
        }

        /*Menu navigation long*/
        navigationResize();
        $(window).resize(function () {
            navigationResize();
        });

        function navigationResize() {
            var $navItemMore = $('#nav > li.more'),
                $navItems = $('#nav > li:not(.more)'),
                navItemWidth = $navItemMore.width(),
                windowWidth = $(window).width();
            if (window.innerWidth > 991) {
                //$('#nav li.more').before($('#overflow > li'));

                $navItems.each(function () {
                    navItemWidth += $(this).width();
                });

                navItemWidth > windowWidth ? $navItemMore.show() : '';

                while (navItemWidth > windowWidth) {
                    navItemWidth -= $navItems.last().width();
                    $navItems.last().prependTo('#overflow');
                    $navItems.splice(-1, 1);
                }
            } else {
                navItemWidth > windowWidth ? $navItemMore.show() : $navItemMore.hide();
            }
        }
        $(".navbar-nav li").hover(function (e) {
            if ($('ul', this).length) {
                var docW = $("body").width();
                var liRight = docW - ($(this).width() + $(this).offset().left);
                var ulWidth = $(this).find('.dropdown-menu').width();
                var isEntirelyVisible = (ulWidth > liRight);
                if (isEntirelyVisible) {
                    $(this).addClass('move-right');
                    $(this).find('.dropdown-menu').css({ right: liRight });
                }
            }
        });
        $('#et-nav .navbar-toggle').click(function (event) {
            event.preventDefault();
            $('.overlay-nav').show();
            $('body').css('cssText', 'overflow:hidden !important; position:fixed');
            $('#et-nav .navbar-collapse.collapse').show();
        });
        $('.overlay-nav').click(function () {
            $('.overlay-nav').hide();
            $('#et-nav .navbar-collapse.collapse').hide();
            $('body').css('cssText', 'overflow:auto !important; position:relative');
        });

        /*Animation scroll*/
        wow = new WOW(
            {
                animateClass: 'animated',
                offset: 100,
            }
        );
        wow.init();


        Models.mJobUser = Backbone.Model.extend({
            action: 'mjob_sync_user'
        });

        /**
         * model mjob
         */
        /*Fixed height banner home*/
        var header = $('.et-pull-bottom').outerHeight();
        var admin_bar = $('body').hasClass('admin-bar');
        var wpadminbar = 0;
        if (admin_bar) {
            wpadminbar = $('#wpadminbar').outerHeight();
            if ($('#wpadminbar').hasClass('mobile') && ($('#wpadminbar').outerWidth() < 992)) {
                wpadminbar = 0
            }
        }
        $(window).resize(function (event) {
            var header = $('.et-pull-bottom').outerHeight();
            var wpadminbar = 0;
            if (admin_bar) {
                wpadminbar = $('#wpadminbar').outerHeight();
                if ($('#wpadminbar').hasClass('mobile') && ($('#wpadminbar').outerWidth() < 992)) {
                    wpadminbar = 0
                }
            }
            jQuery('.slider').css({
                height: (jQuery(window).height() - 85 - header - wpadminbar) + 'px',
                'display': 'block'
            });
        });

        jQuery('.slider').css({
            height: (jQuery(window).height() - 85 - header - wpadminbar) + 'px',
            'display': 'block'
        });
        /*Accordion menu*/
        var Accordion = function (el, multiple) {
            this.el = el || {};
            this.multiple = multiple || false;

            var links = this.el.find('.link');
            // Evento
            links.on('click', { el: this.el, multiple: this.multiple }, this.dropdown)
        }

        Accordion.prototype.dropdown = function (e) {
            var $el = e.data.el;
            $this = $(this),
                $next = $this.next();

            $next.slideToggle();
            $this.parent().toggleClass('open');

            if (!e.data.multiple) {
                $el.find('.submenu').not($next).slideUp().parent().removeClass('open');
            };
        }
        var accordion = new Accordion($('#accordion'), false);

        // Prevent dropdown menu hidden when click on accordion
        $('.dropdown').on('click', '.show-accordion', function (event) {
            event.preventDefault();
            event.stopPropagation();
        });

        Models.Mjob = Backbone.Model.extend({
            action: 'ae-mjob_post-sync',
            initialize: function () { }
        });
        /**
         * mjob collections
         */
        Collections.Mjob = Backbone.Collection.extend({
            model: Models.Mjob,
            action: 'ae-fetch-mjob_post',
            initialize: function () {
                this.paged = 1;
            }
        });
        /**
         * define mjob item view
         */
        var mjobItem = Views.PostItem.extend({
            tagName: 'li',
            className: 'col-lg-4 col-md-4 col-sm-6 col-xs-6 col-mobile-12 item_js_handle',
            template: _.template($('#mjob-item-loop').html()),
            initialize: function () {
                this.renderRating();
            },
            onItemBeforeRender: function () {
                // before render view
                if (this.model.get("et_featured") == '1')
                    this.$el.addClass("featured-item"); // 1.3.8
            },
            onItemRendered: function () {
                $("body").tooltip({ selector: '[data-toggle=tooltip]' });
                var view = this;
                if (view.$el.find('input[name="_wpnonce"]').length > 0) {
                    view.model.set('_wpnonce', view.$el.find('input[name="_wpnonce"]').val());
                }

                this.renderRating();
                this.$el.find(".trimmed").dotdotdot({
                    watch: true,
                    height: "watch",
                    wrap: 'letter',
                    ellipsis: "...",
                });
            },

            // Render rating star
            renderRating: function () {
                var view = this;
                this.$el.find('.rate-it').raty({
                    readOnly: true,
                    half: true,
                    score: function () {
                        return view.model.get('rating_score');
                    },
                    hints: raty.hint
                });
            }
        });
        /**
         * list view control mjob list
         */
        ListMjobs = Views.ListPost.extend({
            tagName: 'ul',
            itemView: mjobItem,
            itemClass: 'item_js_handle'
        });
        $('.mjob-container-control').each(function () {
            if (typeof mjobCollection == 'undefined') {
                //Get public  collection
                if ($('.mJob_postdata').length > 0) {
                    var mjob = JSON.parse($('.mJob_postdata').html());
                    mjobCollection = new Collections.Mjob(mjob);
                } else {
                    mjobCollection = new Collections.Mjob();
                }
            }
            var skills = new Collections.Skills();
            /**
             * init list blog view
             */
            new ListMjobs({
                itemView: mjobItem,
                collection: mjobCollection,
                el: $(this).find('.list-mjobs')
            });
            //post-type-archive-project
            //old block-projects
            /**
             * init block control list blog
             */
            new Views.BlockControl({
                //events: function() {
                //    return _.extend({
                //        'click .custom-filter-query a': 'customFilter'
                //    }, Views.BlockControl.prototype.events);
                //},
                thumbnail: "medium_post_thumbnail",
                collection: mjobCollection,
                skills: skills,
                el: $(this),
                // Categories accorder
                //customFilter: function(event) {
                //    event.preventDefault();
                //    var view = this;
                //    this.customFilter = true;
                //    var $target = $(event.currentTarget),
                //        name = $target.attr('data-name'),
                //        value = $target.attr('data-value'),
                //        liveUpdateEl = view.$el.find('.block-title .block-title-text');
                //
                //    // Add class active
                //    $('.custom-filter-query a').removeClass('active');
                //    $target.addClass('active');
                //
                //    // Add class active for parents
                //    $('#accordion li').removeClass('active');
                //    $target.parents('.open').addClass('active');
                //
                //    if (name !== 'undefined') {
                //        //view.router.navigate($target.attr('href'));
                //        window.history.pushState('', '', $target.attr('href'));
                //
                //        // update title
                //        //liveUpdateEl.text($target.text());
                //
                //        view.query[name] = value;
                //        view.page = 1;
                //        // fetch page
                //        view.fetch($target);
                //    }
                //    return false;
                //},
                onAfterFetch: function (result, res, $target) {
                    var view = this;
                    // Update block title if is custom filter category and skill
                    var liveUpdateEl = view.$el.find('.block-title .block-title-text');
                    var resultText = '';
                    if (view.customFilter == true) {
                        var prefix = liveUpdateEl.attr('data-prefix');
                        liveUpdateEl.find('.search-result-count').text(res.total);
                        liveUpdateEl.find('.term-name').text(prefix + " " + $target.text());
                        this.customFilter = false;
                    }
                    if (res.success) {
                        if (ae_globals.is_search || ae_globals.is_tax_mjob_category || ae_globals.is_tax_skill) {
                            liveUpdateEl.find('.search-result-count').text(res.total);
                            if (res.total <= 1) {
                                resultText = ae_globals.result;
                            } else {
                                resultText = ae_globals.results;
                            }
                            liveUpdateEl.find('.search-text-result').text(resultText);
                        }
                        if ($('.not-found').length > 0) {
                            $('.not-found').remove();
                        }
                        if (res.data.length == 0) {
                            if ($('.my-list-mjobs').length > 0) {
                                $('.list-mjobs').html(ae_globals.no_mjobs);
                            } else {
                                $('.list-mjobs').html(ae_globals.no_services);
                            }
                        }

                    } else {
                        if (ae_globals.is_search || ae_globals.is_tax_mjob_category || ae_globals.is_tax_skill) {
                            liveUpdateEl.find('.search-result-count').text(0);
                        }
                        if ($('.my-list-mjobs').length > 0) {
                            $('.list-mjobs').html(ae_globals.no_mjobs);
                        } else {
                            $('.list-mjobs').html(ae_globals.no_services);
                        }
                    }

                    $('.mjob-container-control .dropdown').removeClass('open');
                    if (ae_globals.is_phone) {
                        var _hasfilter = $('body').hasClass('filter-open');
                        if (_hasfilter) {
                            $(".filter-open-btn").trigger("click");
                        }
                    }
                }
            });
        });
        /*
         * Model extra
         */
        Models.Extra = Backbone.Model.extend({
            action: "ae-mjob_extra-sync",
            defaults: {
                post_type: 'mjob_extra'
            }
        });
        /**
         * Extras list in single mjob page
         *
         * @param void
         * @return void
         * @since 1.0
         * @package MicrojobEngine
         * @category void
         * @author JACK BUI
         */
        Collections.Extras = Backbone.Collection.extend({
            model: Models.Extra,
            action: 'ae-fetch-mjob_extra',
            initialize: function () {
                this.paged = 1;
            }
        });
        /*
         * model order
         */
        Models.Order = Backbone.Model.extend({
            action: "mje_checkout_product",
            defaults: {
                p_data: null,
                p_type: '',
                p_total: '',
                p_payment: '',
                p_nonce: ''
            }
        });
        /*
         * Extra item
         */
        Views.ExtraItemView = Views.PostItem.extend({
            tagName: 'div',
            className: 'form-group row clearfix job-items extra-item',
            template: _.template($('#mjobExtraItem').html()),
            events: _.extend({
                'click .mjob-remove-extra-item': 'deleteItem',
                'keypress .text-currency': 'numbercurrency',
            }, Views.PostItem.prototype.events),
            initialize: function (options) {
                _.extend(this, options);
                Views.PostItem.prototype.initialize.call(this, options);
            },
            onItemRendered: function () {
                var view = this;
                view.$el.find('input[name="et_budget"]').attr('id', 'et_budget_' + this.model._listenId);
                view.$el.find('input[name="post_title"]').attr('id', 'et_extra_' + this.model._listenId);
            },
            deleteItem: function (e) {
                var view = this;
                e.preventDefault();
                this.model.set('_wpnonce', view.$el.find('input[name="_wpnonce"]').val());
                this.model.destroy();
            },
            syncChange: function () {
                var view = this;
                view.$el.find('input,textarea,select').each(function () {
                    view.model.set($(this).attr('name'), $(this).val());
                });
                var is_changed = true;
                is_changed = is_changed || this.model.hasChanged();
                if (is_changed) {
                    this.model.save('', '', {
                        beforeSend: function () {

                        },
                        success: function (result, res, jqXHR) {
                            if (res.success) {
                                AE.pubsub.trigger('mjob:after:sync:extra', result, res, jqXHR);
                            }
                        }
                    });
                }

            },
            numbercurrency: function (event) {
                var keypressed = null;
                if (window.event) {
                    keypressed = window.event.keyCode;
                }
                else {
                    keypressed = event.which;
                }

                if (keypressed < 48 || keypressed > 57) {
                    if (keypressed == 8 || keypressed == 127) {
                        return;
                    }
                    return false;
                }
            }
        });
        /**
         * list extras
         */
        Views.ExtrasListView = Backbone.Marionette.CollectionView.extend({
            tagName: 'div',
            itemView: Views.ExtraItemView,
            itemClass: 'extra-item',
            syncChange: function () {
                var view = this;
                this.children.each(function (child) {
                    child.syncChange();
                });
            }
        });
        //blog code
        /**
         * define blog item view
         */
        BlogItem = Views.PostItem.extend({
            tagName: 'li',
            className: 'post-item clearfix',
            template: _.template($('#ae-post-loop').html()),
            onItemBeforeRender: function () {
                // before render view
            },
            onItemRendered: function () {
                // after render view
            }
        });
        /**
         * list view control blog list
         */
        ListBlogs = Views.ListPost.extend({
            tagName: 'div',
            itemView: BlogItem,
            itemClass: 'post-item'
        });
        // blog list control
        if ($('#posts_control').length > 0) {
            if ($('#posts_control').find('.postdata').length > 0) {
                var postsdata = JSON.parse($('#posts_control').find('.postdata').html()),
                    posts = new Collections.Blogs(postsdata);
            } else {
                posts = new Collections.Blogs();
            }
            /**
             * init list blog view
             */
            new ListBlogs({
                itemView: BlogItem,
                collection: posts,
                el: $('#posts_control').find('.post-list')
            });
            /**
             * init block control list blog
             */
            new Views.BlockControl({
                collection: posts,
                el: $('#posts_control')
            });
        }
        Views.AE_Form = Backbone.View.extend({
            events: {
                'submit form': 'submitForm'
            },
            initialize: function (options) {
                this.options = _.extend(this, options);
                this.blockUi = new Views.BlockUi();
                if (typeof this.type === 'undefined') {
                    this.type = 'aeForm';
                }

                if (typeof this.showNotice === "undefined") {
                    this.showNotice = true;
                }
            },
            resetModel: function (model) {
                this.model = model;
            },
            submitForm: function (event) {
                event.preventDefault();
                var view = this,
                    temp = new Array(),
                    $target = $(event.currentTarget);
                view.initValidate();
                var tempModel = view.model;
                /**
                 * update model from input, textarea, select
                 */
                view.$el.find('input,textarea,select').each(function () {
                    tempModel.set($(this).attr('name'), $(this).val());
                });

                view.$el.find('input[type=checkbox]').each(function () {
                    var name = $(this).attr('name');
                    tempModel.set(name, []);
                });
                /**
                 * update input check box to model
                 */
                view.$el.find('input[type=checkbox]:checked').each(function () {
                    var name = $(this).attr('name');
                    if (typeof temp[name] !== 'object') {
                        temp[name] = new Array();
                    }
                    temp[name].push($(this).val());
                    tempModel.set(name, temp[name]);
                });
                /**
                 * update input radio to model
                 */
                view.$el.find('input[type=radio]:checked').each(function () {
                    tempModel.set($(this).attr('name'), $(this).val());
                });

                if (typeof view.fields != 'undefined' && view.fields.length > 0) {
                    for (i = 0; i < view.fields.length; i++) {
                        if (typeof tempModel.get(view.fields[i]) !== 'undefined') {
                            view.model.set(view.fields[i], tempModel.get(view.fields[i]));
                        }
                    }
                }
                else {
                    view.model = tempModel;
                }
                if (view.form_validator.form()) {
                    var response_option = {
                        beforeSend: function () {
                            if ("undefined" !== typeof view.blockTarget && "" != view.blockTarget) {
                                view.blockUi.block($(view.blockTarget));
                            } else {
                                view.blockUi.block($target);
                            }
                        },
                        success: function (result, resp, jqXHR) {
                            AE.pubsub.trigger('ae:form:submit:success', result, resp, jqXHR, view.type);
                            if (view.showNotice == true) {
                                if (resp.success) {
                                    AE.pubsub.trigger('ae:notification', {
                                        msg: resp.msg,
                                        notice_type: 'success'
                                    });
                                    view.data = resp.data;
                                }
                                else {
                                    AE.pubsub.trigger('ae:notification', {
                                        msg: resp.msg,
                                        notice_type: 'error'
                                    });
                                }
                            }
                            //@todo comment de style loading
                            view.blockUi.unblock();
                        }
                    };
                    if (view.model.get('method') == 'read') {
                        view.model.request('read', response_option)
                    } else {
                        view.model.save('', '', response_option);
                    }
                }
            },
            initValidate: function () {
                var view = this;
                view.form_validator = view.$el.find('form').validate({
                    errorElement: "p",
                    rules: view.rules,
                    messages: view.messages,
                    highlight: function (element, errorClass, validClass) {
                        var $target = $(element);
                        var $parent = $(element).parent();
                        $parent.addClass('has-error');
                        $target.addClass('has-visited');
                    },
                    unhighlight: function (element, errorClass, validClass) {
                        // position error label after generated textarea
                        var $target = $(element);
                        var $parent = $(element).parent();
                        $parent.removeClass('has-error');
                        $target.removeClass('has-visited');
                    }
                });
            },
        })
        /*
         *
         * F R O N T  V I E W S
         *
         */
        Views.Front = Backbone.View.extend({
            el: 'body',
            model: [],
            events: {
                'click .full-text .show-less': 'showLessDescription',
                'click .trim-text .show-more': 'showMoreDescription',
                'submit .ae-gdpr-form-js': 'submitGDPR',
            },
            initialize: function (options) {

                document.querySelectorAll('.chosen-single').forEach((el) => {
                    let settings = {
                        maxItems: 1,
                        copyClassesToDropdown: true,
                        render: {
                            option: function (data, escape) {
                                return '<div class="' + escape(data.$option.className) + '">' + escape(data.text) + '</div>';
                            },
                        }
                    };
                    new TomSelect(el, settings);
                });

                /* init scrollbar for elements on page */

                if ($(".wrapper-list-conversation").length > 0) {
                    this.osConversation = $(".wrapper-list-conversation").OverlayScrollbars();
                }
                document.querySelectorAll('.list-custom-order-wrapper, .outer-detail-custom').forEach((el) => {
                    $(el).OverlayScrollbars({
                        overflow: {
                            x: 'hidden',
                            y: 'scroll',
                        },
                    });
                });

                document.querySelectorAll('.multi-tax-item').forEach((el) => {
                    let settings = {
                        maxItems: parseInt(ae_globals.max_cat),
                        copyClassesToDropdown: true
                    };
                    new TomSelect(el, settings);
                });
                this.blockUi = new Views.BlockUi();
                /*$( ".chosen-results" ).wrapAll( "<div class='chosen-results'></div>" );
                $('.chosen-drop ul').removeClass('chosen-results');*/
                // Trigger show notification
                AE.pubsub.on('ae:notification', this.showNotice, this);
                // Trigger live update user info
                AE.pubsub.on('mjob:update:user', this.updateUser, this);
                // Notice template
                this.noti_templates = new _.template('<div class="notification autohide {{= type }}-bg">' + '<div class="main-center">' + '{{= msg }}' + '</div>' + '</div>');
                //catch action reject project
                AE.pubsub.on('ae:model:onReject', this.rejectPost, this);
                AE.pubsub.on("ae:delete:success", this.afterDelete, this);
                // Rating score
                $('.rate-it').each(function () {
                    $('.rate-it').raty({
                        readOnly: true,
                        half: true,
                        score: function () {
                            return $(this).attr('data-score');
                        },
                        hints: raty.hint
                    });
                });
                AE.pubsub.on('ae:model:onpause', this.pauseMjob, this);
                AE.pubsub.on('ae:model:onunpause', this.unPauseMjob, this);
                AE.pubsub.on('ae:openRejectModal', this.clearContent, this);

                $("body").tooltip({ selector: '[data-toggle=tooltip]' });
                $(".user-account-dropdown").on("show.bs.dropdown", function (event) {
                    $('body').addClass('user-account-open');
                });
                $(".user-account-dropdown").on("hidden.bs.dropdown", function (event) {
                    $('body').removeClass('user-account-open');
                });
                $(".menu-navbar-toggle").click(function () {
                    $('body').toggleClass('menu-nav-open');
                });

                $(document.body).on('click', function (event) {
                    if (!$(event.target).closest('.menu-navbar-toggle').length) {
                        $('body').removeClass('menu-nav-open');
                    }
                });

                $(".toggle-option").change(function (event) {
                    event.preventDefault();
                    var target = $(event.currentTarget),
                        name = target.attr('name'),
                        value = target.is(':checked');
                    $.ajax({
                        type: "post",
                        url: ae_globals.ajaxURL,
                        dataType: 'json',
                        data: { name: name, value: value, action: 'ae_update_subscriber' },

                        beforeSend: function () {
                            //view.blockUi.block(button);

                        },
                        success: function (data, status, xhr) {

                            //view.blockUi.unblock();

                            if (data.success) {
                                AE.pubsub.trigger('ae:notification', {
                                    msg: data.msg,
                                    notice_type: 'success'
                                });
                            } else {
                                AE.pubsub.trigger('ae:notification', {
                                    msg: data.msg,
                                    notice_type: 'error'
                                });
                            }
                        }
                    });
                    return false;

                })

            },
            afterDelete: function (result, res, xhr) {
                AE.pubsub.trigger('ae:notification', {
                    msg: res.msg,
                    notice_type: 'success'
                });
            },
            showNotice: function (params) {
                const noticeTimeOut = ('undefined' !== typeof params.timeOut) 
                    ? params.timeOut
                    : 4000;
                const noticeCloseButton = ('undefined' !== typeof params.closeButton)
                    ? params.closeButton
                    : true;
                toastr.options = {
                    closeButton: noticeCloseButton,
                    showMethod: 'fadeIn',
                    newestOnTop: true,
                    timeOut: noticeTimeOut,
                };
                switch (params.notice_type) {
                    case "success":
                        toastr.success(params.msg);
                        break;
                    case "error":
                        toastr.error(params.msg);
                        break;
                    case "warning":
                        toastr.warning(params.msg);
                        break;
                    case "info":
                    default:
                        toastr.info(params.msg);
                        break;
                }
            },
            updateUser: function (params) {
                var accountHeader = $('#mjob_my_account_header');
                var myAccount = $('#myAccount .user-account');
                if (accountHeader.length > 0) {
                    var template = _.template(accountHeader.html());
                    myAccount.html(template({
                        avatar: '<img src="' + params.avatar + '" />',
                        display_name: params.display_name
                    }));
                }
            },
            /**
             * setup reject post modal and trigger event open modal reject
             */
            rejectPost: function (model) {
                if (typeof this.rejectModal === 'undefined') {
                    this.rejectModal = new Views.RejectPostModal({
                        el: $('#reject_post'),
                        target: $('.mjob-button-reject'),
                    });
                }
                this.rejectModal.onReject(model);
            },
            pauseMjob: function (model, target) {
                var view = this;
                model.set('pause', 1);
                model.save('pause', '1', {
                    beforeSend: function () {
                        view.blockUi.block(target)
                    },
                    success: function (result, res, xhr) {
                        if (res.success) {
                            AE.pubsub.trigger('ae:notification', {
                                msg: res.msg,
                                notice_type: 'success'
                            });
                        } else {
                            AE.pubsub.trigger('ae:notification', {
                                msg: res.msg,
                                notice_type: 'error'
                            });
                        }
                        view.blockUi.unblock();

                    }
                });
            },
            clearContent: function (model) {
                var view = this;
                view.$el.find('textarea[name="reject_message"]').val('');
            },
            unPauseMjob: function (model, target) {
                var view = this;
                if (typeof model.get('pause') !== 'undefined') {
                    model.unset('pause');
                }
                model.set('unpause', 1);
                model.save('unpause', '1', {
                    beforeSend: function () {
                        view.blockUi.block(target)
                    },
                    success: function (result, res, xhr) {
                        if (res.success) {
                            AE.pubsub.trigger('ae:notification', {
                                msg: res.msg,
                                notice_type: 'success'
                            });
                            model.unset('unpause');
                        } else {
                            AE.pubsub.trigger('ae:notification', {
                                msg: res.msg,
                                notice_type: 'error'
                            });
                        }
                        view.blockUi.unblock();

                    }
                });
            },
            number_commission_for_buyer: function (number) {
                var commission = ae_globals.fee_order_buyer;
                var price = 0;
                if (commission > 0) {
                    return number + commission * number / 100;
                }
                return number;
            },
            number_format: function (number, decimals, dec_point, thousands_sep) {
                number = (number + '')
                    .replace(/[^0-9+\-Ee.]/g, '');
                var n = !isFinite(+number) ? 0 : +number,
                    prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                    sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
                    dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
                    s = '',
                    toFixedFix = function (n, prec) {
                        var k = Math.pow(10, prec);
                        return '' + (Math.round(n * k) / k)
                            .toFixed(prec);
                    };
                // Fix for IE parseFloat(0.55).toFixed(0) = 0;
                s = (prec ? toFixedFix(n, prec) : '' + Math.round(n))
                    .split('.');
                if (s[0].length > 3) {
                    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
                }
                if ((s[1] || '')
                    .length < prec) {
                    s[1] = s[1] || '';
                    s[1] += new Array(prec - s[1].length + 1)
                        .join('0');
                }
                return s.join(dec);
            },
            mJobPriceFormat: function (amount, style) {
                if (typeof style == "undefined") {
                    style = "sup";
                }

                var amount_text = this.number_format(amount, ae_globals.decimal, ae_globals.decimal_point, ae_globals.thousand_sep);
                switch (style) {
                    case 'sup':
                        format = '<sup>' + ae_globals.mjob_currency.icon + '</sup>';
                        break;

                    case 'sub':
                        format = '<sub>' + ae_globals.mjob_currency.icon + '</sub>';
                        break;

                    default:
                        format = ae_globals.mjob_currency.icon;
                        break;
                }
                align = 0;
                if (typeof ae_globals.mjob_currency !== 'undefined') {
                    var align = parseInt(ae_globals.mjob_currency.align);
                }
                if (align) {
                    var price = format + amount_text;
                } else {
                    var price = amount_text + format;
                }
                return price;
            },
            showLessDescription: function (e) {
                e.preventDefault();
                $('.full-text').addClass('hide');
                $('.trim-text').removeClass('hide');
            },
            showMoreDescription: function (e) {
                e.preventDefault();
                $('.full-text').removeClass('hide');
                $('.trim-text').addClass('hide');
            },
            submitGDPR: function (event) {
                event.preventDefault();
                var form = $(event.currentTarget),
                    data = {},
                    button = form.find('.btn-submit'),
                    blockUi = new AE.Views.BlockUi(),
                    view = this;

                form.find('input, textarea').each(function () {

                    data[$(this).attr('name')] = $(this).val();

                });
                $.ajax({
                    type: "post",
                    url: ae_globals.ajaxURL,
                    dataType: 'json',
                    data: {
                        data: data,
                        action: 'ae-submit-gdpr',
                    },
                    beforeSend: function () {
                        blockUi.block(button);
                        form.addClass('processing');
                    },
                    success: function (data, status, xhr) {

                        blockUi.unblock();

                        if (data.success) {

                            AE.pubsub.trigger('ae:notification', {
                                msg: data.msg,
                                notice_type: 'success'
                            });
                        } else {
                            AE.pubsub.trigger('ae:notification', {
                                msg: data.msg,
                                notice_type: 'error'
                            });
                        }
                    }
                });
                return false;
            },
        });

        // init an overlayScrollbar to a jQuery element
        jQuery.fn.OverlayScrollbars = function (opts) {
            if (OverlayScrollbarsGlobal) {
                let self = this;
                const { OverlayScrollbars } = OverlayScrollbarsGlobal;
                this.addClass('os-theme-thin-dark');
                const scrollBarOpts = Object.assign({
                    className: "os-theme-thin-dark",
                    scrollbars: {
                        theme: "os-theme-thin-dark",
                        autoHide: 'scroll',
                    }
                }, opts);
                this.osInstance = OverlayScrollbars(this[0], scrollBarOpts);
                const { viewport } = this.osInstance.elements();
                this.osViewport = viewport;
                this.scrollToBottom = function () {
                    self.osInstance.update(true);
                    const { scrollCoordinates } = self.osInstance.state();
                    self.osViewport.scrollTo({
                        top: scrollCoordinates.end.y,
                        behavior: "smooth",
                    }); // set scroll offset
                }
                this.scrollToBottom();
            }
            return this;
        }

        // Serialize object
        jQuery.fn.serializeObject = function () {
            var self = this,
                json = {},
                push_counters = {},
                patterns = {
                    "validate": /^[a-zA-Z][a-zA-Z0-9_]*(?:\[(?:\d*|[a-zA-Z0-9_]+)\])*$/,
                    "key": /[a-zA-Z0-9_]+|(?=\[\])/g,
                    "push": /^$/,
                    "fixed": /^\d+$/,
                    "named": /^[a-zA-Z0-9_]+$/
                };

            this.build = function (base, key, value) {
                base[key] = value;
                return base;
            };

            this.push_counter = function (key) {
                if (push_counters[key] === undefined) {
                    push_counters[key] = 0;
                }
                return push_counters[key]++;
            };

            jQuery.each(jQuery(this).serializeArray(), function () {
                // skip invalid keys
                if (!patterns.validate.test(this.name)) {
                    return;
                }
                var k,
                    keys = this.name.match(patterns.key),
                    merge = this.value,
                    reverse_key = this.name;

                while ((k = keys.pop()) !== undefined) {
                    // adjust reverse_key
                    reverse_key = reverse_key.replace(new RegExp("\\[" + k + "\\]$"), '');
                    // push
                    if (k.match(patterns.push)) {
                        merge = self.build([], self.push_counter(reverse_key), merge);
                    }
                    // fixed
                    else if (k.match(patterns.fixed)) {
                        merge = self.build([], k, merge);
                    }
                    // named
                    else if (k.match(patterns.named)) {
                        merge = self.build({}, k, merge);
                    }
                }
                json = jQuery.extend(true, json, merge);
            });
            return json;
        };
    });
})(jQuery, window.AE.Models, window.AE.Collections, window.AE.Views);

