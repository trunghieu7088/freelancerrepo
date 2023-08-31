(function($, Models, Collections, Views) {
    Views.Modal_Admin_Bid_Custom = Backbone.View.extend({
        el: '#modal_bid_admin',
        events: {
            'submit #bid_form_admin': 'submitAdminBidProject', //#bid_form submit
        },
        initialize: function() {      
            AE.Views.Modal_Box.prototype.initialize.apply(this, arguments);
            this.blockUi = new Views.BlockUi();                 
            $('#freelancer_bid_admin.fre-chosen-single').chosen({
                search_contains: true, 
                width: '100%',
                max_selected_options:1,
                placeholder_text_multiple:'Select employee',

                  // Search within option text                
                // Other options here
            });
        }, 
        initValidator: function () {
            this.bidForm_validator_admin =   $("form#bid_form_admin").validate({
                ignore: "",
                rules: {
                    bid_budget_admin: "required",
                    bid_time_admin: "required",
                    bid_content_admin: "required",
                    freelancer_bid_admin: "required",
                },
                errorPlacement: function (label, element) {
                    // position error label after generated textarea
                    if (element.is("#bid_budget_admin")) {
                        $(element).closest('.fre-project-budget').append(label);
                    } else {
                        $(element).closest('.fre-input-field').append(label);
                    }
                },
                highlight: function (element, errorClass) {
                    $(element).closest('.fre-input-field').addClass('error');
                },
                unhighlight: function (element, errorClass) {
                    $(element).closest('.fre-input-field').removeClass('error');
                },
            });
            
        },
        submitAdminBidProject: function (event) {                               
            event.preventDefault();   
            this.initValidator();
            var view = this;
            var $target = $(event.currentTarget);
            data = $target.serializeObject();
            button = $target.find('button.btn-submit');
            if (this.bidForm_validator_admin.form())
            {
                $.ajax({
                    type: "post",
                    url: ae_globals.ajaxURL,  
                    dataType: 'json',
                    data: data, // action: admin_add_employee_bid  
                    beforeSend: function () {
                        view.blockUi.block(button);
                    },                              
                    success: function (response) { 
                        if (response.success) {
                            AE.pubsub.trigger('ae:notification', {
                                msg: response.message,
                                notice_type: 'success'
                            });
                            setTimeout(function () {
                                location.reload();
                            }, 3000);
                        }
                        else {
                            AE.pubsub.trigger('ae:notification', {
                                msg: response.message,
                                notice_type: 'error'
                            });
                        }
                    },
                });
            }        
            
        }
    });
    if (typeof Views.Modal_Admin_Bid_Custom !== 'undefined') {
        new Views.Modal_Admin_Bid_Custom();
    }
})(jQuery, window.AE.Models, window.AE.Collections, window.AE.Views);