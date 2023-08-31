(function($, Models, Collections, Views) {
    var original_price_credit=$("#custom_price").html();
    var original_paypal_button_custom=$('input[name="custom"]').val();
    var original_paypal_price=$('input[name="a3"]').val();
   
   Views.Modal_Discount_Code_Custom = Backbone.View.extend({
          
         el: 'body',
           events: {         
               'click #apply_code': 'showAlert',         
                  'submit #discount_code_form': 'applyDiscount',
                  'keypress #discount_code': 'hideTextResult',
               'click #remove_discount' : 'removeDiscountOption',
           },
            initialize: function() {
              var view = this;
            
             $( "#discount_code_form" ).validate({
                 rules: {
                   discount_code: 
                   {
                         required: true
                   },
                         
                     },
   
                   messages: 
                         {
                           discount_code:
                           {
                                required:'Please enter discount code if you want to use',
                           },
                         },
                         
                         highlight: function(element, errorClass, validClass) 
                       {
                           $("#discount_code_form").addClass('error');				    	
                       },
                        unhighlight: function(element, errorClass, validClass)
                        {
                            $("#discount_code_form").removeClass('error');
                        }
   
   
               });
           },
          
          applyDiscount: function()
          {
   
                  event.preventDefault();       		
                   $.ajax({
   
                            type: "post",
                   url: ae_globals.ajaxURL,
                   dataType: 'json',
                   data: {
                       action:'check_discount_code',
                       user_id_discount:$('#user_id_discount').val(),
                       discount_code_name:$('#discount_code').val(),
                       discount_sku:$('#discount_sku').val()
                   },
                   beforeSend: function () {
                       $("#discount_code_form").attr('disabled', true).css('opacity', '0.5');                    
                   },
                   success: function (response) { //apply discount code
                     console.log(response);
                       $("#discount_code_form").attr('disabled', false).css('opacity', '1');
                       if(response.data.check=='no')
                       {
                           $("#text-discount-alert").css('display','block');
                           $("#text-discount-success").css('display','none');
                           $("#custom_price").html(original_price_credit);
                           $("#custom_price2").html(original_price_credit);
                           $("#custom_price3").html(original_price_credit);
                            $("#discount_code_id_credit").val('');
                             //paypal
                            $('input[name="custom"]').val(original_paypal_button_custom);
                             $('input[name="a3"]').val(original_paypal_price);
   
                             //remove butotn
                             $('#remove_discount').css('display','none');
                            
                       }
                       if(response.data.check=='ok')
                       {
                           $("#text-discount-success").html('Discount '+response.data.discount_percent+'% : - '+response.data.decrease_price).css('display','block');
                           $("#text-discount-alert").css('display','none');
                           $("#custom_price").html(response.data.discount_price);
                           $("#custom_price2").html(response.data.discount_price);
                           $("#custom_price3").html(response.data.discount_price);
                           $("#discount_code_id_credit").val(response.data.discount_code_id);
                           
                           $("#text-discount-info").html(response.data.discount_code_name+' has been applied').css('display','block');
                           //paypal                       
                           var discount_paypal=original_paypal_button_custom+'||'+response.data.discount_code_id;
                           $('input[name="custom"]').val(discount_paypal);
                           $('input[name="a3"]').val(response.data.discount_price_raw);
   
                           //remove button
                           $('#remove_discount').css('display','inline-block');
   
                       }
                       
   
                       
                   }
               });
          },
   
          hideTextResult: function()
          {
                  $("#text-discount-alert").css('display','none');
          },
   
          removeDiscountOption: function()
          {
               $("#text-discount-alert").css('display','none');
               $("#text-discount-success").css('display','none');
                $("#text-discount-info").css('display','none');
               $("#custom_price").html(original_price_credit);
               $("#custom_price2").html(original_price_credit);
               $("#custom_price3").html(original_price_credit);
               $("#discount_code_id_credit").val('');
   
               //paypal
               $('input[name="custom"]').val(original_paypal_button_custom);
               $('input[name="a3"]').val(original_paypal_price);
               $('#discount_code').val('');
               $('#remove_discount').css('display','none');
          }
          
       });
   
     if (typeof Views.Modal_Discount_Code_Custom !== 'undefined') {
           new Views.Modal_Discount_Code_Custom();
       }
   
   
   })(jQuery, window.AE.Models, window.AE.Collections, window.AE.Views);