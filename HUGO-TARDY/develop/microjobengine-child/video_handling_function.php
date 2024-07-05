<?php
add_action('wp_enqueue_scripts','add_js_video_handling',1);
function add_js_video_handling()
{
    
    if(is_page_template('page-post-service.php'))
    {
        wp_enqueue_script('video-handling-js', get_stylesheet_directory_uri().'/assets/js/custom-upload-video-service.js', array(
            'jquery'
        ), ET_VERSION, true); 
    }
    if(is_page_template('page-my-portfolio.php') || is_page_template('page-author-portfolio.php'))
    {
        wp_enqueue_script('video-player-display-js', get_stylesheet_directory_uri().'/assets/js/custom-display-video-player.js', array(
            'jquery'
        ), ET_VERSION, true); 
    }

}

//add css and js for plyer player to display the video

add_action('wp_enqueue_scripts', 'custom_load_script_video',99,0);
function custom_load_script_video()
{
                 
     wp_enqueue_style( 'custom-plyr-style', get_stylesheet_directory_uri(). '/assets/css/newplyr.css', array(),'1.0');                                            

     wp_enqueue_script('plyrjs', get_stylesheet_directory_uri() . '/assets/js/plyr.js', array(
        'jquery',
        'underscore',
        'backbone',
        'appengine',
        'front',
        'ae-message-js'
        ), ET_VERSION, true);        
                  
}   


add_action('wp_ajax_video_upload_file_service_handling', 'video_upload_file_service_handling_init');

function video_upload_file_service_handling_init()
{
    if (!is_user_logged_in()) {
        die();
    }
    if (!check_ajax_referer('custom_video_upload_nonce', $_POST['_ajax_nonce'])) {
        die();
    }

    if (!function_exists('wp_handle_upload')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }
    
    if (isset($_FILES['file']) && isset($_POST['chunk']) && isset($_POST['chunks'])) 
    {
        $file = $_FILES['file'];
        $chunk = intval($_POST['chunk']);
        $chunks = intval($_POST['chunks']);

        $upload_dir = wp_upload_dir();
        //$targetDir = $upload_dir['path'] . '/';
        $targetDir = str_replace('\\', '/', $upload_dir['path']).'/';
        $targetFile = $targetDir . sanitize_file_name($_REQUEST['custom_file_name']);  

         // Append the chunk number to the target file     
        $targetFileChunked = $targetFile.'_part' . $chunk;
        if (move_uploaded_file($file['tmp_name'], $targetFileChunked)) 
        {
            if ($chunk == $chunks - 1) {
                // All chunks have been uploaded, combine them
                $combinedFile = $targetFile;
                for ($i = 0; $i < $chunks; $i++) {
                    $chunkedFile = $targetFile . '_part' . $i;
                    file_put_contents($combinedFile, file_get_contents($chunkedFile), FILE_APPEND);
                    // Delete the chunked file
                    unlink($chunkedFile);
                }                               
                $attachment = array(
                    'guid'           => $upload_dir['url'].'/'.sanitize_file_name($_REQUEST['custom_file_name']),
                    'post_mime_type' => $_REQUEST['custom_file_type'],
                    'post_title'     =>  sanitize_file_name($_REQUEST['custom_file_name']),
                    'post_content'   => 'attached Video for service',
                    'post_status'    => 'inherit'
                );
                
                //if this is the request from single upload video button of the portfolio page --> set id
                if($_REQUEST['single_video_upload_portfolio']==true || $_REQUEST['single_video_upload_portfolio']=='true')
                {
                    $attach_id = wp_insert_attachment($attachment, $_REQUEST['custom_file_name'],$_REQUEST['portfolio_id']); 
                }
                else
                {
                    $attach_id = wp_insert_attachment($attachment, $_REQUEST['custom_file_name']);       
                }
                
                
                
                if ($attach_id) {
                    update_post_meta($attach_id, '_wp_attached_file', str_replace(site_url() . '/wp-content/uploads/', '', $upload_dir['url'].'/'.sanitize_file_name($_REQUEST['custom_file_name'])));
                    update_post_meta($attach_id,'custom_attachment_type','portfolio_video');        
                    $response = array(
                        'success' => true,
                        'message' => 'File uploaded successfully.',
                        'attach_id' => $attach_id,
                    );
                    wp_send_json($response);
                }

            }
        }
    }

}


add_action( 'wp_ajax_delete_attach_video_on_server', 'delete_attach_video_on_server_init' );
function delete_attach_video_on_server_init() {
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
    else{
        $response = array(
            'success' => false,
            'message' => 'Failed to delete file',           
        );
    }
   
    wp_send_json($response);
    die();
}

//handling video submit mjob

add_action('ae_insert_mjob_post','attach_video_for_submit_mjob',99,2);

function attach_video_for_submit_mjob($result,$args)
{
    if($args['video_attach_id'] && !empty($args['video_attach_id']))
    {
        $attach_video_update=array('ID'=>$args['video_attach_id'],'post_parent'=>$result);
        wp_update_post($attach_video_update);
    }
}

