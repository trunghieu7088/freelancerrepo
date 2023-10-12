<?php
/*
 * Template Name: Validation Page
 
 */
get_header();

if(!is_user_logged_in())
{
    wp_redirect( home_url());
    exit;
}
//do not allow employer to access this page 
$user_validation= wp_get_current_user();
$user_profile_id_check=get_user_meta($user_validation->ID,'user_profile_id',true);
if(in_array('employer',$user_validation->roles))
{
     wp_redirect( home_url());
    exit;
}

//get validation document
$current_user_id=get_current_user_id();
$args=array('post_type'=>'validation',
            'post_status'=>array('publish','pending'),
            'author'=>$current_user_id,
            'numberposts'=>1,
);

$document_validation=get_posts($args);

//echo $document_validation[0]->post_title;

if(empty($document_validation))
{
    $is_show_validationForm=true;
}
else
{
    $approve_status=get_post_meta($document_validation[0]->ID,'approve_status',true);
    if($approve_status=='pending')
    {
        $is_show_validationForm=false;
    }
     if($approve_status=='reject')
     {
        $is_show_validationForm=true;
        //get reject reason
        $reject_reason=get_post_meta($document_validation[0]->ID,'reject_reason',true);
        
        $edit_validation_id=$document_validation[0]->ID;
     }

     if($approve_status=='publish')
     {
        $is_show_validationForm=false;
     }

      $full_name=get_post_meta($document_validation[0]->ID,'full_name',true);
  $address=get_post_meta($document_validation[0]->ID,'address',true);
  $birthday=get_post_meta($document_validation[0]->ID,'birthday',true);
  $identification_number=get_post_meta($document_validation[0]->ID,'identification_number',true);
  $validation_type=get_post_meta($document_validation[0]->ID,'validation_type',true);
     
     //$string = "2010-11-24";
     $date_birthday = DateTime::createFromFormat("Y-m-d",$birthday);
    // echo $date_birthday->format("d");
}

?>

<div class="fre-page-wrapper list-profile-wrapper">
    <div class="fre-page-title">
        <div class="container">
            <h2>Validation Page</h2>
        </div>
    </div>


    <div class="fre-page-section">
        <div class="container" style="min-height: 500px;background-color: #fff;border:1px solid #E8E8E8;">
            <div class="page-notification-wrap" id="fre_notification_container" style="padding:25px;">
                

                 <?php if ( !$user_profile_id_check) : ?>
                    <h4 style="padding:10px;border:1px solid #A52A2A;border-radius:5px;line-height: 25px;" class="text-danger bg-danger">
                        You have to create a <a href="<?php echo site_url('/profile/'); ?>">profile</a> to submit validation document
                               
                            </h4>

                           </div>
                       </div>
                   </div>

                <?php get_footer(); exit;  endif; ?> 


                 <?php if ( $approve_status=='reject') : ?>
                    <div class="row">
                        <div class="col-md-12 col-sm-12" >
                             
                            <h4 style="padding:10px;border:1px solid #A52A2A;border-radius:5px;line-height: 25px;" class="text-danger bg-danger">Admin has rejected your validation document. Please update the document
                                <br>
                                Reason : <?php echo $reject_reason; ?>
                            </h4>
                        </div>
                    </div>
                 <?php endif; ?> 

                <?php if ( $approve_status=='pending' || $approve_status=='publish') : ?>
                    <div class="row">
                        <div class="col-md-12 col-sm-12" >
                             <?php if ( $approve_status=='pending') : ?>
                            <h4 style="padding:10px;border:1px solid #E1C16E;border-radius:5px;" class="text-warning bg-warning">You have sent the validation document to admin please wait for approval
                            </h4>
                              <?php endif; ?>

                              <?php if ( $approve_status=='publish') : ?>
                                <h4 style="padding:10px;border:1px solid #AFE1AF;border-radius:5px;" class="text-succcess bg-success">Your validation document has been verified by admin
                            </h4>
                              <?php endif; ?> 

                        </div>

                        <div class="col-md-12 col-sm-12" style="padding:20px;">


                            <div style="border:1px solid #C0C0C0;border-radius: 5px;">
                             <h4 style="padding:10px;background-color: #C0C0C0;margin-top: 0px !important;margin-bottom: 0px !important;">Information : </h4>
                             <div class="validation_content_info">
                                <ul style="margin-top: 10px;line-height: 30px;">
                                    <?php 

                                 echo '<li> Full name : <strong>'. $full_name.'</strong></li>';
                                 echo '<li> Address : <strong>'. $address.'</strong></li>';
                                 echo '<li> Birthday (Y / m / d ) : <strong>'. $birthday.'</strong></li>';
                                 echo '<li> Identification Number : <strong>'. $identification_number.'</strong></li>';
                                 echo '<li> Validation Type : <strong>'. $validation_type.'</strong></li>';
                                 echo '<li>Attachment files : </li>';

                                 $args=array('numberposts'=>-1,
                                        'post_type'=>'attachment',
                                        'post_parent'=>$document_validation[0]->ID,
                                    );
                                $document_attachments=get_posts($args);

                                foreach($document_attachments as $document_item)
                                {
                                    echo '<a target="_blank" href="'.wp_get_attachment_url($document_item->ID).'">';
                                    echo  $document_item->post_title;
                                    echo '</a>';
                                    echo '<br>';
                                }

                                ?>    
                                
                                </ul>
                           
                            </div>
                        </div>
                        </div>

                    </div>
                <?php endif; ?>


                <?php if ( $is_show_validationForm==true ) : ?> 
                <h3>Basic Information</h3>
                <div class="row">

                    <div class="col-sm-8 col-md-8" style="margin-top:20px;border-right:1px solid #E8E8E8;min-height:500px;">
                        <form name="validation-form" id="validation-form" enctype="multipart/form-data">
                            
                             <input type="hidden" id="edit_validation_id" name="edit_validation_id" value="<?php if($approve_status=='reject') echo $edit_validation_id; ?>">

                            <input type="hidden" id="user_validation_id" name="user_validation_id" value="<?php echo get_current_user_id(); ?>">

                        <div class="fre-input-field">
                          <label class="fre-field-title" for="validation_full_name"><?php _e('Complete Name', ET_DOMAIN);?></label>
                            <input class="text-field" id="validation_full_name" type="text" name="validation_full_name" value="<?php if($approve_status=='reject') echo $full_name; ?>" placeholder="Please enter your complete name">
                        </div>

                        <div class="fre-input-field">
                          <label class="fre-field-title" for="validation_identification_number"><?php _e('Identification Number', ET_DOMAIN);?></label>
                            <input class="text-field" id="validation_identification_number" type="text" name="validation_identification_number" value="<?php if($approve_status=='reject') echo $identification_number; ?>" placeholder="Please enter your identification number">
                        </div>

                          <div class="fre-input-field">
                          <label class="fre-field-title" for="validation_date_of_birth"><?php _e('Date of Birth', ET_DOMAIN);?></label>
                          <br>
                           
                            <select class="text-center" style="width:20%;margin-right:10px;" id="validation_date" name="validation_date">
                                <option value="">Day</option>
                                <?php 
                          
                                for($day=1;$day<=31;$day++)
                                {
                                    if(isset($date_birthday) && $date_birthday->format("d")==$day && $approve_status=='reject')
                                    {
                                        echo  '<option selected="selected" value="'.$day.'">';
                                    }
                                    else
                                    {
                                         echo '<option value="'.$day.'">';
                                    }                  
                                    echo $day;
                                    echo '</option>';
                                }
                                ?>
                            </select>
                            
                              <select class="text-center" style="width:20%;margin-right:10px;" id="validation_month" name="validation_month">
                                <option value="">Month</option>
                                <?php 
                               
                                for($month=1;$month<=12;$month++)
                                {
                                    if(isset($date_birthday) && ((int)$date_birthday->format("m")) == $month && $approve_status=='reject')
                                    {
                                        echo  '<option selected="selected" value="'.$month.'">';
                                    }
                                    else
                                    {
                                         echo '<option value="'.$month.'">';
                                    }                  
                                    echo $month;
                                    echo '</option>';
                                }
                                ?>
                            </select>

                            <select class="text-center" style="width:20%;margin-right:10px;" id="validation_year" name="validation_year">
                                <option value="">year</option>
                                <?php 
                                $year=date('Y');
                                for($year;$year>=1900;$year--)
                                {
                                    if(isset($date_birthday) && $date_birthday->format("Y") == $year && $approve_status=='reject')
                                    {
                                        echo  '<option selected="selected" value="'.$year.'">';
                                    }
                                    else
                                    {
                                         echo '<option value="'.$year.'">';
                                    }                  
                                    echo $year;
                                    echo '</option>';
                                }
                                ?>
                            </select>

                        </div>

                        <div class="fre-input-field">
                          <label class="fre-field-title" for="validation_address"><?php _e('Address', ET_DOMAIN);?></label>
                            <input class="text-field" id="validation_address" type="text" name="validation_address" placeholder="Please enter your address" value="<?php if($approve_status=='reject') echo $address; ?>">
                        </div>

                        <h3>Document</h3>
                        <br>
                        <div class="fre-input-field">
                          <label class="fre-field-title" for="validation_type_doc"><?php _e('Document Type', ET_DOMAIN);?></label>
                          <br>
                          <select style="width:50%" name="validation_type_doc" id="validation_type_doc">

                            <option  <?php if($approve_status=='reject' && $validation_type=='idcard') echo 'selected'; ?> value="idcard">ID CARD</option>
                            <option  <?php if($approve_status=='reject' && $validation_type=='driver_license') echo 'selected'; ?> value="driver_license">Driver License</option>
                          </select>

                        </div>

                    
                           <div class="fre-input-field" style="border:1px solid #E8E8E8;padding:10px;">
                            <label class="fre-field-title" for="validation_attachment_file">Document</label>
                            <p>The photo of your Document</p>
                            <input type="file" id="validation_attachment_file" accept=".jpg,.png,.jpeg,.doc,.docx,.pdf" name="validation_attachment_file">
                           </div>

                           <div class="fre-input-field" style="border:1px solid #E8E8E8;padding:10px;">
                            <label class="fre-field-title" for="validation_attachment_file2">Selfie holding the document</label>
                            <p>take your photo holding your document and follow the instructions on the right</p>
                            <input type="file" id="validation_attachment_file2" accept=".jpg,.png,.jpeg,.doc,.docx,.pdf" name="validation_attachment_file2">
                           </div>

                        <input type="submit" class="fre-btn" value="Submit Validation" id="submit_validation" name="submit_validation">

                    </form>
                    </div>

                    <div class="col-sm-4 col-md-4" style="margin-top:20px;min-height:500px;">
                        <h3>Importante</h3>
                        <p>
                        - The profile name at flecitwork and the name in this identification page should be the same or similar</p>
                       <p>
                        - The document should include your name , photo and date of birth.
                        </p> 

                         <p>- show all the document ( without cutting the edges).</p>
                       
                       <p> - The document should be clear not blurry.</p>
                        
                       <p> - The document cannot be edited.</p>
                      
                        <p> - accepted jpeg, png, pdf.</p>

                        <p> - the profile photo at flexitwork and the id photo should be yours ( it does not have to be the same photo )</p>

                         <p> - make sure you have enough light and your face is visible and centered</p>

                          <p> - não use excesso de maquilhagem</p> 

                           <p> - nao use chapeus </p>   

                            <p> - o document dever ser nitido e legivel </p>

                            <p> <strong> Exemple </strong></p>
                            <div class="text-center">
                                <img style="width:80%;height:80%" src="<?php echo get_stylesheet_directory_uri().'/assets/img/selfieID.png'; ?>">
                             </div>
                   
                    </div>


                </div>

                <?php endif; ?>


            </div>
        </div>
    </div>

</div>

<?php
get_footer();
?>

<script type="text/javascript">
(function ($, Models, Collections, Views) {

  $(document).ready(function () {


    $("#validation_attachment_file").change(function () {
        var fileExtension = ['jpeg', 'jpg', 'png', 'doc', 'docx','pdf'];
        if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
           // alert("Only formats are allowed : "+fileExtension.join(', '));

              AE.pubsub.trigger('ae:notification', {
                        msg: "Only formats are allowed : "+fileExtension.join(', '),
                        notice_type: 'error',
                    });
            $(this).val('');
        }
        if($("#validation_attachment_file")[0].files[0].size  > 5000000)
        {
            //alert('You can only upload file < 5 MB');
            AE.pubsub.trigger('ae:notification', {
                        msg: "You can only upload file < 5 MB",
                        notice_type: 'error',
                    });

            $(this).val('');
        }
    });

     $("#validation_attachment_file2").change(function () {
        var fileExtension = ['jpeg', 'jpg', 'png', 'doc', 'docx','pdf'];
        if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
           // alert("Only formats are allowed : "+fileExtension.join(', '));

              AE.pubsub.trigger('ae:notification', {
                        msg: "Only formats are allowed : "+fileExtension.join(', '),
                        notice_type: 'error',
                    });
            $(this).val('');
        }
        if($("#validation_attachment_file2")[0].files[0].size  > 5000000)
        {
            //alert('You can only upload file < 5 MB');
            AE.pubsub.trigger('ae:notification', {
                        msg: "You can only upload file < 5 MB",
                        notice_type: 'error',
                    });

            $(this).val('');
        }
    });
    

        $( "#validation-form" ).validate({
            rules: 
            {
                validation_full_name: {
                    required: true
                },
                validation_identification_number: {
                    required: true
                },
                validation_address: {
                    required: true
                },
                validation_attachment_file: {
                    required: true,
                     
                },

                validation_attachment_file2: {
                    required: true,
                },
                validation_date: {
                    required: true,
                    min: 1,
                },
                validation_month: {
                    required: true,
                     min: 1,
                },

                  validation_year: {
                    required: true,
                     min: 1,
                }

            },
            messages:
            {
                 validation_date: {
                    required: "Please select day"
                },
                validation_month: {
                    required: "Please select month"
                },
                validation_year: {
                    required: "Please select year"
                },
            },

            submitHandler: function(form,e) {
                e.preventDefault();
              //  console.log('van ok roi');

                //submit document to server

                var complete_name=$("#validation_full_name").val();
                var identification_number=$("#validation_identification_number").val();
                var validate_address=$("#validation_address").val();
                var validation_date=$("#validation_date").val();
                var validation_month=$("#validation_month").val();
                var validation_year=$("#validation_year").val();
                var validation_id=$('#user_validation_id').val();
                var validation_type_doc=$('#validation_type_doc').val();

                
                 
                   

                 $.ajax({

                type: "POST",
                url: ae_globals.ajaxURL,
                dataType: 'json',
                data: {
                    action:'create_validation_document',
                    name: complete_name,
                    number: identification_number,
                    address: validate_address,
                    day: validation_date,
                    month: validation_month,
                    year: validation_year,
                    user_id_sent: validation_id,
                    type_doc: validation_type_doc,
                    validation_edit: $("#edit_validation_id").val()
                },
                success: function (response) { 
                    //console.log(response.data);
                    var notice;
                    if(response.data.success==true)
                    {
                        notice='success';

                   
                        var form_data = new FormData();
                        var file_data = $("#validation_attachment_file")[0].files[0];
                        var file_data2=$("#validation_attachment_file2")[0].files[0];
                        var document_id=response.data.validation_id;
                        form_data.append('file[]', file_data);
                        form_data.append('file[]', file_data2);
                        form_data.append('action', 'upload_image_validation');
                        form_data.append('validation_id_send', document_id);
                        form_data.append('edit_attachment',response.data.edit_attachment);

                        $.ajax({
                            url: ae_globals.ajaxURL,
                            type: 'POST',
                            dataType: 'json', 
                            data: form_data,
                            processData: false,
                            contentType: false,

                           
                            success: function (response) {
                                console.log('success');
                            },  
                            error: function (response) {
                                console.log('error');
                            }

                        });

                    }
                    else
                    {
                        notice='error';
                    }

                      AE.pubsub.trigger('ae:notification', {
                        msg: response.data.message,
                        notice_type: notice,
                    });

                      window.location.href=response.data.redirect_url;

                    }
                });


            } 
});

  });
})(jQuery);
</script>