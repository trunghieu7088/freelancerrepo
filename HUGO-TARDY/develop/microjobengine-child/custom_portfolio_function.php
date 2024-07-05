<?php
//define default banner
define('DEFAULT_BANNER_URL_PORTFOLIO','http://hugo.et/wp-content/uploads/2024/03/default_banner.jpg');

//register custom post type for portfolio

add_action('init','register_portfolio_post_type');

function register_portfolio_post_type() {
    $labels = array(
        'name'               => _x( 'Portfolio', 'post type general name', 'textdomain' ),
        'singular_name'      => _x( 'Portfolio', 'post type singular name', 'textdomain' ),
        'menu_name'          => _x( 'Portfolios', 'admin menu', 'textdomain' ),
        'name_admin_bar'     => _x( 'Portfolio', 'add new on admin bar', 'textdomain' ),
        'add_new'            => _x( 'Add New', 'portfolio', 'textdomain' ),
        'add_new_item'       => __( 'Add New Portfolio', 'textdomain' ),
        'new_item'           => __( 'New Portfolio', 'textdomain' ),
        'edit_item'          => __( 'Edit Portfolio', 'textdomain' ),
        'view_item'          => __( 'View Portfolio', 'textdomain' ),
        'all_items'          => __( 'All Portfolios', 'textdomain' ),
        'search_items'       => __( 'Search Portfolio', 'textdomain' ),
        'not_found'          => __( 'No portfolios found', 'textdomain' ),
        'not_found_in_trash' => __( 'No portfolios found in trash', 'textdomain' ),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'portfolio' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array( 'title', 'editor', 'thumbnail', 'custom-fields' ), // Adjust as needed
    );

    register_post_type( 'portfolio', $args );
}


add_action('mje_after_user_dropdown_menu','custom_menu_portfolio');

function custom_menu_portfolio()
{
    ?>
    <li><a href="<?php echo site_url('my-portfolio'); ?>">My Portfolio</a></li>
    <?php
}

add_action('wp_ajax_createPortfolio','createPortfolio_init');

function createPortfolio_init()
{
    if (!is_user_logged_in()) {        
        die('something went wrong');
    }
    if (!wp_verify_nonce($_POST['create_port_nonce'],'create_port_nonce')) {
        die('something went wrong');
    } 
    extract($_POST);    
    
    $portfolio_array=array(
                            'post_title'=>$port_title,
                            'post_content'=>$port_description,
                            'post_status'=>'publish',
                            'post_type'=>'portfolio'
    );
    $port_inserted=wp_insert_post($portfolio_array);
    if($port_inserted && !is_wp_error($port_inserted))
    {
        update_post_meta($port_inserted,'public_option',$port_public);
        if(!empty($_POST['attachment_ids']))
        {                  
            $list_imgs=explode(',',$_POST['attachment_ids']);
                foreach($list_imgs as $attachment_id )
                {
                    $attach_update = array('ID' => $attachment_id, 'post_parent' => $port_inserted);
                    wp_update_post($attach_update);
                }            
        }  
        
        //handle for video
        if(!empty($_POST['video_attachment_ids']))
        {                  
            $list_videos=explode(',',$_POST['video_attachment_ids']);
                foreach($list_videos as $video_attachment_id )
                {
                    $video_attach_update = array('ID' => $video_attachment_id, 'post_parent' => $port_inserted);
                    wp_update_post($video_attach_update);
                }            
        }
        
        $port_inserted_info=get_post($port_inserted);
        $data['message']='Created portfolio successfully';
        $data['redirect_url']=site_url('/my-portfolio/'.'?port='.$port_inserted_info->post_name);
        $data['success']='true';
    }
    else
    {
        $data['message']='Failed to create portfolio';
        $data['success']='false';
    }
   
    wp_send_json($data);
    die();
}

// function handling upload files

add_action('wp_ajax_port_upload_images_action', 'port_upload_images_action_init');

function port_upload_images_action_init()
{
    if (!is_user_logged_in()) {
        die();
    }
    if (!check_ajax_referer('port_upload_images_none', $_POST['_ajax_nonce'])) {
        die();
    }

    
    if (!function_exists('wp_handle_upload')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }
    $upload_dir = wp_upload_dir();
    $upload_overrides = array( 'test_form' => false );

    if (!empty($_FILES['file'])) {
        $uploaded_file = $_FILES['file'];
    }
    
    
    $upload_info=array(
                'name' => sanitize_file_name($uploaded_file['name']),
                'type' => $uploaded_file['type'],
			    'tmp_name' => sanitize_file_name($uploaded_file['tmp_name']),
			    'error' => $uploaded_file['error'],
			    'size' => $uploaded_file['size']
    ); 
    

    $movefile = wp_handle_upload( $uploaded_file, $upload_overrides );

    $attachment = array(       
        'guid'  => $upload_dir['url'].'/'.sanitize_file_name($uploaded_file['name']), 
        'post_mime_type' => $uploaded_file['type'],
        'post_title'     =>  sanitize_file_name($uploaded_file['name']),
        'post_content'   => 'images of portfolio',
        'post_status'    => 'inherit'
    );

    $attach_id = wp_insert_attachment($attachment, $uploaded_file['name']);    
    
    if($attach_id)
    {
        update_post_meta($attach_id,'_wp_attached_file',substr($upload_dir['subdir'],1).'/'.sanitize_file_name($uploaded_file['name']));        
        update_post_meta($attach_id,'datacustomID',$_REQUEST['datacustomID']);        
        update_post_meta($attach_id,'custom_attachment_type','portfolio_item');        
    }


    if ( $movefile && ! isset( $movefile['error'] ) ) {
        $data['success']='true';
        $data['attach_id']= $attach_id;
    } else {
        $data['success']='false';
    }
    wp_send_json($data);
    die();
}

//delete files on server

add_action( 'wp_ajax_delete_attach_file_on_server', 'delete_attach_file_on_server_init' );
function delete_attach_file_on_server_init() {
    if (!is_user_logged_in()) {
        die();
    }
    $result_delete=wp_delete_attachment($_POST['attach_file_id_delete'],true);
    if($result_delete)
    {
        $response = array(
            'success' => true,
            'message' => 'File has been deleted.',           
        );
    }
    else
    {        
        $args_attachment = array(
            'post_type' => 'attachment', // Change 'post' to your custom post type if needed
            'post_status' =>'inherit',
            'meta_key' =>'datacustomID',
            'meta_value' => $_POST['datacustomID'],
            'posts_per_page' => 1, // Limit to one post
        );

        $query = new WP_Query($args_attachment);

        if ($query->have_posts()) 
        {            
            while ($query->have_posts())
             {
                    $query->the_post();
                    $id_attachment = get_the_ID(); // Get the ID of the attachment             
            }
                wp_reset_postdata(); // Reset post data            
        }

        $result_force_delete=wp_delete_attachment($id_attachment,true);

        if($result_force_delete)
        {
            $response = array(
                'success' => true,
                'message' => 'File has been deleted.',           
            );
        }
        else
        {
            $response = array(
                'success' => false,
                'message' => 'Failed to delete file',           
            );
        }       

        wp_send_json($response);
        die();
    }
}


function get_all_portfolio($user_id)
{
    global $post;
    $data=array();
    $args_port = array(
            'post_type' => 'portfolio', 
            'post_status' =>'publish',
            'posts_per_page' => -1,
            'author'=>$user_id,
        );
    $query = new WP_Query($args_port);

    if ($query->have_posts()) 
    {            
        while ($query->have_posts())
         {
            $query->the_post();
            $data[]=$post;
        }
            wp_reset_postdata(); // Reset post data  
        return $data;          
    }
    
    return false;
}

//get specified portfolio

function get_chosen_portfolio($port_slug)
{
    $args_chosen_port = array(
        'name'           => $port_slug,
        'post_type'      => 'portfolio',
        'post_status'    => 'publish',
        'numberposts' => 1
      );
      $chosen_portfolio = get_posts($args_chosen_port);
      
      if($chosen_portfolio)      
      return $chosen_portfolio[0];
      
      return false;
}

function get_portfolio_images($user_id , $portfolio='all')
{
    global $post;
    $data=array();
    $args_attachment = array(
        'post_type' => 'attachment', 
        'post_status' =>'inherit',
        'author'=>$user_id,
        'meta_key' =>'custom_attachment_type',
        'meta_value' => 'portfolio_item',
        'posts_per_page' => -1, 
    );
    if($portfolio!='all')
    {
        $args_chosen_port = array(
            'name'           => $portfolio,
            'post_type'      => 'portfolio',
            'post_status'    => 'publish',
            'numberposts' => 1
          );
          $chosen_portfolio = get_posts($args_chosen_port);
          if($chosen_portfolio)
          {
            $args_attachment['post_parent']=$chosen_portfolio[0]->ID;
          }
        
    }

    $query = new WP_Query($args_attachment);
    if ($query->have_posts()) 
    {            
        while ($query->have_posts())
         {
            $query->the_post();
            $data[]=$post;
        }
            wp_reset_postdata(); // Reset post data  
        return $data;          
    }
    
    return false;

}

//handle update banner image

add_action('wp_ajax_update_banner_image_portfolio','update_banner_image_portfolio_action');

function update_banner_image_portfolio_action()
{
    if (!is_user_logged_in()) {
        die();
    }
    if (!function_exists('wp_handle_upload')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }

    $upload_dir = wp_upload_dir();
    $upload_overrides = array( 'test_form' => false );

    if (!empty($_FILES['file'])) {
        $uploaded_file = $_FILES['file'];
    }

    $movefile = wp_handle_upload( $uploaded_file, $upload_overrides );

    if ( $movefile && ! isset( $movefile['error'] ) ) 
    {
        $user_profile_id=get_user_meta(get_current_user_id(),'user_profile_id',true);
        if($user_profile_id)
        {
            update_post_meta($user_profile_id,'banner_image_url',$movefile['url']);
        }
        $data['url_banner']=$movefile['url'];
        $data['success']='true';
        $data['message']='Updated banner successfully';
    }
    else
    {
        $data['success']='false';
        $data['message']='Failed to update banner';
    }

    wp_send_json($data);
    die();
    
}

//edit portofilio handling

add_action('wp_ajax_editPortfolio','editPortfolio_action');

function editPortfolio_action()
{
    if (!is_user_logged_in()) {
        die();
    }
    if (!wp_verify_nonce($_POST['edit_port_nonce'],'edit_port_nonce')) {
        die('something went wrong');
    } 
    extract($_POST);    
    
    $portfolio_array=array(
                            'ID'=>$port_id,
                            'post_title'=>$edit_port_title,
                            'post_content'=>$edit_port_description,                            
    );
    $update_result=wp_update_post( $portfolio_array );

    if($update_result && !is_wp_error($update_result))
    {
        update_post_meta($update_result,'edit_port_public',$edit_public_option);
        $data['success']='true';
        $data['message']='Updated portfolio successufully';
    }
    else
    {
        $data['success']='false';
        $data['message']='Failed to update portfolio';
    }

    wp_send_json($data);

    die();


}

//handling single upload image

add_action('wp_ajax_single_upload_images','single_upload_images_action');

function single_upload_images_action()
{
    if (!is_user_logged_in()) {
        die();
    }
    if (!wp_verify_nonce($_REQUEST['_ajax_nonce'],'single_upload_images_nonce')) {
        die('something went wrong');
    } 

    if (!function_exists('wp_handle_upload')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }

    $upload_dir = wp_upload_dir();
    $upload_overrides = array( 'test_form' => false );

    if (!empty($_FILES['file'])) {
        $uploaded_file = $_FILES['file'];
    }

    $movefile = wp_handle_upload( $uploaded_file, $upload_overrides );

    $attachment = array(       
        'guid'  => $upload_dir['url'].'/'.sanitize_file_name($uploaded_file['name']), 
        'post_mime_type' => $uploaded_file['type'],
        'post_title'     =>  sanitize_file_name($uploaded_file['name']),
        'post_content'   => 'images of portfolio',
        'post_status'    => 'inherit'
    );

    $attach_id = wp_insert_attachment($attachment, $uploaded_file['name'],$_REQUEST['portID_singleUpload']);    
    
    if($attach_id)
    {
        update_post_meta($attach_id,'_wp_attached_file',substr($upload_dir['subdir'],1).'/'.sanitize_file_name($uploaded_file['name']));                
        update_post_meta($attach_id,'custom_attachment_type','portfolio_item');        
    }


    if ( $movefile && ! isset( $movefile['error'] ) ) {
        $data['success']='true';
        $data['attach_id']= $attach_id;
    } else {
        $data['success']='false';
    }
    wp_send_json($data);
    die();

}


//handle delete images

add_action('wp_ajax_delete_images_of_portfolio','delete_images_of_portfolio_action');

function delete_images_of_portfolio_action()
{
    
    if (!is_user_logged_in()) {
        die();
    }
    if (!wp_verify_nonce($_POST['data_delete_images_nonce'],'data_delete_images_nonce')) {
        die('something went wrong');
    } 
    extract($_POST);
    if(isset($list_images_ids) && !empty($list_images_ids))
    {
        foreach($list_images_ids as $image_id)
        {
            wp_delete_attachment($image_id,true);            
        }
    }
    $data['success']='true';
    $data['message']='Delete images successfully';
    wp_send_json($data);
    die();
}

//handle delete portfolio

add_action('wp_ajax_deletePortfolio','deletePortfolio_action');

function deletePortfolio_action()
{
    if (!is_user_logged_in()) {
        die();
    }
    if (!wp_verify_nonce($_POST['delete_port_nonce'],'delete_port_nonce')) {
        die('something went wrong');
    } 
    extract($_POST);
    if(isset($delete_portfolio_id) && !empty($delete_portfolio_id))
    {
       $delete_port_info=get_post($delete_portfolio_id);
        if($delete_port_info)
        {
            $all_attach_imgs=get_attached_media('image',$delete_port_info->ID);
            if($all_attach_imgs)
            {
                foreach($all_attach_imgs as $attach_img)
                {
                    wp_delete_attachment($attach_img->ID,true);
                }
            }   
            wp_delete_post($delete_port_info->ID,true);         
        }
    }
    $data['success']='true';
    $data['message']='Delete portfolio successfully';
    $data['redirect_url']=site_url('my-portfolio');
    wp_send_json($data);
    die();
}

//get videos

function get_portfolio_videos($user_id , $portfolio='all')
{
    global $post;
    $data=array();
    $args_attachment = array(
        'post_type' => 'attachment', 
        'post_status' =>'inherit',
        'author'=>$user_id,
        'meta_key' =>'custom_attachment_type',
        'meta_value' => 'portfolio_video',
        'posts_per_page' => -1, 
    );
    if($portfolio!='all')
    {
        $args_chosen_port = array(
            'name'           => $portfolio,
            'post_type'      => 'portfolio',
            'post_status'    => 'publish',
            'numberposts' => 1
          );
          $chosen_portfolio = get_posts($args_chosen_port);
          if($chosen_portfolio)
          {
            $args_attachment['post_parent']=$chosen_portfolio[0]->ID;
          }
        
    }

    $query = new WP_Query($args_attachment);
    if ($query->have_posts()) 
    {            
        while ($query->have_posts())
         {
            $query->the_post();
            $data[]=$post;
        }
            wp_reset_postdata(); // Reset post data  
        return $data;          
    }
    
    return false;

}

