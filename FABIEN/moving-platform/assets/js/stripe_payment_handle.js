(function ($) {
    $(document).ready(function() {  
       
        let payment_modal= $("#stripe-payment-modal");
        let is_sending_payment=false;
        
      
        $(".pay-now").click(function(event){
        
            event.preventDefault();   
            let pay_item_id=$(this).attr('data-pay-item');
            let pay_item_title=$(this).attr('data-pay-item-title');
            $("#pay_request_id").val(pay_item_id);
            $("#checkout-product-name").text(pay_item_title);
            
            payment_modal.slideDown();

            //reset stripe error messages
            $("#stripe-card-errors").text('');

            //reset fields of stripe payment form
            $("#billing_name").val('');
            cardNumber.clear();
            cardExpiry.clear();
            cardCvc.clear();

            $("#moving-payment-form").css('opacity','1');   

        });

        $("#stripe-close").click(function(event){
            event.preventDefault();          
            //payment_modal.css('display','block');
            payment_modal.slideUp();
        });

        //handle to close modal if the user click to dark area
        $(window).on('click', function (e) {
            if ($(e.target).is('#stripe-payment-modal')) {                            
               payment_modal.slideUp();
            }
          });

        if($('#moving-payment-form').length > 0)
        { 
            var stripe_info = Stripe(stripe_public_key);
            var stripe_elements = stripe_info.elements();
           // var custom_card = stripe_elements.create('card');

           
           //init card elements
            var cardNumber = stripe_elements.create('cardNumber',{
                placeholder: 'Card Number: 1234 1234 1234 1234', // Custom placeholder               
            });
            var cardExpiry = stripe_elements.create('cardExpiry');
            var cardCvc = stripe_elements.create('cardCvc');

            //init card validators
            var cardNumberValid = false;
            var cardExpiryValid = false;
            var cardCvcValid = false;

            cardNumber.on('change', function(event) {
                if (event.error) {
                    cardNumberValid = false;
                } else {                    
                    cardNumberValid = true;
                    $("#stripe-card-errors").text('');
                }
            });

            cardExpiry.on('change', function(event) {
                if (event.error) {
                    cardExpiryValid = false;
                } else {                    
                    cardExpiryValid = true;
                    $("#stripe-card-errors").text('');
                }
            });
          
            cardCvc.on('change', function(event) {
                if (event.error) {
                    cardCvcValid = false;
                } else {                    
                    cardCvcValid = true;
                    $("#stripe-card-errors").text('');
                }
            });

          

            cardNumber.mount("#custom-stripe-cardNum");
            cardExpiry.mount("#custom-stripe-expiry");
            cardCvc.mount("#custom-stripe-cvc");

            let payment_submit_validator= $("#moving-payment-form").validate({
                ignore: "",
                rules: {
                    billing_name: "required",                     
                },
                messages: 
                {
                    billing_name: 
                    {
                        required: required_validation_message
                    },
                },
                errorElement: 'div', // Change the element to <div>
                errorClass: 'error_message', // Change the error class to 'message'
                errorPlacement: function (label, element) {                                   
                        $(element).next().append(label);                        
                },
                highlight: function (element, errorClass) {
                    $(element).addClass('error_message_field');
                },
                unhighlight: function (element, errorClass) {
                    $(element).removeClass('error_message_field');
                },
                submitHandler: function(form) 
                {
                    if (!cardNumberValid) {                      
                        $('#stripe-card-errors').text(card_invalid_error);
                        return false;
                    }

                    if (!cardExpiryValid) {                      
                        $('#stripe-card-errors').text(expiry_invalid_error);
                        return false;
                    }

                    if (!cardCvcValid) {                      
                        $('#stripe-card-errors').text(cvc_invalid_error);
                        return false;
                    }

                    if(is_sending_payment==true)
                    {                        
                        return false;
                    }

                    var payment_data=$(form).serialize();
                    //send request to server backend
                    $.ajax({
                        type: "post",
                        url: moving_ajaxURL,
                        dataType: 'json',
                        data: payment_data,        
                        beforeSend: function () {
                            $("#moving-payment-form").css('opacity','0.5');                                
                            is_sending_payment=true;    // set status to block double submit form                 
                            toastr.info(sending_payment);
                        },                      
                        success: function (response) {
                                               
                            if(response.success==true || response.success =='true')
                            {                                                                                                                                                          
                                let paid_user_id= response.paid_user_id;
                                let pay_request_id= response.pay_request_id;                                
                             
                                stripe_info.confirmCardPayment(response.client_secret, {
                                    payment_method: {
                                        type:'card',
                                        card: cardNumber,
                                        billing_details: {
                                            name: $("#billing_name").val(),
                                        }
                                    }
                                }).then(function(result) {
                                    if (result.error) {
                                        toastr.error(result.error);                                        
                                        window.location.reload();
                                    } 
                                    else 
                                    {                                        
                                        if (result.paymentIntent.status === 'succeeded') 
                                        {
                                            //call ajax to create order here
                                            $.ajax({
                                                type: "post",
                                                url: moving_ajaxURL,
                                                dataType: 'json',
                                                data: 
                                                {
                                                    action:'complete_order_request',
                                                    user_id: paid_user_id,
                                                    request_item: pay_request_id,
                                                    payment_amount: $("#pay_request_price").val(),
                                                    
                                                    //payment stripe info
                                                    payment_status: result.paymentIntent.status,
                                                    payment_intentID: result.paymentIntent.id,
                                                    payment_currency_code: result.paymentIntent.currency,
                                                    payment_created: result.paymentIntent.created,
                                                   
                                                },                                                                      
                                                success: function (order_response) 
                                                {
                                                    if(order_response.success=='true' || order_response.success==true)
                                                    {
                                                        toastr.success(order_response.message);
                                                        $('[data-contact-id="'+order_response.request_item+'"]').removeClass('not-paid').text(order_response.contact_method);
                                                      
                                                        //remove add cart and buy buttons after purchase successfully
                                                        $('[data-pay-item="'+order_response.request_item+'"]').remove();
                                                        $('[data-action-cart="'+order_response.request_item+'"]').remove();

                                                        //highlight request item wrapper and add paid label
                                                        $('[data-request-item-wrapper="'+order_response.request_item+'"]').addClass('paid-request-item');
                                                        $('[data-mark-paid-icon="'+order_response.request_item+'"]').removeClass('unpaid_mark');                                                        
                                                        

                                                        payment_modal.slideUp();
                                                        //window.location.href=bookingResponse.redirect_url;                                        
                                                    }

                                                    //reset is sending payment status
                                                    is_sending_payment=false;           
                                                }
                                            });

                                        }
                                    }
                                });
                            }
                            else
                            {
                                toastr.error(response.message);                                
                            }
                           
                        }
                    });


                }
            });

            $(document).on("click",".add-to-cart",function(event) {
            //$(".add-to-cart").click(function(event){
                event.preventDefault();
                let request_add_cart=$(this).attr('data-action-cart');
                let temp_add_cart=$(this);
                $.ajax({
                    type: "post",
                    url: moving_ajaxURL,
                    dataType: 'json',
                    data: 
                    {
                        action:'add_request_cart',
                        cart_request_id: request_add_cart,
                    },        
                    beforeSend: function () {
                        temp_add_cart.attr('disabled','disabled');
                    },                      
                    success: function (response) {
                        if(response.success=='true' || response.success==true)
                        {
                            toastr.success(response.message);
                            let cart_btn_element=$("[data-action-cart='"+response.added_id+"']");
                            
                            cart_btn_element.text(response.updated_text);
                            cart_btn_element.addClass('remove-from-cart');
                            cart_btn_element.removeClass('add-to-cart');
                            
                            update_cart_shopping(response.total_items);

                        }
                        else
                        {
                            toastr.error(response.message);   
                        }
                        temp_add_cart.removeAttr('disabled');
                    }
                });

            });

            $(document).on("click",".remove-from-cart",function(event) {                
                    event.preventDefault();
                    let request_remove_cart=$(this).attr('data-action-cart');
                    let temp_remove_cart=$(this);
                    $.ajax({
                        type: "post",
                        url: moving_ajaxURL,
                        dataType: 'json',
                        data: 
                        {
                            action:'remove_request_cart',
                            cart_request_id: request_remove_cart,
                        },        
                        beforeSend: function () {
                            temp_remove_cart.attr('disabled','disabled');
                        },                      
                        success: function (response) {
                            if(response.success=='true' || response.success==true)
                            {
                                toastr.success(response.message);
                                let remove_cart_btn_element=$("[data-action-cart='"+response.removed_id+"']");
                                
                                remove_cart_btn_element.text(response.updated_text);
                                remove_cart_btn_element.removeClass('remove-from-cart');
                                remove_cart_btn_element.addClass('add-to-cart');      
                                update_cart_shopping(response.total_items);
                                                              
                            }
                            else
                            {
                                toastr.error(response.message);   
                            }
                            temp_remove_cart.removeAttr('disabled');
                           
                        }
                    });
    
                });

                function update_cart_shopping(number_item)
                {
                    if($("#number-item-in-cart").length > 0)
                    {
                        $("#number-item-in-cart").text(number_item);
                    }
                }
           
        }

     
    })
})(jQuery);