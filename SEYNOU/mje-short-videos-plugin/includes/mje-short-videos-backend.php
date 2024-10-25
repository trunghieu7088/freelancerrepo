<?php
class MJE_Short_Videos_Backend
{
    public static $instance;

    function __construct(){        
		       
		$this->init_hook();     

	}

    function init_hook()
    {              
        add_action('init',array($this, 'register_post_type_short_video' ));
        
        //hook action to add short video
        add_action('ae_insert_mjob_post',array($this,'add_short_video'),10,2);

    }

    function register_post_type_short_video()
    {
        $labels = array(
            'name'               => _x( 'Short Video', 'post type general name', 'mje_short_video' ),
            'singular_name'      => _x( 'Short Video', 'post type singular name', 'mje_short_video' ),
            'menu_name'          => _x( 'Short Videos', 'admin menu', 'mje_short_video' ),
            'name_admin_bar'     => _x( 'Short Video', 'add new on admin bar', 'mje_short_video' ),
            'add_new'            => _x( 'Add New', 'Short Video', 'mje_short_video' ),
            'add_new_item'       => __( 'Add New Short Video', 'mje_short_video' ),
            'new_item'           => __( 'New Short Video', 'mje_short_video' ),
            'edit_item'          => __( 'Edit Short Video', 'mje_short_video' ),
            'view_item'          => __( 'View Short Video', 'mje_short_video' ),
            'all_items'          => __( 'All Short Videos', 'mje_short_video' ),
            'search_items'       => __( 'Search Short Video', 'mje_short_video' ),
            'not_found'          => __( 'No Short Videos found', 'mje_short_video' ),
            'not_found_in_trash' => __( 'No Short Videos found in trash', 'mje_short_video' ),
        );
    
        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'short_video' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array( 'title', 'editor', 'thumbnail', 'custom-fields' ), // Adjust as needed
        );
    
        register_post_type( 'short_video', $args );
    }


    public static function get_instance()    
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    function add_short_video($result,$args)
    {        
        
        if(isset($args['choosenvideoType']) && $args['choosenvideoType']=='upload')
        {               
            if(isset($args['short_video_attach_id']) && !empty($args['short_video_attach_id']))
            {
                
                $short_video_args=array(
                        'post_title'=>sanitize_text_field($args['video_caption']),
                        'post_content'=>wp_strip_all_tags($args['video_caption']),
                        'post_status'=>'publish',
                        'post_type'=>'short_video',
                );
                $inserted_short=wp_insert_post($short_video_args);

                if($inserted_short && !is_wp_error($inserted_short))
                {
                    update_post_meta($inserted_short,'short_video_type',$args['choosenvideoType']); //update video type for video
                    update_post_meta($inserted_short,'service_list',array($result)); //update service ids list for video
                    update_post_meta($inserted_short,'short_video_view_count',0); 

                    //update attach video ( associate to video post) and remove meta temp file
                    $attach_update = array('ID' => $args['short_video_attach_id'], 'post_parent' => $inserted_short); 
                    wp_update_post($attach_update);                    
                    delete_post_meta($args['short_video_attach_id'],'is_temp_short_video','true');
                
                    update_post_meta($result,'short_video_id',$inserted_short); //update short video id to service                    
                }

            }
        }
        if(isset($args['choosenvideoType']) && $args['choosenvideoType']=='youtube')
        {
                if(isset($args['youtube_video_caption']) && isset($args['youtube_video_link']))            
                {
                    $youtube_video_args=array(
                        'post_title'=>sanitize_text_field($args['youtube_video_caption']),
                        'post_content'=>wp_strip_all_tags($args['youtube_video_caption']),
                        'post_status'=>'publish',
                        'post_type'=>'short_video',
                );
                $youtube_short=wp_insert_post($youtube_video_args);
                if($youtube_short && !is_wp_error($youtube_short))
                {
                    update_post_meta($youtube_short,'short_video_type',$args['choosenvideoType']); //update video type for video
                    update_post_meta($youtube_short,'service_list',array($result)); //update service ids list for video
                    update_post_meta($youtube_short,'short_video_count',0); 
                    
                    update_post_meta($youtube_short,'youtube_video_link',$args['youtube_video_link']); 

                    $youtube_video_id=$this->extract_youtube_video_id($args['youtube_video_link']);
                    update_post_meta($youtube_short,'youtube_video_id',$youtube_video_id); 
                    
                    update_post_meta($result,'short_video_id',$youtube_short); //update short video id to service                    
                }
              
            }
        }
    }

    function extract_youtube_video_id($url) {
        // Kiểm tra nếu URL là từ youtu.be
        if (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }
        
        // Kiểm tra nếu URL là dạng youtube.com/watch
        if (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }
        
        // Kiểm tra nếu URL là dạng youtube.com/shorts
        if (preg_match('/youtube\.com\/shorts\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }

        if (preg_match('/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }
        
        // Trả về null nếu không tìm thấy video_id
        return null;
    }
    
 
    
}
new MJE_Short_Videos_Backend();