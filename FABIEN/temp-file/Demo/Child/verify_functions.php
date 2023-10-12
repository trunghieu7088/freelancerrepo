<?php
function add_page_for_validation()
{
$PageGuid = site_url() . "/validation-page";
$check_exist=get_page_by_title('Validation Page');
      if(empty($check_exist))
      {
        $validation_page = array( 'post_title'     => 'Validation Page',
                         'post_type'      => 'page',
                         'post_name'      => 'validation-page',
                         'post_content'   => '',
                         'post_status'    => 'publish',
                         'comment_status' => 'closed',
                         'ping_status'    => 'closed',
                         'post_author'    => 1,
                         'menu_order'     => 0,
                         'guid'           => $PageGuid );

      $validation_page_id=wp_insert_post( $validation_page, FALSE ); 
      update_post_meta($validation_page_id,'_wp_page_template','page-validation.php');
      }

}

add_action( 'init', 'add_page_for_validation' );

function create_validation_document_init()
{
    if(get_current_user_id() != $_POST['user_id_sent'])
    {
      $result['message']='There is something wrong, please refresh and try again';
      $result['success']=false;
      
    }
    else
    {
      if(isset($_POST['validation_edit']) && !empty($_POST['validation_edit']))
      {
           //only update document

              update_post_meta($_POST['validation_edit'],'full_name',$_POST['name']);
             update_post_meta($_POST['validation_edit'],'identification_number',$_POST['number']);
             update_post_meta($_POST['validation_edit'],'address',$_POST['address']);

             $birthday=new DateTime();
             $birthday->setDate($_POST['year'], $_POST['month'], $_POST['day']);
             update_post_meta($_POST['validation_edit'],'birthday',$birthday->format('Y-m-d'));

             update_post_meta($_POST['validation_edit'],'validation_type',$_POST['type_doc']);

             update_post_meta($_POST['validation_edit'],'approve_status','pending');
             update_post_meta($_POST['validation_edit'],'reject_reason','');

          $result['message']='You have updated the document successfully';
          $result['success']=true;
          $result['validation_id']= $_POST['validation_edit'];
          $result['edit_attachment']='edit';
          $result['redirect_url']=site_url('validation-page');
      }
      else
      {
           $document_array=array('post_title'=>'Validation '.get_the_author_meta('display_name',$_POST['user_id_sent']),
                              'post_status'=>'pending',
                              'post_type'=>'validation',
                              'post_author' => get_current_user_id(),

           );
             $document_id=wp_insert_post($document_array);

             //add information for document and save DB
             update_post_meta($document_id,'full_name',$_POST['name']);
             update_post_meta($document_id,'identification_number',$_POST['number']);
             update_post_meta($document_id,'address',$_POST['address']);

             $birthday=new DateTime();
             $birthday->setDate($_POST['year'], $_POST['month'], $_POST['day']);
             update_post_meta($document_id,'birthday',$birthday->format('Y-m-d'));

             update_post_meta($document_id,'validation_type',$_POST['type_doc']);

             update_post_meta($document_id,'approve_status','pending');
             update_post_meta($document_id,'reject_reason','');


             $result['message']='You have sent the document successfully';
             $result['success']=true;
             $result['validation_id']= $document_id;
             $result['redirect_url']=site_url('validation-page');
        }

        //send email nofitication to admin :            
                $mailer_custom=AE_Mailing::get_instance();            
                $admin_email=get_bloginfo('admin_email'); 
              
                $link=get_site_url(null,'/wp-admin/post.php?post='.$result['validation_id'].'&action=edit');
                $content='A user has submitted a validation document';
                $content.='<br>'.'Please go to this page to verify the validation document :';
                $content.='<br>'.'<a href="'.$link.'">'.get_the_author_meta('display_name',get_current_user_id()).'</a>';
                $mailer_custom->wp_mail( $admin_email,
                  'New Validation Submit',$content);

          wp_send_json_success($result);
            die();
    }

      
   
}


add_action( 'wp_ajax_create_validation_document', 'create_validation_document_init' );

function create_nopriv_validation_document_init()
{
   $result['message']='Please login to validate';
   $result['success']=false;
        wp_send_json_success($result);
      die();
}

add_action( 'wp_ajax_nopriv_create_validation_document', 'create_nopriv_validation_document_init' );

function upload_image_validation_init()
{
  //delete old attachments if edit mode 
  if(isset($_POST['edit_attachment']))
  {

    $args=array('numberposts'=>-1,
        'post_type'=>'attachment',
        'post_parent'=>$_POST['validation_id_send'],
    );
    $document_attachments_delete=get_posts($args);

    foreach($document_attachments_delete as $document_item_delete)
    {
      wp_delete_attachment($document_item_delete->ID);
    }
    
  }

    $uploadedfile = $_FILES['file'];
    $upload_overrides = array('test_form' => false);
  

      $files = $_FILES['file'];

      foreach ( $files['name'] as $key => $value ) {
  if ( $files['name'][ $key ] ) {
    $file = array(
      'name' => $files['name'][ $key ],
      'type' => $files['type'][ $key ],
      'tmp_name' => $files['tmp_name'][ $key ],
      'error' => $files['error'][ $key ],
      'size' => $files['size'][ $key ]
    );

    $movefile = wp_handle_upload( $file, $upload_overrides );
   // $result[]= $movefile;

    //insert attachments 
    $attachment = array(
      'guid'           => $movefile['url'], 
      'post_mime_type' => $movefile['type'],
      'post_title'     => $files['name'][ $key ],
      'post_content'   => 'attachment for validation',
      'post_status'    => 'inherit'
    );

    $attach_id = wp_insert_attachment( $attachment, $files['name'][ $key ] , $_POST['validation_id_send'] );
    update_post_meta($attach_id,'_wp_attached_file',str_replace(site_url().'/wp-content/uploads/','',$movefile['url']));
    }
  }
 
   wp_send_json_success($result);
  die();
}

add_action( 'wp_ajax_upload_image_validation','upload_image_validation_init' );

function add_custom_validation_manager()
{
       $args = array(
          'public' => true,
          'show_ui' =>true,
          'show_in_menu'=>true,
           'supports' => array( 'title', 'editor', 'custom-fields' ),

            'labels' => array(
                'name' => __("validation", 'enginethemes'),
                'singular_name' => __('validation', 'enginethemes'),
                'add_new' => __('Add New', 'enginethemes'),
                'add_new_item' => __('Add New validation', 'enginethemes'),
              
                'edit_item' => __('Edit validation', 'enginethemes'),
                'new_item' => __('New validation', 'enginethemes'),
                'all_items' => __('All validation', 'enginethemes'),
                'view_item' => __('View validation', 'enginethemes'),
                'search_items' => __('Search validation', 'enginethemes'),
                'not_found' => __('No Microjobs found', 'enginethemes'),
                'not_found_in_trash' => __('No Microjobs found in Trash', 'enginethemes'),
                'parent_item_colon' => '',
                'menu_name' => __('validation', 'enginethemes')
            ),

            'menu_icon' => 'dashicons-admin-users'
        );
        register_post_type('validation',$args);
}
add_action('init','add_custom_validation_manager');

//show information for detail validation in admin 

function validation_meta_box()
{
 add_meta_box( 'validation-box', 'Validation Information', 'validation_info_output', 'validation' );
}
add_action( 'add_meta_boxes', 'validation_meta_box' );

function validation_info_output($post)
{
  $full_name=get_post_meta($post->ID,'full_name',true);
  $address=get_post_meta($post->ID,'address',true);
  $birthday=get_post_meta($post->ID,'birthday',true);
  $identification_number=get_post_meta($post->ID,'identification_number',true);
  $validation_type=get_post_meta($post->ID,'validation_type',true);

  $args=array('numberposts'=>-1,
        'post_type'=>'attachment',
        'post_parent'=>$post->ID,
  );
  $document_attachments=get_posts($args);
  

  echo '<p> Full name : <strong>'. $full_name.'</strong></p>';
  echo '<p> Address : <strong>'. $address.'</strong></p>';
  echo '<p> Birthday (Y / m / d ) : <strong>'. $birthday.'</strong></p>';
  echo '<p> Identification Number : <strong>'. $identification_number.'</strong></p>';
  echo '<p> Validation Type : <strong>'. $validation_type.'</strong></p>';

  echo '<p>Attachment files : </p>';

  foreach($document_attachments as $document_item)
  {
    echo '<a target="_blank" href="'.wp_get_attachment_url($document_item->ID).'">';
    echo  $document_item->post_title;
    echo '</a>';
  //  echo wp_get_attachment_url($document_item->ID);
    echo '<br>';
  }
}



//approve validation

function approve_validation_document( $post_id,$post,$update)
{
   if ( ! current_user_can( 'manage_options' ) ) {
    return;
   }
   update_post_meta($post_id,'approve_status','publish');
   update_post_meta($post_id,'reject_reason','');
   $mailer_custom=AE_Mailing::get_instance();     
   $profile_name=get_userdata($post->post_author);                
   $content='<h3>Admin has approved your <a href="'.site_url('/validation-page/').'">validation document</a></h3>';    
   $mailer_custom->wp_mail( $profile_name->user_email,
                  'Approve Validation Document',$content);

   $content_noti='type=verify_validation&amp;sender='.$post->post_author.'&amp;';   
   $notification = array(
                'post_content' => $content_noti,
                'post_excerpt' => $content_noti,
                'post_status' => 'publish',
                'post_author' => $post->post_author,
                'post_type' => 'notify',
                'post_title' => 'Verify validation '.$profile_name->dipsplay_name,                
            );
    $fre_noti = Fre_Notification::getInstance();
        $noti = $fre_noti->insert($notification);

   
}

add_action('publish_validation','approve_validation_document',10,3);


//reject validation


add_action('edit_post_validation','reject_validation',10,4);

function reject_validation($post_id, $post)
{
  if(!current_user_can('manage_options')) 
  {
    return;
  }
  $approve_status=get_post_meta($post_id,'approve_status',true);
  if($post->post_status == 'pending' && $approve_status=='pending')
  {
    $reject_reason=get_post_meta($post_id,'reject_reason',true);
    update_post_meta($post_id,'approve_status','reject');

      $mailer_custom=AE_Mailing::get_instance();     
   $profile_name=get_userdata($post->post_author);                
   $content='<h3>Admin has rejected your <a href="'.site_url('/validation-page/').'">validation document</a> .Please check and update your document </h3>';    
   $content.='<p>Reason : '.$reject_reason.'</p>';
   $mailer_custom->wp_mail( $profile_name->user_email,
                  'Approve Validation Document',$content);

   $content_noti='type=reject_validation&amp;sender='.$post->post_author.'&amp;';   
   $notification = array(
                'post_content' => $content_noti,
                'post_excerpt' => $content_noti,
                'post_status' => 'publish',
                'post_author' => $post->post_author,
                'post_type' => 'notify',
                'post_title' => $reject_reason,                
            );
    $fre_noti = Fre_Notification::getInstance();
        $noti = $fre_noti->insert($notification);
  }
  
}