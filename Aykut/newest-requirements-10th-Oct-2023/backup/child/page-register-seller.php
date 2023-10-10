<?php
/*
 * Template Name: Register Seller Page
 
 */

$profile_id=get_user_meta(get_current_user_id(),'user_profile_id',true);
$registered_seller=get_post_meta($profile_id,'registered_seller',true);
if($registered_seller)
{
	wp_redirect(site_url('/profile/'));	
}

get_header();

?>
<style>
  .custom-error-select
  {
    text-align: right;
    font-weight: normal !important;
    position: absolute;
    right: 0;top: 100%;
    margin-top: 5px;
    font-size:13px !important;
  }
</style>
<div id="content">
	 <div class="container dashboard withdraw">
	 	 <div class="row title-top-pages" >
               
         </div>

          <h2 class="text-center" style="margin-top:50px;margin-bottom: 50px;">Registrierung</h2>
          <div class="step-wrapper">
          	 <form class="post-job post et-form" id="registerSellerForm" name="registerSellerForm">
		        
          	 	<input type="hidden" name="verifyID" id="verifyID" value="<?php echo get_current_user_id(); ?>">

		        <div class="form-group clearfix">
		        	<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 category-area mje-mjob-category-field" style="">
			            <div class="input-group">
			                <label for="post_title" class="input-label">Vorname *</label>
			                <input type="text" class="input-item input-full" id="forename" name="forename" value="<?php echo $post_title;?>" required>
			            </div>
			        </div>

			        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 category-area mje-mjob-category-field" style="">
			        	<div class="input-group">
		                <label for="post_title" class="input-label">Nachname *</label>
		                <input type="text" class="input-item input-full" id="surname" name="surname" value="<?php echo $post_title;?>" required>
		           		 </div>

			        </div>


		        </div>

		       
		        <div class="form-group clearfix">
		        	<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 category-area mje-mjob-category-field" style="">
                		<div class="input-group">
                    <label for="mjob_category"><?php _e('Land *', 'enginethemes');?></label>
                    <?php
                    ae_tax_dropdown('country',
                    	array('attr' => 'data-chosen-width="100%" data-chosen-disable-search=""  data-placeholder="' . __("Choose country", 'enginethemes') . '"',
                    		'class' => 'chosen chosen-single tax-item required',
                    		'hide_empty' => false,
                    		'hierarchical' => true,
                    		'id' => 'customcountry',
                    		'name' => 'customcountry',
                    		'show_option_all' => false,
                    	)
                    );?>
                			</div>
            		</div>

            		<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 category-area mje-mjob-category-field" style="">
                		<div class="input-group">
                    <label for="mjob_category"><?php _e('Sprache *', 'enginethemes');?></label>
                    <?php
                    ae_tax_dropdown('language',
                    	array('attr' => ' data-chosen-width="100%" data-chosen-disable-search=""  data-placeholder="' . __("Choose languages", 'enginethemes') . '"',
                    		'class' => 'chosen chosen-single tax-item required',
                    		'hide_empty' => false,                    		
                    		'hierarchical' => true,
                    		'id' => 'customlanguage',
                    		'name' => 'customlanguage',
                    		'show_option_all' => false,
                    	)
                    );?>
                			</div>
            		</div>

            	</div>

            	 <div class="form-group clearfix">
		        	<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 category-area mje-mjob-category-field" style="">
                		<div class="input-group">
                    <label for="mjob_category"><?php _e('Höchster Abschluss *', 'enginethemes');?></label>
                    <?php
                    ae_tax_dropdown('degree',
                    	array('attr' => 'data-chosen-width="100%" data-chosen-disable-search=""  data-placeholder="' . __("Choose degree", 'enginethemes') . '"',
                    		'class' => 'chosen chosen-single tax-item required',
                    		'hide_empty' => false,
                    		'hierarchical' => true,
                    		'id' => 'degree',
                    		'name' => 'degree',
                    		'show_option_all' => false,
                    	)
                    );?>
                			</div>
            		</div>

            		<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 category-area mje-mjob-category-field" style="">
                		<div class="input-group">
                			  <label for="post_title" class="input-label">Abschlussjahr *</label>
		             <select id="CustomGraduationYear" name="CustomGraduationYear" data-chosen-width="100%" data-chosen-disable-search="" class="chosen chosen-single"> 
                 <option value="">Bitte auswählen</option>                
		               <?php 
                                            for ($x = date("Y"); $x >= 1900; $x-=1) 
                                            {
                                               
                        
                                                    echo '<option value="'.$x.'">';
                                                
                                                echo $x;
                                                echo '</option>';
                                             }
                                        ?>
		             </select>
                		</div>
                	</div>

            	</div>

               <div class="form-group clearfix">
                  <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 category-area mje-mjob-category-field" style="">
                      <div class="input-group">
                        <label for="tradingform" class="input-label">Du handelst als * </label>
                         <span data-toggle="tooltip" data-placement="top" title="Falls du dir nicht sicher sein solltest, welche von den Optionen auf dich zutrifft, erkundige dich bitte bei deinem zuständigen Finanzamt."><i style="font-size:18px;margin-left:5px;" class="fa fa-info-circle"></i></span>
                           <select id="tradingform" name="tradingform" data-chosen-width="100%" data-chosen-disable-search="" class="chosen chosen-single"> 
                           <option value="">Bitte auswählen</option>  
                           <option value="privateperson">Privatperson</option>
                           <option value="freelancer">Freelancer</option>
                           <option value="soletrader">Einzelkaufmann</option>
                           <option value="enterprise">Unternehmen</option>
                         </select>
                      </div>
                  </div>

                  <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 category-area mje-mjob-category-field" style="">
                        <div class="input-group">
                            <label for="phonenumber" class="input-label">Telefonnummer *</label>
                            <input placeholder="+491512345678" type="text" class="input-item input-full" name="phonenumber" id="phonenumber" value="<?php echo $post_title;?>" required>
                        </div>
                  </div>

               </div>

                <div class="form-group clearfix">
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 category-area">
                  <div class="input-group">
                      <label id="vatNumberLabel" for="VatNumber" class="input-label">Umsatzsteuernummer</label>
                      <input disabled="disabled" style="background-color: #dddddd;" required="required" type="text" class="input-full" name="VatNumber" id="VatNumber" value="">
                  </div>
              </div>
             </div>


            	<div class="form-group clearfix mje-mjob-title-field" style="margin-top:20px;">
            		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 category-area">
			            <div class="input-group">
			                <label for="post_title" class="input-label">Fach *</label>
			                <input required="required" type="text" class="input-full" name="major" id="major" value="<?php echo $post_title;?>">
			            </div>
		        	</div>
		        </div>


		        	

		        <div class="form-group clearfix mje-mjob-title-field" style="margin-top:20px;">
		        	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 category-area">
			            <div class="input-group">
			                <label for="post_title" class="input-label">Universität *</label>
			                <input type="text" class="input-item input-full" id="university" name="university" value="<?php echo $post_title;?>">
			            </div>
			        </div>
		        </div>

		        <div class="form-group clearfix mje-mjob-des-field" style="margin-bottom:40px;">
		        	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 category-area">
			            <div class="input-group">
			                <label class="mb-20"><?php _e('Beschreibung', 'enginethemes')?></label>
			                <?php wp_editor($post_content, 'customdescription', ae_editor_settings());?>
			            </div>
        			</div>
       			 </div>



		        
		     
		        <button class="waves-effect waves-light btn-submit" type="submit">Speichern</button>

            <div class="form-group" style="margin-top:30px;">
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 category-area">
                  <div class="input-group">
                      <label class="input-label"></label>
                      <h5>*Pflichtfelder</h5>
                       <h5>Einmalige Registrierung notwendig, um Services zu veröffentlichen.</h5>
                     
                  </div>
              </div>
            </div>

		    </form>
          </div>

	 </div>

</div>

<script type="text/javascript">
    (function ($) {
  $(document).ready(function () {

      $('[data-toggle="tooltip"]').tooltip(); 

      $('#tradingform').change(function(){
         if($(this).val()=='enterprise' || $(this).val()=='soletrader' || $(this).val()=='freelancer')
         {
           $("#VatNumber").attr('disabled',false);
           $("#VatNumber").css('background-color','#fff');
           if($(this).val()=='soletrader' || $(this).val()=='enterprise')
           {
            $('#vatNumberLabel').text('Handelsregisternummer');
           }
           else
           {
              $('#vatNumberLabel').text('Umsatzsteuernummer');
           }
         }
         else
         {
           $('#vatNumberLabel').text('Umsatzsteuernummer');
           $("#VatNumber").attr('disabled','disabled');
           $("#VatNumber").css('background-color','#dddddd');
           $("#VatNumber").parent().removeClass('has-error');
            $("#VatNumber").removeClass('has-visited');
            $("#VatNumber").parent().find('.error').remove();
            $("#VatNumber").val('');
         }
      });

    function AddSelectText()
    {
      $("#degree").prepend("<option value=''>Bitte auswählen</option");      
      $("#degree").val($("#degree option:first").val());

      $("#customcountry").prepend("<option value=''>Bitte auswählen</option");      
      $("#customcountry").val($("#customcountry option:first").val());

      $("#customlanguage").prepend("<option value=''>Bitte auswählen</option");      
      $("#customlanguage").val($("#customcountry option:first").val());


    }

    AddSelectText();

    function ValidateCustomSelect(element)
    {
      var getElement="#"+element+"_chosen";
      $(getElement).addClass('has-visited');
      $(getElement).parent().addClass('has-error');
      $(getElement).parent().append('<label id="'+element+'Error" class="custom-error-select">Dieses Feld ist erforderlich.</label>') ;         
      $(getElement).css('border-bottom','1px solid #e52e5d');
    }
        
  /*  $("#CustomGraduationYear").change(function(){
      if($(this).val() !== '')
      {
        $("#CustomGraduationYear_chosen").removeClass('has-visited');
        $("#CustomGraduationYear_chosen").parent().removeClass('has-error');
        $("#CustomGraduationYear_chosen").css('border-bottom','none'); 
        $("#CustomGraduationYearError").remove();          
      }
    }); */

    $('select').on('change', function() {
          if($(this).val() !== '')
          {
            
            var currentElement="#"+$(this).attr('id')+"_chosen";
            var labelError="#"+$(this).attr('id')+"Error";
            $(currentElement).removeClass('has-visited');
            $(currentElement).parent().removeClass('has-error');
            $(currentElement).css('border-bottom','none'); 
            $(labelError).remove();  
          }
      });

       $("#registerSellerForm").submit(function(){
        var graduationYearValidate=$("#CustomGraduationYear").val();
        var degreeValidate=$("#degree").val();
        var customcountryValidate=$("#customcountry").val();
        var customlanguageValidate=$("#customlanguage").val();
        var tradingformValidate=$("#tradingform").val();

        if(graduationYearValidate=='')
        {
          ValidateCustomSelect('CustomGraduationYear');      
        }

          if(degreeValidate=='')
        {
          ValidateCustomSelect('degree');      
        }

           if(customcountryValidate=='')
        {
          ValidateCustomSelect('customcountry');      
        }

            if(customlanguageValidate=='')
        {
          ValidateCustomSelect('customlanguage');      
        }

        if(tradingformValidate== '')
        {
            ValidateCustomSelect('tradingform');
        }




       });



     $("#registerSellerForm").validate({
       
 
    rules: {
      forename: "required",
      surname: "required",
      university: "required",      
      phonenumber: {
      		required: true,
   		//	number: true,
   		},
      CustomGraduationYear: {
       required: true,
    },



    },

    
    /*  messages: {
    forename: "Please enter your forename",
      university: "Please enter your university",
      password: {
        required: "Please provide a password",
        minlength: "Your password must be at least 5 characters long"
      },
      password_confirmation: {
        required: "Please provide a password",
        minlength: "Your password must be at least 5 characters long"
      },
      email: "Please enter a valid email address",
      
    },*/
	
    highlight:function(element, errorClass, validClass)
    {                       
                        var $target = $(element );
                        var $parent = $(element ).parent();
                        $parent.addClass('has-error');
                        $target.addClass('has-visited');  

    },
                    
    unhighlight:function(element, errorClass, validClass)
    {
                        // position error label after generated textarea
                        var $target = $(element );
                        var $parent = $(element ).parent();
                        $parent.removeClass('has-error');
                        $target.removeClass('has-visited');
    },


    submitHandler: function(form,e) 
    {    	
       e.preventDefault();  
       var forename=$('#forename').val();
       var surname=$("#surname").val();       
       var verifyID=$("#verifyID").val();
       var country=$("#customcountry").val();
       var university=$("#university").val();
       var major=$("#major").val();
       var degree=$("#degree").val();
       var language=$("#customlanguage").val();
       var graduationYear=$("#CustomGraduationYear").val();
       var description=$("#customdescription").val();
       var sellertype= $('#tradingform').val();
       var phonenumber=$("#phonenumber").val();

        var VatNumber='';
        if($("#VatNumber").val() !== '' && ( $("#tradingform").val() == 'enterprise' || $("#tradingform").val() == 'freelancer'  || $("#tradingform").val() == 'soletrader' ))
       {
          VatNumber=$("#VatNumber").val();
       }


        $.ajax({

                type: "POST",
                url: ae_globals.ajaxURL,
                dataType: 'json',
                data: {
                    action:'verify_seller',
                    forename: forename,
                    surname: surname,
                    verifyID: verifyID,
                    country : country,
                    university: university,
                    major: major,
                    graduationYear: graduationYear,
                    degree: degree,
                    language: language,
                    description: description,
                    sellertype: sellertype,
                    phonenumber: phonenumber,
                    VatNumber: VatNumber,
                },

                success: function (response) 
                {                 	                	
                	if(response.data.confirm=='success')
                	{
                		toastr.success(response.data.message);
                		window.location.href = response.data.redirect_url;
                	}
                	else
                	{
                		toastr.error(response.data.message);
                	}
                	
             	
                }

                }); 

    },

 });

      });
})(jQuery);
</script>

<?php
get_footer();