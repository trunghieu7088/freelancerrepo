<?php
//require files
add_action('wp_enqueue_scripts', 'custom_profiles_featureJS');
function custom_profiles_featureJS()
{            

      wp_enqueue_script('custom-profiles-featurejs', get_stylesheet_directory_uri().'/assets/js/custom-profiles-feature.js', array(
                'front'
            ), ET_VERSION, true);
     
    
}

function get_all_reviews_of_seller($seller_id)
{
    global $post;
    $args=array('post_type' => 'mjob_post',
        'posts_per_page' => -1,
        'author' =>$seller_id,
        'order'=> 'DESC',
        'orderby'=> 'date',                         
        );
    $query = new WP_Query($args);
    $all_reviews_count=0;
    $all_mjobs_count=0;
    if ($query->have_posts()) 
    {
        while($query->have_posts())
        {
            $query->the_post();
            $args_comment=array('post_id'=>$post->ID, 'count'=>true);
            $comment_counts=get_comments($args_comment);
            $all_reviews_count+=$comment_counts;
            $all_mjobs_count+=1;
        }        
    }
    wp_reset_postdata();     
    $all_info=array('all_reviews_count'=> $all_reviews_count,'all_mjobs_count'=>$all_mjobs_count);
    return $all_info;
}

//get all profiles for archive page

function get_all_profiles($page_number,$language=array(),$country=array(),$expertise='',$search='',$sortby='')
{
    global $post;
    $args=array('post_type' => 'mjob_profile',
        'posts_per_page' => 3, 
        'paged'  =>$page_number,
        'post_status' =>'publish',    
        'meta_query'     => array(
            //turn on this later
          //  'relation' => 'AND', 
            array(
                'key'   => 'custom_role',
                'value' => 'expert',
            ),
           /* array(
                'key'   => 'is_custom_public',
                'value' => 'true',
            ), */
        ),           
        );

        if(empty($sortby) || $sortby=='newest')
        {
            $args['order']='DESC';
            $args['orderby']='date';    
        }
        if($sortby=='oldest')
        {
            $args['order']='ASC';
            $args['orderby']='date';          
        }

        if(!empty($expertise))
        {
            $args['meta_query'][]=array(
                'key'   => 'expertise',
                'value' => $expertise,
            );
        }

        if(!empty($search))
        {   
            $args['s']=sanitize_text_field($search);
        }

        if(!empty($language) && is_array($language))
        {
            $args['tax_query'][]= array(
                    'taxonomy' => 'language', // Replace with the actual taxonomy slug
                    'field'    => 'term_id', // You can use 'term_id', 'name', or 'slug' here
                    'terms'    => $language, // Replace with the term you want to query
            );
        }
        if(!empty($country) && is_array($country))
        {
            $args['tax_query'][]= array(
                    'taxonomy' => 'country', // Replace with the actual taxonomy slug
                    'field'    => 'term_id', // You can use 'term_id', 'name', or 'slug' here
                    'terms'    => $country, // Replace with the term you want to query
            );
        }
    $query = new WP_Query($args);
    $all_profiles=array();
    $profile_info=array();
    if ($query->have_posts()) 
    {
        while($query->have_posts())
        {
            $query->the_post();
            $converted_profile=convert_profile_card_for_display($post);
            $all_profiles[]=$converted_profile;         
        }        
    }
    $profile_info['profile_list']=$all_profiles;
    $profile_info['max_num_pages']=$query->max_num_pages;
    wp_reset_postdata();       
    
    if($sortby=='highrating')
    {
        usort($profile_info['profile_list'],'compareRatingScoreDESC');
    }
    return $profile_info;
}

function convert_profile_card_for_display($custom_profile)
{
    global $wp_query, $ae_post_factory, $post;

    // Convert profile
    $profile_obj = $ae_post_factory->get('mjob_profile');
    $profile_id = $custom_profile->ID;
    if($profile_id) 
    {
        $profile = get_post($profile_id);
        if($profile && !is_wp_error($profile)) {
            $profile = $profile_obj->convert($profile);
        }
    }

    $user = mJobUser::getInstance();
    $user_data = $user->get($custom_profile->post_author);

    // User profile information
    $description = !empty($profile->profile_description) ? $profile->profile_description : "";
    $converted_profile['description']=wp_trim_words($description, 30, '...');

    $converted_profile['display_name'] = isset($user_data->display_name) ? $user_data->display_name : '';
    $converted_profile['country_name'] = isset($profile->tax_input['country'][0]) ? $profile->tax_input['country'][0]->name : 'None';
    $converted_profile['languages'] = isset($profile->tax_input['language']) ? $profile->tax_input['language'] : 'None';
    if(empty($converted_profile['languages']))
    {
        $converted_profile['languages']='None';
    }
    
    //get rating score of profile
    $custom_rating_score=mje_get_total_reviews_by_user($custom_profile->post_author);
    if(!$custom_rating_score)
    {
        $converted_profile['custom_rating_score']=0;
    }
    else
    {
        $converted_profile['custom_rating_score']=$custom_rating_score;
    }
    //get expertise
    $expertise=get_term(get_post_meta($profile->ID,'expertise',true),'mjob_category');
    if(!$expertise || empty($expertise) || $expertise=='' || is_wp_error($expertise)) 
    {
        $expertiseShow='None';
    }
    else
    {
        $expertiseShow=$expertise->name;
    }
    $converted_profile['expertise']=$expertiseShow;

    //get all reviews count of seller
    $review_info=get_all_reviews_of_seller($custom_profile->post_author);
    $converted_profile['number_of_reviews']=$review_info['all_reviews_count'];
    $converted_profile['author_link']=get_author_posts_url($custom_profile->post_author);
    $converted_profile['avatar']=mje_avatar($custom_profile->post_author,80);
    $converted_profile['profile_id']=$profile->post_author;

    return $converted_profile;
}

function custom_profile_show_contact_btn($to_user)
{
	$custom_current_user=get_current_user_id();

	if (mje_is_has_conversation($custom_current_user, $to_user)) {
	?>
		<a href="<?php echo mje_get_conversation_link($custom_current_user, $to_user); ?>" class="card-custom-contact-btn contact-link">Contact me <i class="fa fa-comment"></i></a>
        
	<?php
	} else if ($to_user != $custom_current_user) {
	?>
		<a href="#" class="card-custom-contact-btn contact-link do-contact" data-touser="<?php echo $to_user; ?>">Contact me <i class="fa fa-comment"></i></a>
		<?php
	}
}

//get all languages or countries for custom filters profile ( drop down list)
function get_term_for_filter_profile($taxonamy)
{
    $term_list=get_terms(array(
        'taxonomy' => $taxonamy,
        'parent'   => 0,
        'hide_empty'=> false,
    ));
    return $term_list;
}

function get_expertise_for_filter_profile()
{
    $term_list=get_terms(array(
        'taxonomy' => 'mjob_category',        
        'hide_empty'=> false,
        'parent'=>0,        
    ));
    return $term_list;
}

function get_child_expertise($parent_id)
{
    $expertise_child=get_terms(array(
        'taxonomy' => 'mjob_category',        
        'hide_empty'=> false,
        'parent'=>$parent_id,        
    ));
    return $expertise_child;
}

function compareRatingScoreDESC($item1,$item2)
{
    return $item2['custom_rating_score'] - $item1['custom_rating_score'];
   
}