(function ($, Models, Collections, Views) {
$(document).ready(function($){

 $(".flash-message").click(function(){

   $(this).css('display','none');
 });

$("#extraform").submit(function(event){     
      event.preventDefault();
   
      var urgentlabel=$("#urgentlabel").val();
      var urgentdescription=$("#urgentdescription").val();
      var urgentprice=$("#urgentprice").val();

      var privatelabel=$("#privatelabel").val();
      var privatedescription=$("#privatedescription").val();
      var privateprice=$("#privateprice").val();

      var avaiblecredit=$("#avaiblecredit").val();
      var totalcost=$("#totalcost").val();
      var yourbalance=$("#yourbalance").val();
      var addcredits=$("#addcredits").val();

     var emailtitle=$("#emailtitle").val();
      var emailcontent=$("#emailcontent").val();
      var credithistory=$("#credithistory").val();
     

         $.ajax({

                type: "post",
                url: ae_globals.ajaxURL,
                dataType: 'json',
                data: {
                    action:'save_extra_options',
                    urgentlabel: urgentlabel,
                    urgentdescription: urgentdescription,
                    urgentprice: urgentprice,
                    privatelabel: privatelabel,
                    privatedescription: privatedescription,
                    privateprice: privateprice,
                    avaiblecredit: avaiblecredit,
                    totalcost: totalcost,
                    yourbalance: yourbalance,
                    addcredits: addcredits,
                    emailtitle: emailtitle,
                    emailcontent: emailcontent,
                    credithistory: credithistory,

                },
                success: function (response) 
                { 
                  $(".flash-message").css('display','block');
                   $(".flash-message").html(response.data.adminnotice);
                  //console.log(response);
               }

                });
        
   });



		      
});
})(jQuery);	