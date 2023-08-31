<?php
add_action('wp_enqueue_scripts', 'add_custom_admin_employee_js');
function add_custom_admin_employee_js()
{    
    if (is_singular('project')) {
    wp_enqueue_script('custom-add-employee-bid-js', get_stylesheet_directory_uri().'/assets/js/admin-employee-bid.js', array(
                'front'
            ), ET_VERSION, true);
    }
}


add_action( 'wp_ajax_admin_add_employee_bid', 'admin_add_employee_bid_init' );

function admin_add_employee_bid_init()
{
  
    if ( !current_user_can( 'manage_options' ) )
    {
        die();
    }
       
    $budget=$_REQUEST['bid_budget_admin']; 
    $bid_time=$_REQUEST['bid_time_admin']; 
    $bid_content=$_REQUEST['bid_content_admin']; 
    $type_time=$_REQUEST['type_time_admin']; 
    $project_title=$_REQUEST['project_title']; 
    $project_guid=$_REQUEST['project_guid']; 
    $post_parent=$_REQUEST['post_parent_admin']; 
    $freelancer=$_REQUEST['freelancer_bid_admin']; 
    $bid_info=array(
                 'post_status'=>'publish',
                 'post_author'=> $freelancer,
                 'post_content'=>$bid_content,
                 'post_title'=> $project_title,
                 'guid'=>$project_guid,
                 'post_parent'=>$post_parent,
                 'post_type'=>'bid',   
                 'post_name'=>sanitize_title($project_title),
    );
    $bid_result=wp_insert_post($bid_info);

    if($bid_result)
    {
        $project = get_post($post_parent);
	    $content = 'type=new_bid&project=' . $post_parent . '&bid=' . $bid_result;
		
        // send notification to employer
		$notification = array(
			'post_type'    => 'notify',
			'post_content' => $content,
			'post_excerpt' => $content,
			'post_author'  => $project->post_author, // send noti to employer
			'post_title'   => sprintf(__("New bid on %s", ET_DOMAIN), get_the_title($project->ID)),
			'post_status'  => 'publish',
			'post_parent'  => $project->ID
		);
        $bid_noti = Fre_Notification::getInstance();
		$notify_id    = $bid_noti->insert($notification);

        //update meta for bid
		update_post_meta($bid_result, 'notify_id', $notify_id);
        update_post_meta($bid_result,'bid_budget',$budget);
        update_post_meta($bid_result,'bid_time',$bid_time);
        update_post_meta($bid_result,'type_time',$type_time);

        //send email to the employer
        $mailer_instance=FRE_Mailing::get_instance();
        $mailer_instance->bid_mail($bid_result);

        //send email to the freelancer
        $freelancer_info=get_userdata($freelancer);
        $email_content='<h3>Admin has added you to this project '.'<a href="'.$project_guid.'">'.$project_title.'</a></h3>';
        $mailer_instance->wp_mail( $freelancer_info->user_email,
									'Added to the project',$email_content);       

        //send notification to the freelancer
        $content_noti='type=admin_add_freelancer_pj&amp;project_id='.$project->ID.'&amp;';
        $notification_freelancer_title='Admin has added you to this project';
        $notification_freelancer=array(
            'post_content' => $content_noti,
            'post_excerpt' => $content_noti,
            'post_status' => 'publish',
            'post_author' =>$freelancer,
            'post_type' => 'notify',
            'post_title' => $notification_freelancer_title, 
            'post_parent' => $project->ID,               
        );
        $notification_freelancer_result=$bid_noti->insert($notification_freelancer);
        if($notification_freelancer_result)
        {
            update_post_meta($notification_freelancer_result,'project_guid',$project_guid);
        }

        $data['success']=true;
        $data['message']='Added employee to project successfully';

    }
    else
    {
        $data['success']=false;
        $data['message']='Failed to add employee to project';
    }
    
    wp_send_json($data);
    die();

}

function get_all_freelancer()
{
    $user_query = new WP_User_Query( array( 'role' => 'freelancer', 'fields' => 'ID' ) );
    $all_freelancer = $user_query->get_results();    
    if (!empty($all_freelancer)) 
    {
        foreach ($all_freelancer as $user_id) {                      
            $freelancer[]=$user_id;
        }
    } 
    return $freelancer;
}

function get_all_profile_of_freelancer($value,$project_id)
{
    global $post;
    $freelancer_ids=get_all_freelancer();    
    $args = array(
        'post_type'      => 'fre_profile',         // Post type is 'post'
        'post_status'    => 'publish',      // Published posts
        'posts_per_page' => -1,             // Number of posts per page
        'author__in' => $freelancer_ids,
    );
    
    $query = new WP_Query($args);
    
    if ($query->have_posts()) 
    {
        while ($query->have_posts()) {
            $query->the_post();
            $freelancer_user_info=get_userdata($post->post_author);   
            $user_profile_had_bid=array(
                                    'post_type'=>'bid',
                                    'author'=>$freelancer_user_info->ID,
                                    'post_status'=>'publish',
                                    'post_parent'=>$project_id,
                                    'numberposts'=>1,
                            );
            $had_bid_profile=get_posts($user_profile_had_bid);
            if(empty($had_bid_profile))
            {
                $list_freelancer[]='<option value="'.$freelancer_user_info->ID.'">'.$freelancer_user_info->display_name.' - '.$freelancer_user_info->user_email.'</option>';
            }                                   
        }
        wp_reset_postdata(); 
    }
    return $list_freelancer;
}
add_filter('fetch_profile_freelancer','get_all_profile_of_freelancer',10,2);