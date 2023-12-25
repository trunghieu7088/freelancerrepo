(function ($) {
    $(document).ready(function () {
      let url_ajax=window.location.origin+'/wp-admin/admin-ajax.php'; 
               //cancel subscription
            $("#cancelSubscriptionbtn").click(function(){

              $.ajax({

                type: "POST",
                url: url_ajax,
                dataType: 'json',
                data:{
                    action: 'unsubcribe_plan',
                    subscriptionID: $(this).attr('data-subscription-id'),
                    unsubcribeNonce: $(this).attr('data-unsubscribe-nonce'),
                  },  
                beforeSend: function() {                   
                    toastr.warning('Cancelling subscription...');    
                    $("#cancelSubscriptionbtn").attr('disabled','disabled');
                },          
                success: function(response) { 
                  if(response.success=='true')             
                  {         
                    toastr.success(response.message);   
                    window.location.reload();
                  }                 
                },   
                error: function(error) {                    
                    toastr.error('Something went wrong. Please refresh');
                }             
            });


            });

          $("#subscriptionForm").submit(function(e){
              e.preventDefault();                
              var formData = $(this).serialize();               
              $.ajax({

                type: "POST",
                url: url_ajax,
                dataType: 'json',
                data: formData,            
                success: function(response) {       
                    if(response.success=='true')             
                    {                        
                        toastr.success(response.message);                       
                        window.location.href=response.redirect_url;                         
                    }                                    
                },   
                error: function(error) {                    
                    toastr.error('Something went wrong. Please refresh');
                }             
            });

          });

          $("#summaryInfo").popover({
            title: '<i class="fa fa-info-circle"></i> Information',
            content: '1. The number of posts per month represents the quantity of services you can post each month upon successful renewal of the subscription. <br> <br>  2. The transaction fee is a percentage of the money that the Admin will receive when you (the seller) successfully complete a project or work.',
            placement: "bottom",
            html:'true',
            container:'body',
        });
        
        if(typeof plan_id !=='undefined')
        {
            paypal.Buttons({
              style: {
                  shape: 'rect',
                  color: 'gold',
                  layout: 'vertical',
                  label: 'subscribe'
              },
              createSubscription: function(data, actions) {
                return actions.subscription.create({
                  /* Creates the subscription */
                  //plan_id: 'P-249253466R074273WMVIIIWI',               
                  plan_id: plan_id,               
                });
              },
              onApprove: function(data, actions) {
                //console.log(data);
                //set paypal subscription ID and paypal order id to the form for submit
                toastr.warning('Creating subscription..Please wait');
                $("#paypal_subscription_id").val(data.subscriptionID);
                $("#paypal_order_id").val(data.orderID);
                $("#paypal_paymentSource").val(data.paymentSource);
                $("#subscriptionForm").submit();             
               
              },
              onCancel: function(data, actions) {                  
                  window.location.reload();
              },
          }).render('#paypal-button-container-'+plan_id); // Renders the PayPal button
        }
         
      
        $("#free-subscribe-plan-btn").click(function(){ 
            toastr.warning('Creating subscription..Please wait');           
            $("#subscriptionForm").submit();
        });
       
     
    });

})(jQuery);

