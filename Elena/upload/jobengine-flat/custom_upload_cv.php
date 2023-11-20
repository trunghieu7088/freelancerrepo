<?php
    add_action('wp_enqueue_scripts', 'custom_load_script_style',99,0);
    function custom_load_script_style()
    {          
        wp_enqueue_style( 'custom-upload-cv-style', get_stylesheet_directory_uri(). '/assets/css/custom-upload-cv.css', array(),'1.0');                                                    
        wp_enqueue_style( 'custom-fontawesome', get_stylesheet_directory_uri(). '/assets/fontawesome/css/font-awesome.min.css', array(),'1.0');                                                    
        wp_enqueue_script('custom-upload-cv-js', get_stylesheet_directory_uri() . '/assets/js/custom_upload_cv.js', array(
            'jquery',
        ), '1.0', true);             
        
    }   

    add_action('wp_ajax_custom_cv_upload_file','custom_cv_upload_file_function');

    function custom_cv_upload_file_function()
    {
        if (!is_user_logged_in()) {
            die();
        }

        if (!check_ajax_referer('custom-upload-cv-nonce', $_POST['_ajax_nonce'])) {
            die();
        }

        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        if (!empty($_FILES['file'])) {
            $uploaded_file = $_FILES['file'];
        }

        $upload_overrides = array('test_form' => false);
    
        $file = array(
            'name' => sanitize_file_name($uploaded_file['name']),
            'type' => $uploaded_file['type'],
            'tmp_name' => $uploaded_file['tmp_name'],
            'error' => $uploaded_file['error'],
            'size' => $uploaded_file['size'],
        );
    
        $movefile = wp_handle_upload($file, $upload_overrides);
        
        if($movefile && !is_wp_error($movefile))
        {
            $attachment = array(
                'guid'           => $movefile['url'],
                'post_mime_type' => $uploaded_file['type'],
                'post_title'     =>  sanitize_file_name($uploaded_file['name']),
                'post_content'   => 'custom cv pdf file',
                'post_status'    => 'inherit'
            );
            $attach_id = wp_insert_attachment($attachment, $uploaded_file['name'],$_POST['resumeID']);  
            if ($attach_id) {
               // update_post_meta($attach_id, '_wp_attached_file', str_replace(site_url() . '/wp-content/uploads/', '', $upload_dir['url'].'/'.sanitize_file_name( $uploaded_file['name'])));
               update_post_meta($attach_id, '_wp_attached_file', $movefile['url']);
               update_post_meta($_POST['resumeID'],'custom_cv_attach_id',$attach_id);
            }
            
            $response = array(
                'success' => 'true',
                'message' => 'File uploaded successfully.',           
            );
        }
        else
        {
            $response = array(
                'success' => 'false',
                'message' => 'Failed to upload files.',           
            );
        }
       
        wp_send_json($response);
        die();

    }