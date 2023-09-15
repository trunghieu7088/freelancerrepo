(function($, Views, Models, Collection, AE) {
    $(document).ready(function() {             
        Views.PaymentMethod = Backbone.View.extend({
            el: '.mjob-payment-method-page',
            events: {

            },
            initialize: function() {
                // Init model
                
                if($('#current_user').length > 0) {
                    this.user = new Models.mJobUser(currentUser.data);
                } else {
                    this.user = new Models.mJobUser();
                }

                // Init block ui
                this.blockUi = new Views.BlockUi();

                var view = this;
                view.user.set('do_action', 'update_payment_method');
                // Form control
                var bankAccountForm = new Views.AE_Form({
                    el: '#bankAccountForm',
                    model: view.user,
                    rules: {
                        bank_country: 'required',
                        bank_first_name: 'required',
                        bank_last_name: 'required',
                        bank_name: 'required',
                        bank_swift_code: 'required',
                        bank_account_no: 'required',
                        retype_bank_account_no: {
                            equalTo: '#bankAccountForm #bank_account_no'
                        }
                    },
                    type: 'saveBankAccount',
                    blockTarget: '#bankAccountForm button'
                });

                // Form control
                var paypalAccountForm = new Views.AE_Form({
                    el: '#paypalAccountForm',
                    model: view.user,
                    rules: {
                        //custom code avi
                        paypal_first_name: 'required',
                        paypal_last_name: 'required',
                        paypal_middle_name: 'required',
                        paypal_custom_address: 'required',
                        paypal_custom_tel: 'required',
                        paypal_email: {
                            required: true,
                            email: true
                        }
                        //end custom code
                    },
                    type: 'savePayPalAccount',
                    blockTarget: '#paypalAccountForm button'
                });
            },
        });

        new Views.PaymentMethod();
    });
})(jQuery, window.AE.Views, window.AE.Models, window.AE.Collections, window.AE);