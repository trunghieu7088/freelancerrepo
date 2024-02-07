<?php
//require files
add_action('wp_enqueue_scripts', 'custom_profiles_featureJS');
function custom_profiles_featureJS()
{            
    global $post;
    if(isset($post) && !empty($post))
    {
        $custom_template_slug   = 'page-all-profiles.php';
        $page_template_slug     = get_page_template_slug( $post->ID );

        if( $page_template_slug == $custom_template_slug || (is_single() && 'mjob_post'==get_post_type()) ){
            wp_enqueue_script('custom-profiles-featurejs', get_stylesheet_directory_uri().'/assets/js/custom-profiles-feature.js', array(
                'front'
            ), ET_VERSION, true);
        }
         
    }

    wp_enqueue_script('custom-search-typeJS', get_stylesheet_directory_uri().'/assets/js/custom-search-bar.js', array(
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
        'posts_per_page' => 18, 
        'paged'  =>$page_number,
        'post_status' =>'publish',            
        'meta_query'     => array(
            //turn on this later
          //  'relation' => 'AND', 
            array(
                'key'   => 'custom_role',
                'value' => 'expert',
            ),        
            array(
                'key'   => 'is_custom_public',
                'value' => 'true',
            ),
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

        if($sortby=='highrating')
        {
            $args['meta_key']='custom_rating_score';
            //$args['meta_type']='NUMERIC';
            $args['order']='DESC';
            $args['orderby']='meta_value_num';          
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
    //$profile_info['found_posts']=$query->found_posts;
    wp_reset_postdata();       
       
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
    $converted_profile['description']=wp_trim_words($description, 25, '...');

    $converted_profile['display_name'] = isset($user_data->display_name) ? $user_data->display_name : '';
    $converted_profile['country_name'] = isset($profile->tax_input['country'][0]) ? $profile->tax_input['country'][0]->name : 'None';
    $converted_profile['languages'] = isset($profile->tax_input['language']) ? $profile->tax_input['language'] : 'None';
    if(empty($converted_profile['languages']))
    {
        $converted_profile['languages']='None';
    }
    
    //get rating score of profile

    /* ko dung code nay vi se dong bo rating score bang add_action init
    $custom_rating_score=custom_mje_get_total_reviews_by_user($custom_profile->post_author);
    if(!$custom_rating_score)
    {
        $converted_profile['custom_rating_score']=0;
    }
    else
    {
        $converted_profile['custom_rating_score']=(float)$custom_rating_score;
    }
    */
    $converted_profile['custom_rating_score']=get_post_meta($custom_profile->ID,'custom_rating_score',true);
    if(!$converted_profile['custom_rating_score'] || empty($converted_profile['custom_rating_score']) || !isset($converted_profile['custom_rating_score']))
    {
        $converted_profile['custom_rating_score']=0;
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
    } else if($to_user == $custom_current_user)
    {
        ?>
            <a href="javascript:void(0)" class="card-custom-contact-btn custom-contact-myself">Contact me <i class="fa fa-comment"></i></a>
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


function custom_mje_get_total_reviews_by_user($user_id)
    {
        $posts = get_posts(array(
            'post_type' => 'mjob_post',
            'post_status' => array(
                'publish',
                'pause',
                'unpause',
            ),
            'meta_query' => array(
                array(
                    'key' => 'rating_score',
                    'value' => 0,
                    'compare' => '>',
                ),
            ),
            'posts_per_page' => -1,
            'author' => $user_id,
        ));

        $count = 0;
        foreach ($posts as $post) {
            $rating_score = get_post_meta($post->ID, 'rating_score', true);
            $count += $rating_score;
        }

        if (count($posts) != 0) {
            return $count / count($posts);
        } else {
            return 0;
        }
    }

add_action('init','custom_sync_rating_score_and_description');

    function custom_sync_rating_score_and_description()
    {
        $current_user_id=get_current_user_id();
        if($current_user_id)
        {            
            $custom_score=custom_mje_get_total_reviews_by_user($current_user_id);
            $user_profile_id=get_post_meta($current_user_id,'user_profile_id',true);
            update_post_meta($user_profile_id,'custom_rating_score', $custom_score); 
            
            $profile_description=get_post_meta($user_profile_id,'profile_description',true);
            if(!$profile_description)
            {
                $profile_description='';
            }
            //update description of profile post
            $update_post=array('ID'=>$user_profile_id,'post_content'=>$profile_description);
            wp_update_post($update_post);
        }
    }   
    
    //custom search bar 
    function mje_show_search_form()
    { 
        global $profile_link,$session_link;
        ?>
        <form action="<?php echo $profile_link; ?>" class="et-form custom-search-bar-wrapper">
            <?php
            if (isset($_COOKIE['mjob_search_keyword'])) {

                $keyword = $_COOKIE['mjob_search_keyword'];
            } else {
                $keyword = '';
            }
            $place_holder  = __('Search', 'enginethemes');
            $keyword = '';
            $searchType='Profile';
            if(isset($_GET['search']) && !empty($_GET['search']))
            {
                $searchType='Profile';
                $keyword=$_GET['search'];
            }
            if( (isset($_GET['s']) && !empty($_GET['s'])) || is_search())
            {
                $searchType='Session';
                $keyword=$_GET['s'];
            }
            ?>
            
        <div class="custom-searchbar-area">
            <span class="new-search-icon"><i class="fa fa-search"></i></span>
            <?php if (is_singular('mjob_post')) : ?>
                <input type="text" name="s" class="newcustom-input-search" id="input-search" placeholder="<?php echo $place_holder; ?>" value="<?php echo $keyword; ?>">
            <?php elseif (is_search()) : ?>
                <input type="text" name="s" class="newcustom-input-search" id="input-search" placeholder="<?php echo $place_holder; ?>" value="<?php echo get_query_var('s'); ?>">
            <?php else : ?>
                <input type="text" name="s" class="newcustom-input-search" id="input-search" placeholder="<?php echo $place_holder; ?>" value="<?php if(isset($searchType) && $searchType=='Profile' ) echo $keyword; ?>">
            <?php endif; ?>
                        
            <span id="searchType" class="custom-dropdown-icon-search" data-custom-status="off">
                <span id="customTypeTextSearh" data-custom-searchType="<?php echo $searchType; ?>">
                    <?php 
                    if(isset($searchType) && $searchType=='Session') 
                    {
                        echo 'Session';
                    }
                    else
                    {
                        echo 'Profile';
                    }
                    
                    ?>
                </span>
                <i class="fa fa-dropdown fa-caret-down"></i>
            </span>

            <div class="custom-selection-board">
                <div class="option-list-container">

                    <div class="custom-item" data-custom-option="Profile">
                        <span class="option-title"><i class="fa fa-user"></i> Profile</span>                       
                        <span class="option-content">Live, Personalized One-on-One Session</span>
                    </div>

                    <div class="custom-item" data-custom-option="Session">
                        <span class="option-title"><img class="session-icon-search" src="<?php echo get_stylesheet_directory_uri().'/assets/img/sessionicon.png'; ?>">  Session</span>                       
                        <span class="option-content">Live, Personalized Session within Predefined Categories</span>
                    </div>   

                </div>
            </div>
        </div>
            
        </form>
    <?php
    }

add_action('wp_head','add_custom_settings_searchbar',999);
function add_custom_settings_searchbar()
{
    global  $session_link, $profile_link;
    $session_link=get_site_url();
    $args=array('post_type'=>'page','numberposts'=>1,'meta_key'=>'_wp_page_template','meta_value'=>'page-all-profiles.php');
    $all_profile_page=get_posts($args);
    $all_profile_page_item=$all_profile_page[0];    
    $profile_link=site_url().'/'.$all_profile_page_item->post_name;
    ?>
    <script type="text/javascript">
        let search_session_link='<?php echo $session_link; ?>';
        let search_profile_link='<?php echo $profile_link; ?>';       
    </script>
    <?php
}

/* start draft code --> delete */
function set_custom_rating()
{
    global $post;
    $args = array(
        'post_type'      => 'mjob_profile',
        'posts_per_page' => -1,        
        'post_status'    => 'publish',
    
        'meta_query'     => array(
            array(
                'key'   => 'custom_role',
                'value' => 'expert',
            ),           
        ),
    );

    $query = new WP_Query($args);
   
    if ($query->have_posts()) 
    {
        while($query->have_posts())
        {
            $query->the_post();
            $custom_score=custom_mje_get_total_reviews_by_user($post->post_author);
            update_post_meta($post->ID,'custom_rating_score', $custom_score);
            update_post_meta($post->ID,'is_custom_public','true');
            $profile_description=get_post_meta($post->ID,'profile_description',true);
            $update_post=array('ID'=>$post->ID,'post_content'=>$profile_description);
            wp_update_post($update_post);
            //echo $post->post_title;    
        }        
    }
    wp_reset_postdata(); 
}


/* end draft code --> delete */