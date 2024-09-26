(function ($) {
    $(document).ready(function() { 

        let m_is_sending_payment=false;
        if($('#multiple-checkout-form').length > 0)
            { 
                var multiple_stripe_info = Stripe(stripe_public_key);
                var multiple_stripe_elements = multiple_stripe_info.elements();
               // var custom_card = stripe_elements.create('card');
    
               //init card elements
                var m_cardNumber = multiple_stripe_elements.create('cardNumber',{
                    placeholder: 'Card Number: 1234 1234 1234 1234' // Custom placeholder
                });
                var m_cardExpiry = multiple_stripe_elements.create('cardExpiry');
                var m_cardCvc = multiple_stripe_elements.create('cardCvc');
    
                //init card validators
                var m_cardNumberValid = false;
                var m_cardExpiryValid = false;
                var m_cardCvcValid = false;

                m_cardNumber.on('change', function(event) {
                    if (event.error) {
                        m_cardNumberValid = false;
                    } else {                    
                        m_cardNumberValid = true;
                        $("#m-stripe-card-errors").text('');
                    }
                });
    
                m_cardExpiry.on('change', function(event) {
                    if (event.error) {
                        m_cardExpiryValid = false;
                    } else {                    
                        m_cardExpiryValid = true;
                        $("#m-stripe-card-errors").text('');
                    }
                });
              
                m_cardCvc.on('change', function(event) {
                    if (event.error) {
                        m_cardCvcValid = false;
                    } else {                    
                        m_cardCvcValid = true;
                        $("#m-stripe-card-errors").text('');
                    }
                });
                
                m_cardNumber.mount("#m-custom-stripe-cardNum");
                m_cardExpiry.mount("#m-custom-stripe-expiry");
                m_cardCvc.mount("#m-custom-stripe-cvc");
            }

            let m_payment_submit_validator= $("#multiple-checkout-form").validate({
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
                    if (!m_cardNumberValid) {                      
                        $('#m-stripe-card-errors').text(card_invalid_error);
                        return false;
                    }

                    if (!m_cardExpiryValid) {                      
                        $('#m-stripe-card-errors').text(expiry_invalid_error);
                        return false;
                    }

                    if (!m_cardCvcValid) {                      
                        $('#m-stripe-card-errors').text(cvc_invalid_error);
                        return false;
                    }

                    if(m_is_sending_payment==true)
                    {                        
                        return false;
                    }

                    var m_payment_data=$(form).serialize();
                    //send request to server backend
                    $.ajax({
                        type: "post",
                        url: moving_ajaxURL,
                        dataType: 'json',
                        data: m_payment_data,        
                        beforeSend: function () {
                            $("#multiple-checkout-form").css('opacity','0.5');                                
                            m_is_sending_payment=true;    // set status to block double submit form                 
                            toastr.info(sending_payment);
                        },                      
                        success: function (response) {
                                               
                            if(response.success==true || response.success =='true')
                            {                                                                                                                                                          
                                let paid_user_id= response.paid_user_id;                                
                             
                                multiple_stripe_info.confirmCardPayment(response.client_secret, {
                                    payment_method: {
                                        type:'card',
                                        card: m_cardNumber,
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
                                                    request_items: $("#request_collection").val(),                                                    
                                                    payment_amount: $("#total_price").val(),
                                                    is_multiple: true,
                                                    
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
                                                        if(order_response.is_multiple==true || order_response.is_multiple=='true')
                                                        {
                                                            window.location.href=order_response.redirect_url;
                                                        }
                                                                                                
                                                    }

                                                    //reset is sending payment status
                                                    m_is_sending_payment=false;           
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
        
            $(".m-remove-item").click(function(event){
                    event.preventDefault();
                    let m_request_remove_cart=$(this).attr('data-remove-cart-item');
                    $.ajax({
                        type: "post",
                        url: moving_ajaxURL,
                        dataType: 'json',
                        data: 
                        {
                            action:'remove_request_cart',
                            cart_request_id: m_request_remove_cart,
                        },        
                                            
                        success: function (response) {
                            if(response.success=='true' || response.success==true)
                            {

                                toastr.success(response.message);
                                if(response.is_must_redirect)
                                {
                                    window.location.href=response.is_must_redirect;
                                }
                                let remove_cart_btn_element=$("[data-request-cart-item='"+response.removed_id+"']");
                                
                                // remove item in frontend out of list + update total price and total items , ids
                                remove_cart_btn_element.remove(); 

                                $("#request_collection").val(response.item_ids);
                                $("#total_price").val(response.total_price);
                                
                                //update text 
                                $("#total_items").text(response.total_items); 
                                $("#total_price_text").text(response.total_price);   
                                                              
                            }
                            else
                            {
                                toastr.error(response.message);   
                            }                          
                        }
                    });
            });
    })
})(jQuery);