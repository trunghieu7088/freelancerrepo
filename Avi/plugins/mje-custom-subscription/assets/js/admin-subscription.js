(function ($) {
    $(document).ready(function () {
        let url_ajax=window.location.origin+'/wp-admin/admin-ajax.php'; 
        $("#savePaypalInfo").submit(function(e){
            e.preventDefault();  
            var formData = $(this).serialize();
            $.ajax({

                type: "POST",
                url: url_ajax,
                dataType: 'json',
                data: formData,
                success: function(response) {                    
                    toastr.success(response.message);
                },   
                error: function(error) {                    
                    toastr.error('Something went wrong. Please refresh');
                }             
            });
        });

        $("#createSubscriptionPlanForm").submit(function(e){
            e.preventDefault();  
            var formData = $(this).serialize();        
            $.ajax({

                type: "POST",
                url: url_ajax,
                dataType: 'json',
                data: formData,
                beforeSend: function() {
                    $("#createSubscriptionPlanForm").find(':input').attr('disabled', 'disabled'); 
                    toastr.warning('Taking action.... Please wait');                  
                },
                success: function(response) {       
                    if(response.success=='true')             
                    {
                        // console.log(response.subscription_info);
                        toastr.success(response.message);                       
                        setTimeout(function(){ window.location.href=response.redirect_url; }, 1000); 
                         
                    }                                    
                },   
                error: function(error) {                    
                    toastr.error('Something went wrong. Please refresh');                    
                }             
            });

        });

        if($('#subscriptionplansjson').length > 0)
        {
            var listplanJson = JSON.parse($('#subscriptionplansjson').html());      
        }
        else
        {
            var listplanJson = '';
        }
        

        $(".modalPlan").click(function(){           
           //console.log( $(this).attr('data-paypal-id'));
           //console.log(listplanJson);
           var targetPaypalPlanId=$(this).attr('data-paypal-id');
           var filteredPlan = listplanJson.filter(function(plan) {
            return plan.paypal_plan_id == targetPaypalPlanId;
        });            
            var detailPlan=filteredPlan[0];            
            $("#subscriptionPlanModal").find('.modal-title').html(detailPlan.title);
            var planContent='';
            planContent+='<p>'+'Price per month: <strong>'+detailPlan.price_text+'</strong></p>';          
            planContent+='<p>'+'Posts per month: <strong>'+detailPlan.plan_number_posts+'</strong></p>';          
            planContent+='<p>'+'Description: <strong>'+detailPlan.description+'</strong></p>';          
            planContent+='<p>'+'Subtitle: <strong>'+detailPlan.subtitle+'</strong></p>';          
            planContent+='<p>'+'Transaction fee: <strong>'+detailPlan.transaction_fee_text+'</strong></p>';          
            planContent+='<p>'+'Paypal ID: <strong>'+detailPlan.paypal_plan_id+'</strong></p>';
            console.log(detailPlan.advertisement) ;
            $.each(detailPlan.advertisement, function(index, value) {                
                if(value !=='false')
                {
                    planContent+='<p>Adverstisement: '+'<strong>'+value+'</strong></p>';
                }
                
            });        
            $("#subscriptionPlanModal").find('.admin-subscription-modal').html(planContent);
            $("#subscriptionPlanModal").modal();
        });

        //open modal confirm deactivate or reactivate
        $(".setStatus-subscription-btn").click(function(){            
            var statusPlan=$(this).attr('data-plan-status');   
            
            $("#btn-actionPlan").attr('data-paypal-id',$(this).attr('data-plan-id'));         
            $("#btn-actionPlan").attr('data-plan-status',statusPlan);         
            if(statusPlan =='archive')
            {
                $("#confirmActiveplan").find('.active-modal-content').html('<h4>Are you sure want to deactivate this plan ?</h4>');                
                
                $("#btn-actionPlan").text('Deactivate');                                                       
                $("#confirmActiveplan").find('#btn-actionPlan').removeClass('btn-success');
                $("#confirmActiveplan").find('#btn-actionPlan').addClass('btn-warning');
            }
            else
            {
                $("#confirmActiveplan").find('.active-modal-content').html('<h4>Are you sure want to activate this plan ?</h4>');                
                $("#btn-actionPlan").text('Activate');
                $("#confirmActiveplan").find('#btn-actionPlan').removeClass('btn-warning');
                $("#confirmActiveplan").find('#btn-actionPlan').addClass('btn-success');
              
            }
                    
            $("#confirmActiveplan").modal();
        });

        //deactivate or reactivate plan
        $("#btn-actionPlan").click(function(){
            var paypal_plan_id_action=$("#btn-actionPlan").attr('data-paypal-id');
            var paypal_plan_status=$("#btn-actionPlan").attr('data-plan-status');
            $.ajax({

                type: "POST",
                url: url_ajax,
                dataType: 'json',
                data: {
                    action:'setStatusPlan',
                    setStatus: paypal_plan_status,
                    paypalPlanID: paypal_plan_id_action,
                },
                success: function(response) {                    
                    toastr.success(response.message);
                    if(response.success=='true')
                    {
                        window.location.reload();
                    }
                },   
                error: function(error) {                    
                    toastr.error('Something went wrong. Please refresh');
                }             
            });
        });

        $(".real-delete-plan-btn").click(function(){  
            $("#btn-deletePlan").attr('data-paypal-id',$(this).attr('data-plan-id'));
            $("#deletePlanModal").find('.active-modal-content').html('<h4>Are you sure want to delete this plan ?</h4>');                
            $("#deletePlanModal").modal();
        });

        $("#btn-deletePlan").click(function(){  
            var delete_paypal_id=$(this).attr('data-paypal-id');
            //console.log(delete_paypal_id);
            $.ajax({

                type: "POST",
                url: url_ajax,
                dataType: 'json',
                data: {
                    action:'deleteWPPlan',                    
                    paypalPlanID: delete_paypal_id,
                },
                success: function(response) {                    
                    toastr.success(response.message);
                    if(response.success=='true')
                    {
                        window.location.reload();
                    }
                },   
                error: function(error) {                    
                    toastr.error('Something went wrong. Please refresh');
                }             
            });            
        });
        

    if($('#subscriptionList').length > 0)
    {
        var subscriptionListJSON = JSON.parse($('#subscriptionList').html());      
    }
    else
    {
        var subscriptionListJSON = '';
    }
  
       let subscriptionDataTable= new DataTable('#subscriptionTable',{
            "bFilter" : true,
            data: subscriptionListJSON,        
            columns: [
                {
                    className: 'dt-control',
                    orderable: false,              
                    defaultContent: '',                     
                },     
                { data: 'subscriber' },             
                { data: 'plan_name' },               
                { data: 'status' },
                { data: 'price' },            
                { data: 'date' },            
            ],
          
        } );
    

    function format(d) {
        // `d` is the original data object for the row
        let paymentInfobtn='';
        if(parseInt(d.price) > 0)
        {
            paymentInfobtn='<button data-subscriptionID="'+d.paypalSubscriptionID+'" class="btn btn-info get-payment-info-btn" type="button">'+'Payment info Paypal'+'</button>'
        }
        else
        {
            paymentInfobtn='';
        }
        return (            
            '<p>Remaining Post: <strong> '+d.remaining_post+'</strong></p>' +
            '<p> Paypal Subscription ID: <strong>'+d.paypalSubscriptionID+'</strong></p>'+
            '<p>'+d.userInfo+'</p>'+                  
            '<p>Total renewal times : <strong>'+d.total_renewal_times+'</strong></p>'+
            '<p>Total Paid ( Total renewal times  * price ): <strong>'+d.total_paid+'</strong></p>'+
            '<p>Last renew on : <strong>'+d.last_subscription_date+'</strong></p>'+
            paymentInfobtn
           // '<button data-subscriptionID="'+d.paypalSubscriptionID+'" class="btn btn-info get-payment-info-btn" type="button">'+'Payment info Paypal'+'</button>'
            
        );
    }

    subscriptionDataTable.on('click', 'td.dt-control', function (e) {
        let tr = e.target.closest('tr');
        let row = subscriptionDataTable.row(tr);
     
        if (row.child.isShown()) {
            // This row is already open - close it
            row.child.hide();
        }
        else {
            // Open this row
            row.child(format(row.data())).show();
        }
    });

    subscriptionDataTable.on('click', '.get-payment-info-btn', function (e) {
        //console.log();
        var subscriptionID=$(this).attr('data-subscriptionID');
        $.ajax({

            type: "POST",
            url: url_ajax,
            dataType: 'json',
            data: {
                action:'getPaymentInfoPaypal',                    
                subscriptionID: subscriptionID,
            },
            success: function(response) {                    
                console.log(response);
                var paymentSubscriber=response.subscription_info.subscriber;
                var paymentcontent='';
                paymentcontent+='<p>Name: '+paymentSubscriber.name.given_name+' '+ paymentSubscriber.name.surname+'</p>';
                paymentcontent+='<p>Address 1: '+paymentSubscriber.shipping_address.address.address_line_1+' '+ paymentSubscriber.shipping_address.address.admin_area_1+'</p>';
                paymentcontent+='<p>Address 2: '+paymentSubscriber.shipping_address.address.address_line_2+' '+paymentSubscriber.shipping_address.address.admin_area_2+'</p>';
                paymentcontent+='<p>Postal code: '+paymentSubscriber.shipping_address.address.postal_code+'</p>';
                paymentcontent+='<p>Country code: '+paymentSubscriber.shipping_address.address.country_code+'</p>';                          
                paymentcontent+='<p>Email: '+paymentSubscriber.email_address+'</p>';
                
                $("#subscriptionDetailModal").find('.admin-subscription-modal').html(paymentcontent);                
                $("#subscriptionDetailModal").modal("show");
            },   
            error: function(error) {                    
                toastr.error('Something went wrong. Please refresh');
            }             
        });            
    });

    //handle add admin subscription area
    $("#setAdminSubscription").submit(function(e){
        e.preventDefault();  
        var formData = $(this).serialize();
        $.ajax({

            type: "POST",
            url: url_ajax,
            dataType: 'json',
            data: formData,
            success: function(response) {                    
                toastr.success(response.message);
                window.location.reload();
            },   
            error: function(error) {                    
                toastr.error('Something went wrong. Please refresh');
            }             
        });
    });

    $(".custom-btn-delete-admin-subscription").click(function(){
        let delete_email=$(this).attr('data-email');
        $.ajax({

            type: "POST",
            url: url_ajax,
            dataType: 'json',
            data: {
                action: 'delete_email_admin_sub',
                delete_email: delete_email,
            },
            success: function(response) {                    
                toastr.success(response.message);
                window.location.reload();
            },   
            error: function(error) {                    
                toastr.error('Something went wrong. Please refresh');
            }             
        });
    });

    });

})(jQuery);