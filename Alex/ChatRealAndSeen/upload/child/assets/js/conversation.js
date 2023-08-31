(function($, Views, Models, Collections, AE) {

    $(document).ready(function() {

        Models.mJobConversation = Backbone.Model.extend({

            action: 'mjob_conversation_sync'

        });



        /**

         * CONVERSATION ITEM VIEW

         */

        var conversationItem = Views.PostItem.extend({

            tagName: 'li',

            className: 'clearfix conversation-item',

            template: _.template($('#conversation-item-loop').html())

        });



        Views.ConversationList = Views.ListPost.extend({

            tagName: 'ul',

            itemClass: 'history-item'

        });



        var conversationContainer = $('.mjob_conversation_list_page');

        if(conversationContainer.length > 0) {

            if(typeof conversationCollection === "undefined") {

                if($('.conversation_postdata').length > 0) {

                    var conversation = JSON.parse($('.conversation_postdata').html());

                    conversationCollection = new Collections.Message(conversation);

                } else {

                    conversationCollection = new Collections.Message();

                }

            }



            // Conversation list view

            var conversationList = new Views.ConversationList({

                itemView: conversationItem,

                collection: conversationCollection,

                el: conversationContainer.find('.list-conversation')

            });



            // Conversation block control

            new Views.BlockControl({

                collection: conversationCollection,

                el: conversationContainer,

            })

        }



        /**

         * BLOCK CONTROL FOR MESSAGES LIST IN CONVERSATION DETAIL

         */

        var messageItem = Views.PostItem.extend({

            tagName: 'li',

            className: 'clearfix message-item',

            template: _.template($('#message-item-loop').html())

        });



        Views.MessageList = Views.ListPost.extend({

            tagName: 'ul',

            itemView: messageItem,

            itemClass: 'message-item',

            initialize: function(options) {

                _.extend(this, options);

                Views.ListPost.prototype.initialize.call(this, options);

            },

        });



        var messageContainer = $('.conversation-form');

        if(messageContainer.length > 0) {

            if(typeof messageCollection === "undefined") {

                if($('.message_postdata').length > 0) {

                    var messages = JSON.parse($('.message_postdata').html());

                    messageCollection = new Collections.Message(messages);

                } else {

                    messageCollection = new Collections.Message();

                }

            }



            // Message list view

            var messageList = new Views.MessageList({

                itemView: messageItem,

                collection: messageCollection,

                el: messageContainer.find('.list-conversation'),

                appendHtml: function(collectionView, itemView, index){

                    collectionView.$el.prepend(itemView.el);

                }

            });



            // Message block control

            new Views.BlockControl({

                collection: messageCollection,

                el: messageContainer

            })

        }



        /**

         * CONVERSATION MODAL

         */

        Views.ModalConversation = Views.Modal_Box.extend({

            el: '#conversation',

            events: {



            },

            initialize: function() {

                AE.Views.Modal_Box.prototype.initialize.call();

                if(typeof this.model === 'undefined') {

                    this.model = new Models.Message();

                }



                AE.pubsub.on('ae:form:submit:success', this.sendMessageSuccess, this);

            },

            onOpen: function(data) {

                var view = this;

                view.model.set('type', 'conversation');

                view.model.set('from_user', data.from_user);

                view.model.set('to_user', data.to_user);

                view.model.set('is_conversation', 1);

                view.model.set('conversation_status', 'unread');

                view.model.set('post_title', conversation_global.conversation_title);

                view.setupFields();

                view.openModal();

            },

            setupFields: function() {

                var view = this;



                if (typeof view.carousels === 'undefined') {

                    view.carousels = new Views.Carousel({

                        el: $('.gallery_container_modal_conversation'),

                        uploaderID:'modal_conversation',

                        model: view.model,

                        carouselTemplate: '#ae_carousel_file_template',

                        extensions: ae_globals.file_types

                    });

                }



                if(typeof view.conversationForm === 'undefined') {

                    view.conversationForm = new Views.AE_Form({

                        el: '.mjob-modal-conversation-form', // Wrapper of form

                        model: this.model,

                        rules: {

                            conversation_content: 'required'

                        },

                        type: 'conversation',

                        blockTarget: '.mjob-modal-conversation-form button'

                    })

                }

            },

            sendMessageSuccess: function(result, resp, jqXHR, type) {



                // Show attach button

                $('.plupload_buttons').show();



                if(type == 'conversation') {

                    var view = this;

                    if(resp.success == true) {

                        // Update contact link

                        $('.contact-link').removeClass('do-contact');

                        $('.contact-link').attr('href', resp.data.permalink);



                        // Reset form

                        view.$el.find('#post_content').val('');

                        view.$el.find('.gallery-image').html('');



                        window.location.href = resp.data.permalink;

                    }

                    view.closeModal();

                }



            }

        });



        Views.Conversation = Backbone.View.extend({

            el: 'body',

            events: {

                'click .do-contact' : 'doContact',

                'click .mark-as-read' : 'doMarkAsRead'

            },

            initialize: function() {

                if($('#current_user').length > 0) {

                    if(typeof currentUser.data !== "undefined") {

                        this.user = new Models.mJobUser(currentUser.data);

                    } else {

                        this.user = new Models.mJobUser(currentUser);

                    }

                } else {

                    this.user = new Models.mJobUser();

                }



                this.model = new Models.Message();

                this.conversationObj = new Models.mJobConversation();

            },

            doContact: function(event) {

                event.preventDefault();

                var toUser = $(event.currentTarget).attr('data-touser');



                // Check if user logged in or not

                if(this.user.get('id') == 0 || this.user.get('id') == "") {

                    // Open sign in modal

                    if(typeof this.signInModal === "undefined") {

                        this.signInModal = new Views.SignInModal();

                    }

                    this.signInModal.openModal();

                    AE.pubsub.trigger('mjob:open:signin:modal', this.signInModal);

                } else if(this.user.get('id') != toUser) {

                   if(typeof currentUser.data.register_status !== 'undefined' && currentUser.data.register_status == '') {

                       // Open conversation modal

                       if(typeof this.conversationModal === "undefined") {

                           this.conversationModal = new Views.ModalConversation();

                       }

                       this.conversationModal.onOpen({

                           'to_user': toUser,

                           'from_user': this.user.get('ID')

                       });

                   } else {

                       AE.pubsub.trigger('ae:notification', {

                           notice_type: 'error',

                           msg: ae_globals.pending_account_error_txt

                       })

                   }

                }



                // Update auhentication form redirect

                var current_url = window.location.href;

                $('#signInForm .redirect_url').val(current_url);

                $('#signUpForm .redirect_url').val(current_url);

            },

            doMarkAsRead: function(event) {

                event.preventDefault();

                var view = this;

                view.conversationObj.set('do_action', 'mark_as_read');

                view.conversationObj.save('', '', {

                    success: function(status, res, jqXHR) {

                        if(res.success == true) {

                            AE.pubsub.trigger('ae:notification', {

                                notice_type: 'success',

                                msg: res.msg

                            })



                            $('#myAccount').find('.alert-sign').remove();

                            $('#myAccount').find('li div.inner').removeClass('unread');

                            $('#myAccount').find('.unread-message-count').text('0');

                        } else {

                            AE.pubsub.trigger('ae:notification', {

                                notice_type: 'error',

                                msg: res.msg

                            })

                        }

                    }

                })

            }

        });



        new Views.Conversation();



        /**

         * CONVERSATION VIEW IN SINGLE

         */

        Views.SingleConversation = Backbone.View.extend({

            el: '.conversation-form',

            events: {

              'keypress #post_content': 'sendMessage',

			  'click .mje-send-message': 'sendMessageClick'

            },

            initialize: function() {

                // Resize textarea

                 //custom code realtime chat here and seen option
                this.sendSeenAlert();
                this.connect_realtime();
              
                // end

                autosize(this.$el.find('textarea'));



                this.blockUi = new Views.BlockUi();



                this.initModel();



                this.initCarousel();



                if($('#default-message-query').length > 0) {

                    this.query = JSON.parse($('#default-message-query').html());

                } else {

                    this.query = {};

                }



                // Init list message collection and view

                var messageContainer = $('.conversation-form');

                if(typeof this.messageCollection === "undefined") {

                    if($('.message_postdata').length > 0) {

                        var messages = JSON.parse($('.message_postdata').html());

                        this.messageCollection = new Collections.Message(messages);

                    } else {

                        this.messageCollection = new Collections.Message();

                    }

                }

                // Message list view

                this.messageList = new Views.MessageList({

                    itemView: messageItem,

                    collection: this.messageCollection,

                    el: messageContainer.find('.list-conversation'),

                });



                AE.pubsub.on('ae:form:submit:success', this.afterSubmitForm, this);



              // Single message heartbeat

              var self = this;

              $(document).on('heartbeat-send', function (event, data) {

                  data.is_single_message = mje_heartbeat.is_single_message;

                  data.conversation_id = $('#conversation_id').val();

              });

                 // doan code nay dung de show message cho normal message khi co tin nhan moi , chuc nang la default cua MJE can tat di
              // chi xai cai real time moi
               /*
              $(document).on('heartbeat-tick', function(event, data) {

                if( data.unread_messages.fetch_message === true ) {
                    var is_load=true;
                  self.fetchMessages(is_load);

                }

              });
                  */
              // end

            },

            sendSeenAlert: function()
            {
                // seen not seen option                                
               let url_ajax_seen=window.location.origin+'/wp-admin/admin-ajax.php';                
                let sender_seen=$('#from_user').val();                  
                let recipient_seen=$('#to_user').val();  
                let real_order_id_seen=$("#typing-indicator").attr('data-real-order-id');
                let normal_order_id_seen=$("#typing-indicator").attr('data-normal-id-conversation');
                Backbone.ajax({
                            dataType: "json",
                            type: "post",
                            url: url_ajax_seen,
                             data : 
                             {
                                action: "sendSeenAlertNormal", //Tên action           
                                from_user : sender_seen,         
                                to_user: recipient_seen,
                                real_order_id_send: real_order_id_seen,
                                normal_order_id_send: normal_order_id_seen,
                            },
                            success: function(response){                                                                    
                                 //  console.log(response);
                            }                       

                        });

                //end

            },
            connect_realtime: function()
            {
                  var self = this;   
                  if(presencechannel){
                            presencechannel.bind('pusher:subscription_succeeded', function(data){
                            var realResponse=  pusher.subscribe('presence-chat-channel'+data.me.id);
                            realResponse.bind('normal-message-event', function(data) {
                            //alert(JSON.stringify(data));
                            //console.log(JSON.stringify(data));
                            self.fetchMessages();  
                            self.sendSeenAlert(); //seen option
                             });
                        });   
                            //custom code
                            // code for typing indicator
                            
                            presencechannel.bind('pusher:subscription_succeeded', function(data){

                            var realtypingResponse=  pusher.subscribe('presence-typing-indicator-channel'+data.me.id);

                             let conversation_type_order=$('#typing-indicator').attr('data-real-order-id');   

                             let conversation_type_normal=$('#typing-indicator').attr('data-normal-id-conversation'); 

                             if(conversation_type_normal)
                             {
                                //seen option code
                                  realtypingResponse.bind('normal-message-seen-event'+data.me.id, function(data) {
                                                          
                                   setTimeout(function()
                                    {   
                                        $("#seenlabel").remove();
                                        $(".single-seenlabel").remove();
                                        $("#normalMessageList").append('<p id="seenlabel" style="display:none;" class="text-right text-seen-custom"><i class="fa fa-check" aria-hidden="true"></i> VU</p>');
                                        $("#seenlabel").fadeIn();
                                    },3000);
                                  
                                  });
                                //end

                                  realtypingResponse.bind('normal-message-typing-event-'+conversation_type_normal, function(data) {

                                      if(conversation_type_normal==$('#typing-indicator').attr('data-normal-id-conversation'))
                                    {
                                         $('#typing-indicator').html(data.message);                                
                                      
                                         $('#typing-indicator').slideDown('fast');
                                        setTimeout(function()
                                        {                                                                       
                                            $('#typing-indicator').slideUp('fast');                                    
                                        },3000);
                                    }
                                    
                                     });
                             }

                             if(conversation_type_order)
                             {

                                 //seen option code
                                  let order_conversationID=$("#typing-indicator").attr('data-real-order-id');
                                  realtypingResponse.bind('order-message-seen-event-'+data.me.id+'-'+order_conversationID, function(data) {

                                   setTimeout(function()
                                    {                                          
                                        $("#seenlabel").remove();
                                        $(".single-seenlabel").remove();
                                        $("#orderMessageList").append('<p id="seenlabel" style="display:none;" class="text-right text-seen-custom"><i class="fa fa-check" aria-hidden="true"></i> VU</p>');
                                        $("#seenlabel").fadeIn();
                                    },3000);
                                  
                                  });
                                //end

                                realtypingResponse.bind('order-message-typing-event-'+conversation_type_order, function(data) {                                                    
                                         //let appendText='<p>'+data.message+'</p>';
                                
                           
                                                
                          
                                    if(conversation_type_order==$('#typing-indicator').attr('data-real-order-id'))
                                    {
                                         $('#typing-indicator').html(data.message);                                
                                      
                                         $('#typing-indicator').slideDown('fast');
                                        setTimeout(function()
                                        {                                                                       
                                            $('#typing-indicator').slideUp('fast');                                    
                                        },3000);
                                    }
                              
                                

                                 });
                             }
                         

                     

                        });   
                  
                  }                

            },           

            // Init new model

            initModel: function() {

                this.model = new Models.Message();

                // Initialize model

                this.model.set('type', 'message');

                this.model.set('post_parent', $('#conversation_id').val());

                this.model.set('from_user', $('#from_user').val());

                this.model.set('to_user', $('#to_user').val());

                this.model.set('page', $('#page_type').val());

                this.model.set('post_title', conversation_global.message_title);

                this.model.set('_wpnonce', $('input[name="_wpnonce"]').val());

            },

            // Init new carousel

            initCarousel: function() {

                if(typeof this.carousel === "undefined") {

                    this.carousel = new Views.Carousel({

                        el: $('.gallery_container_single_conversation'),

                        uploaderID:'carousel_single_conversation',

                        model: this.model,

                        carouselTemplate: '#ae_carousel_file_template',

                        extensions: ae_globals.file_types

                    });

                }

            },

			sendMessageClick: function(e){

				var view = this;

                var $target = $("#post_content");

                //if( e.keyCode == 13 && !e.shiftKey) {

                    var message = $target.val();
                    if(message =='')
                    {
                        message=$('.item-name').text();
                    }

                    var msgToCheck = view.cleanMessage(message);

                    if(msgToCheck != '') {

                        view.model.set('post_content', message);

                        view.model.save('', '', {

                            beforeSend: function() {

                                view.blockUi.block(view.$el.find('.group-compose'));

                                $target.blur();

                            },

                            success: function(status, response, xhr) {

                                console.log('send msgClick');

                                if(response.success) {

                                    view.fetchMessages();

                                } else {

                                    AE.pubsub.trigger('ae:notification', {

                                        notice_type: 'error',

                                        msg: response.msg

                                    });

                                    view.blockUi.unblock();

                                }

                            }

                        })

                    }

                    return false;

                //}

			},

            sendMessage: function(e) {

                //custom code real chat typing here
              
                let url_ajax=window.location.origin+'/wp-admin/admin-ajax.php';                
                let sender=$('#from_user').val();                  
                let recipient=$('#to_user').val();  
                let real_order_id=$("#typing-indicator").attr('data-real-order-id');
                let normal_order_id=$("#typing-indicator").attr('data-normal-id-conversation');
                Backbone.ajax({
                            dataType: "json",
                            type: "post",
                            url: url_ajax,
                             data : 
                             {
                                action: "show_typing_text_normalmessage", //Tên action           
                                from_user : sender,         
                                to_user: recipient,
                                real_order_id_send: real_order_id,
                                normal_order_id_send: normal_order_id,
                            },
                            success: function(response){                                                                    
                                   
                            }                       

                        });
               

                //end custom


                var view = this;

                var $target = $(e.currentTarget);

                if( e.keyCode == 13 && !e.shiftKey) {

                    var message = $target.val();
                    if(message =='')
                    {
                        message=$('.item-name').text();
                    }

                    var msgToCheck = view.cleanMessage(message);

                    if(msgToCheck != '') {

                        view.model.set('post_content', message);

                        view.model.save('', '', {

                            beforeSend: function() {



                                view.blockUi.block(view.$el.find('.group-compose'));

                                $target.blur();

                                var barDiv = $(".wrapper-list-conversation");

                                barDiv.mCustomScrollbar("update"); // 1.3.9.5

                                barDiv.mCustomScrollbar("scrollTo", 'bottom');



                            },

                            success: function(status, response, xhr) {

                                console.log('send msg2');

                                if(response.success) {

                                    view.fetchMessages();

                                } else {

                                    AE.pubsub.trigger('ae:notification', {

                                        notice_type: 'error',

                                        msg: response.msg

                                    });

                                    view.blockUi.unblock();

                                }

                                console.log('scroll Call');

                                var barDiv = $(".wrapper-list-conversation");

                                barDiv.mCustomScrollbar("update"); // 1.3.9.5

                                barDiv.mCustomScrollbar("scrollTo", 'bottom');



                            }

                        })

                    }

                    return false;

                }

            },

            cleanMessage: function(message) {

                message = message.replace(/\s+/g, '');

                message = message.replace(/(\r\n|\n|\r)/gm, '');

                return message;

            },

            // Trigger send message success

            fetchMessages: function(is_load=null) {

                var view = this;

                view.messageCollection.fetch({

                    data: {

                        query: view.query,

                        page: 1,

                        paged: 1,

                        paginate: true,

                    },

                    success: function() {

                        // Scroll to the newest message





                        // Show attach button

                        $('.plupload_buttons').show();



                        // Reset model

                        view.initModel();

                        view.carousel.setModel(view.model);

                        view.carousel.setupView();



                        // Reset form
                         if(is_load===null)
                        view.$el.find('#post_content').val('').focus();



                        view.blockUi.unblock();



                        // update textarea size

                        autosize.update(view.$el.find('#post_content'));

                        var barDiv = $(".wrapper-list-conversation");

                        barDiv.mCustomScrollbar("update"); // 1.3.9.5

                        barDiv.mCustomScrollbar("scrollTo", 'bottom');



                    }

                });

            },

            afterSubmitForm: function(result, resp, xhr, type) {

                if (type == 'send-offer' || type == 'reject' || type == 'decline') {

                    var view = this;

                    view.messageCollection.fetch({

                        data: {

                            query: view.query,

                            page: 1,

                            paged: 1,

                            paginate: true,

                        },

                        success: function() {

                            // Scroll to the newest message

                            $(".wrapper-list-conversation").mCustomScrollbar("scrollTo", 'bottom');

                        }

                    });

                }

            }

        });



        new Views.SingleConversation();

    });

})(jQuery, window.AE.Views, window.AE.Models, window.AE.Collections, window.AE);