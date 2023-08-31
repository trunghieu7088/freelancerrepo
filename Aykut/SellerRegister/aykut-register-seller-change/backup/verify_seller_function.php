<?php 

add_action('init','setupAbstractKey');

function setupAbstractKey()
{     
      update_option('abstract_api_key','dcfb7fa90e3248409340bcbeb8743cbc');
}

function add_page_for_register_seller()
{
$PageGuid = site_url() . "/register-seller-page";
$check_exist=get_page_by_title('Register Seller Page');
      if(empty($check_exist))
      {
        $register_page = array( 'post_title'     => 'Register Seller Page',
                         'post_type'      => 'page',
                         'post_name'      => 'register-seller-page',
                         'post_content'   => '',
                         'post_status'    => 'publish',
                         'comment_status' => 'closed',
                         'ping_status'    => 'closed',
                         'post_author'    => 1,
                         'menu_order'     => 0,
                         'guid'           => $PageGuid );

      $register_page_id=wp_insert_post( $register_page, FALSE ); 
      update_post_meta($register_page_id,'_wp_page_template','page-register-seller.php');
      }

}

add_action( 'init', 'add_page_for_register_seller' );


add_action( 'wp_ajax_update_phonenumber_seller', 'update_phonenumber_seller_init' );

function update_phonenumber_seller_init()
{
    if(get_current_user_id() != $_POST['verifyID'])
    {
       $result['message']='There is something wrong, please refresh and try again';
      $result['success']=false;
    }
    else
    {
          $ch = curl_init();
                  $abstract_api_key=get_option('abstract_api_key');
              // Set the URL that you want to GET by using the CURLOPT_URL option.
              curl_setopt($ch, CURLOPT_URL, 'https://phonevalidation.abstractapi.com/v1/?api_key='.$abstract_api_key.'&phone='.$_POST['phonenumber']);

              // Set CURLOPT_RETURNTRANSFER so that the content is returned as a variable.
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

              // Set CURLOPT_FOLLOWLOCATION to true to follow redirects.
              curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

              // Execute the request.
              $data = curl_exec($ch);

              // Close the cURL handle.
              curl_close($ch);

               $handle_data=json_decode($data,true);
            if($handle_data['valid']==true)
            {

                  $args_check_phone = array(
                              'posts_per_page'   => -1,
                              'post_type' =>'mjob_profile',
                              'post_status' =>'publish',
                              'meta_key' => 'phone_number',
                              'meta_value' => $_POST['phonenumber'],
                               
                              );
                  $unique_phone = new WP_Query($args_check_phone);

                  if($unique_phone->have_posts())                 
                  {
                    $result['message']='Your phone number has been already registered';
                    $result['confirm']='fail';

                  }
                  else
                  {
                        $result['message']='update succesfully';
                        $result['confirm']='success';
                        $result['redirect_url']=site_url('/profile/');
                        $profile_id=get_user_meta($_POST['verifyID'],'user_profile_id',true);
                        update_post_meta($profile_id,'phone_number',$_POST['phonenumber']);
                   }
            }
            else
            {
                   $result['message']='Your phonenumber is invalid';
                   $result['confirm']='fail';
            }
      }

      wp_send_json_success($result);
       die();
}

add_action( 'wp_ajax_verify_seller', 'verify_seller_init' );

function verify_seller_init()
{
     if(get_current_user_id() != $_POST['verifyID'])
    {
       $result['message']='There is something wrong, please refresh and try again';
      $result['confirm']='fail';
    }
    else
    {
     

      //handle mobile first
      //check if mobile is valid
       $ch = curl_init();
                  $abstract_api_key=get_option('abstract_api_key');
              // Set the URL that you want to GET by using the CURLOPT_URL option.
              curl_setopt($ch, CURLOPT_URL, 'https://phonevalidation.abstractapi.com/v1/?api_key='.$abstract_api_key.'&phone='.$_POST['phonenumber']);

              // Set CURLOPT_RETURNTRANSFER so that the content is returned as a variable.
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

              // Set CURLOPT_FOLLOWLOCATION to true to follow redirects.
              curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

              // Execute the request.
              $data = curl_exec($ch);

              // Close the cURL handle.
              curl_close($ch);

               $handle_data=json_decode($data,true);
               if($handle_data['valid']==true)
               {

                  $args_check_phone = array(
                              'posts_per_page'   => -1,
                              'post_type' =>'mjob_profile',
                              'post_status' =>'publish',
                              'meta_key' => 'phone_number',
                              'meta_value' => $_POST['phonenumber'],
                               
                              );
                  $unique_phone = new WP_Query($args_check_phone);

                  if($unique_phone->have_posts())                 
                  {
                    $result['message']='Your phone number has been already registered';
                    $result['confirm']='fail';

                  }
                  else
                  {
                        $result['message']='update succesfully';
                        $result['confirm']='success';

                    $profile_id=get_user_meta($_POST['verifyID'],'user_profile_id',true);

                         update_post_meta($profile_id,'phone_number',$_POST['phonenumber']);

                        update_user_meta(get_current_user_id(),'first_name',$_POST['forename']);
                        update_user_meta(get_current_user_id(),'last_name',$_POST['surname']);

                        wp_set_object_terms( $profile_id,intval($_POST['country']),'country');
                        //wp_set_object_terms( $profile_id,intval($_POST['degree']),'degree');
                        wp_set_object_terms( $profile_id,intval($_POST['language']),'language');

                        $degree=get_term($_POST['degree'],'degree');
                  //     $country=get_term($_POST['country'],'country');
                        update_post_meta($profile_id,'academic_degree',$degree->term_id);

                        update_post_meta($profile_id,'profile_description',$_POST['description']);
                        update_post_meta($profile_id,'billing_country',$_POST['country']);
                        update_post_meta($profile_id,'registered_seller',1);
                        update_post_meta($profile_id,'seller_type',$_POST['sellertype']);
                        update_post_meta($profile_id,'graduation_year',$_POST['graduationYear']);
                        update_post_meta($profile_id,'university',$_POST['university']);
                        update_post_meta($profile_id,'major',$_POST['major']);
                  }
                   

               }
               else
               {
                   $result['message']='Your phone number is invalid';
                   $result['confirm']='fail';

               }
               //$result['valid']=$handle_data['valid'];
                  $result['redirect_url']=site_url('/profile/');
               //$result['phonecheck']=$data;
               
     


    
       wp_send_json_success($result);
       die();
    }
}
