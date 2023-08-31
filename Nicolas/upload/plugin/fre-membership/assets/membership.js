(function($, Models, Collections, Views) {
    var check_submit = 0;
	Views.Modal_CanCel_Membership = Views.Modal_Box.extend({
        events: {
            // user register
            'submit form.cancel_membership': 'submitCancel',
            //custom code here
            'submit #renewdiscount_membership': 'renewDiscount',
            //end
        },
        /**
         * init view setup Block Ui and Model User
         */
        initialize: function() {
            this.user = AE.App.user;
            this.blockUi = new Views.BlockUi();
            // upload file portfolio image
            var view = this;
            var author_id = view.user.get('ID');
            console.log(author_id);
        },
        //custom code discount here
        renewDiscount: function(event)
        {   
            event.preventDefault();  
                var form = $(event.currentTarget),            
            view = this;     
            var discount_code=$('#discount_code').val();  
            if(discount_code =='' || discount_code =='undefined')
            {
                alert('please enter the discount code');
                return;
            }
            $.ajax({    
                type: "post",
                url: ae_globals.ajaxURL,
                dataType: 'json',
                data: {
                    action:'renewWithDiscount',
                    user_id_discount:$('#user_id_discount').val(),
                    discount_code_name:discount_code,
                    plan_sku: $('#customPlansku').val(),
                    pack_type: $("#pack_type").val()
                },
                beforeSend: function () {
                    form.attr('disabled', true).css('opacity', '0.5');
                    view.blockUi.block(form);
                },
                success: function (data, statusText, xhr) {
                    //console.log(data);
                    console.log(data.msg);
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
                    view.closeModal();
              
                    view.blockUi.unblock();
                    window.location.reload(true);
                }
            });
        },
        //end

        submitCancel: function(event){
            event.preventDefault();

            var form = $(event.currentTarget),
            $button = form.find(".btn-submit"), data = form.serializeObject(),
            view = this;

            $.ajax({    
                type: "post",
                url: ae_globals.ajaxURL,
                dataType: 'json',
                data: data,
                beforeSend: function () {
                    form.attr('disabled', true).css('opacity', '0.5');
                    view.blockUi.block(form);
                },
                success: function (data, statusText, xhr) {
                    if (data.success) {
                        AE.pubsub.trigger('ae:notification', {
                            msg: data.msg,
                            notice_type: 'success'
                        });
                       // $('#portfolio_item_'+id).closest('li').remove();

                    } else {
                        AE.pubsub.trigger('ae:notification', {
                            msg: data.msg,
                            notice_type: 'error'
                        });
                    }
                    view.closeModal();
                   // obj.attr('disabled', false).css('opacity', '1');
                    view.blockUi.unblock();
                    window.location.reload(true);
                }
            });
            return false;
        },

    });

    Views.Modal_Subscriber_Credit = Views.Modal_Box.extend({
        events: {
            // user register
            'submit form.subscriber_credit': 'subscriberCredit',
            },
        /**
         * init view setup Block Ui and Model User
         */
        initialize: function() {

            this.user = AE.App.user;
            this.blockUi = new Views.BlockUi();
            // upload file portfolio image
            var view = this;
            var author_id = view.user.get('ID');
            //form.trigger('reset');
        },

        subscriberCredit: function(event){
            event.preventDefault();
            event.stopPropagation();
            if( window.check_submit ){
                return ;
            }
            window.check_submit = 1;

            var form = $(event.currentTarget),
            $button = form.find(".btn-submit"), data = form.serializeObject(),view = this;

            $.ajax({
                type: "post",
                url: ae_globals.ajaxURL,
                dataType: 'json',
                data: data,
                beforeSend: function () {
                    form.attr('disabled', true).css('opacity', '0.5');
                    view.blockUi.block(form);
                  //  console.log(data);
                },
                success: function (data, statusText, xhr) { //subscriberViaCredit
                    if (data.success) {
                      //  console.log(data);
                        AE.pubsub.trigger('ae:notification', {
                            msg: data.msg, notice_type: 'success'
                        });
                        if(data.redirect_url){
                            window.setTimeout( function(){
                                window.location.href = data.redirect_url;
                            }, 2000 );
                         } else {
                            window.setTimeout( function(){
                                window.location.reload(true);
                            }, 1500 );
                        } 
                    } else {
                        AE.pubsub.trigger('ae:notification', {
                            msg: data.msg, notice_type: 'error'
                        });
                        window.check_submit = 0; // only allow if the first submit is fail.
                    }
                    view.closeModal();
                    view.blockUi.unblock();
                    form.attr('disabled', false).css('opacity', '1');

                }
            });
            return false;
        },

    });
	Views.memeberShip = Backbone.View.extend({
        el: 'body',
        events: {
            // user account details
            'click a.btnCancelMembership': 'openModalCancel',
            'click button.btnSubscriberViaCredit': 'openModalConfirmSubscriberCredit',
            'click .openDiscountModal' : 'openDiscountModalForm', //custom code discount here
            // user profile details
        },
        initialize: function() {
        	//alert('123');
        },
        openModalConfirmSubscriberCredit:  function(){
            event.preventDefault();
            //var portfolio = new Models.Portfolio();
            this.modalPortfolio = new Views.Modal_Subscriber_Credit({
                el: '#modalSubscriberViaCredit',
                //collection: this.portfolios_collection,
                // model: portfolio
            });
            //this.modalPortfolio.setModel(portfolio, this.profile);
            this.modalPortfolio.openModal();
        },
        openModalCancel: function(){

        	event.preventDefault();
            //var portfolio = new Models.Portfolio();
            this.modalPortfolio = new Views.Modal_CanCel_Membership({
                el: '#modalCancelMembership',
                //collection: this.portfolios_collection,
                // model: portfolio
            });
            //this.modalPortfolio.setModel(portfolio, this.profile);
            this.modalPortfolio.openModal();
        },
        openDiscountModalForm: function()
        {
            event.preventDefault();           
               this.modalPortfolio = new Views.Modal_CanCel_Membership({
                el: '#modalDiscountRenew',
                //collection: this.portfolios_collection,
                // model: portfolio
            });
            //this.modalPortfolio.setModel(portfolio, this.profile);
            this.modalPortfolio.openModal();
        },
    });
    if (typeof Views.memeberShip !== 'undefined') {
        new Views.memeberShip();
    }



})(jQuery, window.AE.Models, window.AE.Collections, window.AE.Views);
