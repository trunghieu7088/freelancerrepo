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

<div id="content">
	 <div class="container dashboard withdraw">
	 	 <div class="row title-top-pages" >
                <p class="block-title">Register Seller Page</p>
         </div>

          <div class="row block-posts blog-pages" id="post-control" style="margin-top:0px !important;">
          		<h4 style="margin-left:15px;">You are not the registered seller, you need to create seller profile to post service</h4>
          		
          </div>
          <h3 class="text-center" style="margin-top:50px;margin-bottom: 50px;">Register Seller Form</h3>
          <div class="step-wrapper">
          	 <form class="post-job post et-form" id="registerSellerForm" name="registerSellerForm">
		        
          	 	<input type="hidden" name="verifyID" id="verifyID" value="<?php echo get_current_user_id(); ?>">

		        <div class="form-group clearfix">
		        	<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 category-area mje-mjob-category-field" style="">
			            <div class="input-group">
			                <label for="post_title" class="input-label">Forename</label>
			                <input type="text" class="input-item input-full" id="forename" name="forename" value="<?php echo $post_title;?>" required>
			            </div>
			        </div>

			        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 category-area mje-mjob-category-field" style="">
			        	<div class="input-group">
		                <label for="post_title" class="input-label">Surname</label>
		                <input type="text" class="input-item input-full" id="surname" name="surname" value="<?php echo $post_title;?>" required>
		           		 </div>

			        </div>


		        </div>

		       
		        <div class="form-group clearfix">
		        	<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 category-area mje-mjob-category-field" style="">
                		<div class="input-group">
                    <label for="mjob_category"><?php _e('Country', 'enginethemes');?></label>
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
                    <label for="mjob_category"><?php _e('Language', 'enginethemes');?></label>
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
                    <label for="mjob_category"><?php _e('Degree', 'enginethemes');?></label>
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
                			  <label for="post_title" class="input-label">Graduation Year</label>
		             <select id="custom-graduation-year" name="custom-graduation-year" data-chosen-width="100%" data-chosen-disable-search="" class="chosen chosen-single">
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

            	<div class="form-group clearfix mje-mjob-title-field" style="margin-top:20px;">
            		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 category-area">
			            <div class="input-group">
			                <label for="post_title" class="input-label">Major</label>
			                <input required="required" type="text" class="input-full" name="major" id="major" value="<?php echo $post_title;?>">
			            </div>
		        	</div>
		        </div>

		        	

		        <div class="form-group clearfix mje-mjob-title-field" style="margin-top:20px;">
		        	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 category-area">
			            <div class="input-group">
			                <label for="post_title" class="input-label">University</label>
			                <input type="text" class="input-item input-full" id="university" name="university" value="<?php echo $post_title;?>">
			            </div>
			        </div>
		        </div>

		        <div class="form-group clearfix mje-mjob-des-field" style="margin-bottom:40px;">
		        	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 category-area">
			            <div class="input-group">
			                <label class="mb-20"><?php _e('Description', 'enginethemes')?></label>
			                <?php wp_editor($post_content, 'customdescription', ae_editor_settings());?>
			            </div>
        			</div>
       			 </div>

       			<div class="form-group clearfix">
		        	<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 category-area mje-mjob-category-field" style="">
                		<div class="input-group">
                			<label  for="sellertype" class="input-label">TradingForm</label>                			
                			 <br><br>
                			  <input type="radio" id="sellertype1" name="sellertype" value="privateperson" checked>
							  <label style="font-size:14px;" for="sellertype1">Private person</label>
							  <input type="radio" id="sellertype2" name="sellertype" value="enterprise">
							  <label style="font-size:14px;" for="sellertype2">Enterprise</label>
                		</div>
                	</div>
                </div>


		         <div class="form-group clearfix" style="margin-top:20px;">
		         	<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 category-area mje-mjob-category-field" style="">
			            <div class="input-group">
			                <label for="phonenumber" class="input-label">Phone Number</label>
			                <input placeholder="49 15 1234 5678" type="text" class="input-item input-full" name="phonenumber" id="phonenumber" value="<?php echo $post_title;?>" required>
			            </div>
		        	</div>

		        

		        </div>

		     
		        <button class="waves-effect waves-light btn-submit" type="submit">SAVE</button>



		    </form>
          </div>

	 </div>

</div>

<script type="text/javascript">
    (function ($) {
  $(document).ready(function () {
        
  

     $("#registerSellerForm").validate({
    rules: {
      forename: "required",
      surname: "required",
      university: "required",
      phonenumber: {
      		required: true,
   			number: true,
   		}

    },

    /*
    messages: {
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
      email: "Please enter a valid email address"
    },
	*/
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
       var graduationYear=$("#custom-graduation-year").val();
       var description=$("#customdescription").val();
       var sellertype= $('input[name="sellertype"]:checked').val();
       var phonenumber=$("#phonenumber").val();

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

