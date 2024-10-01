<?php
add_action('edit_user_profile', 'add_custom_verify_field'); 

function get_seller_verification_status($user_id)
{
    $user_profile_id=get_user_meta($user_id,'user_profile_id',true);

    if($user_profile_id)
    {
        $verify_option_status=get_post_meta($user_profile_id,'manual_verify_option_status',true);
        if($verify_option_status)
        {
            return $verify_option_status;
        }
        else
        {
            return false;
        }
    }
    else
    {
        return false;
    }
}

function add_custom_verify_field($user)
{
    $verify_option_status=get_seller_verification_status($user->ID);
    ?>
        <h3>Seller Verification Option</h3>
        <table class="form-table">
            <tr>
                <th><label for="manual_verify_option_status">Status</label></th>
                <td>                                        
                    <select id="manual_verify_option_status" name="manual_verify_option_status" class="regular-text">
                        <option value="none">None</option>
                        <option <?php if($verify_option_status=='verified') echo 'selected'; ?> value="verified">Verified</option>
                    </select>
                    <br />
                    <br />
                    <span class="description">If you set the status to 'verified', every job by this seller will display a 'verified' label.</span>
                </td>
            </tr>
        </table>
    <?php
}

add_action('edit_user_profile_update','save_verification_status_seller');

function save_verification_status_seller($user_id)
{
    if (!current_user_can('manage_options')) {
        return false;
    }

    $user_profile_id=get_user_meta($user_id,'user_profile_id',true);

    if($user_profile_id)
    {
        update_post_meta($user_profile_id,'manual_verify_option_status',$_POST['manual_verify_option_status']);
    }

    //custom code 26th aug 2024
    $user_mjob_post=array(
                    'post_status'=>array('publish','pending'),
                    'post_type'=>'mjob_post',
                    'numberposts' => -1,
                    'author'=>$user_id
    );
    $mjob_collection=get_posts($user_mjob_post);
    if($mjob_collection)
    {
        foreach($mjob_collection as $mjob_item)
        {
            update_post_meta($mjob_item->ID,'manual_verify_option_status',$_POST['manual_verify_option_status']);
        }
    }
    //end custom code

}

// custom code 26th aug 2024

add_filter('mje_mjob_filter_query_args', 'filter_custom_mjob_verified_manually', 999);

function filter_custom_mjob_verified_manually($query_args)
{
    $query = $_REQUEST['query'];
    if(isset($query['verified']) && $query['verified']=='true')
    {        
        $jobs=get_manual_verified_mjobs();

        if($jobs)
        {
            foreach($jobs as $job)
            {
                array_push($query_args['post__in'],$job);
            }
          
        }        
    } 

    return $query_args;
    
}

function get_manual_verified_mjobs()
{
    $jobs=array();
    $verified_mjob_args=array(
        'post_status'=>array('publish','pending'),
        'post_type'=>'mjob_post',
        'numberposts' => -1,    
        'meta_query'     => array(
            array(
                'key'     => 'manual_verify_option_status',
                'value'   => 'verified',
                'compare' => '='
            ),
    ),    
    );
    $verified_jobs=get_posts($verified_mjob_args);
    if($verified_jobs)
    {
        foreach($verified_jobs as $job_item)
        {
            $jobs[]=$job_item->ID;
        }
    }
    return $jobs;
}
//end


add_action('mje_mjob_item_before_rating','add_verified_label_to_verified_seller',999);

function add_verified_label_to_verified_seller($post)
{
    ob_start();
    $verify_option_status=get_seller_verification_status($post->post_author);
    ?>
    <?php if($verify_option_status=='verified'): ?>
    <span class="can_is_verified">
				<i class="fa fa-check-circle" aria-hidden="true"></i>
				<span><?php _e('verified', 'mje_verification') ?></span>
			</span>
    <?php endif; ?>
    <?php
    
    echo ob_get_clean();
}


//for displaying label when fetching mjobs by ajax
add_action('mje_mjob_item_js_before_rating','add_verified_label_to_verified_seller_render_js',999);

function add_verified_label_to_verified_seller_render_js()
{
    ?>
    	<# if(verify_option_status) { #>
            <span class="can_is_verified">
				<i class="fa fa-check-circle" aria-hidden="true"></i>
				<span><?php _e('verified', 'mje_verification') ?></span>
			</span>
        <# } #>
    <?php
}

//add verification option to server

add_filter('ae_convert_mjob_post', 'add_verified_label_filter_mjob',999);

function add_verified_label_filter_mjob($mjob_post)
{
    $verify_option_status=get_seller_verification_status($mjob_post->post_author);
    $mjob_post->verify_option_status= $verify_option_status;
    return $mjob_post;
}