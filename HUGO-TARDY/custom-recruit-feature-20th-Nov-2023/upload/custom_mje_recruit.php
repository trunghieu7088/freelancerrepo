<?php
 function is_mje_recruit_active()
 {
     $all_active_plugins=get_option('active_plugins');
     if(in_array('mje-recruit/mje_recruit.php',$all_active_plugins))
     {
         return true;
     }
     else
     {
         return false;
     }
 }
function custom_shortcode_mje_recruit_list_homepage()
{
    if (is_mje_recruit_active()) {
        remove_shortcode('mjob_recruitments');
        add_shortcode('mjob_recruitments', 'custom_mje_recruits_home_page');
    }
}

add_action('after_setup_theme', 'custom_shortcode_mje_recruit_list_homepage', 999);

function custom_mje_recruits_home_page($att)
{
    $args = array(
        'post_type' => MJOB_RECRUIT,
        'post_status' => 'publish',
        'showposts' => 8,
        'orderby' => 'date',
        'order' => 'DESC',
    );
    $skin_name = MJE_Skin_Action::get_skin_name();
    $mjob_title = __('Latest Recruitments', 'mje_recruit');
?>
    <div class="block-items mje-request-block mje-request-<?php echo $skin_name; ?>">
        <div class="container ">
            <?php if ($skin_name == 'diplomat') { ?>
                <h6><?php echo $mjob_title; ?></h6>
            <?php } else { ?>
                <p class="block-title float-center"><?php echo $mjob_title; ?></p>
            <?php } ?>
            <?php

            $request_job = new WP_Query($args);
            $t = 0;
            global $ae_post_factory;
            $post_object = $ae_post_factory->get(MJOB_RECRUIT);
            if ($request_job->have_posts()) : ?>
                <ul class="row mje-request-list">
                    <?php
                    while ($request_job->have_posts()) :
                        $request_job->the_post();
                        global $post, $convert;
                        $convert = $post_object->convert($post);
                        mjob_request_template_home($convert);
                    endwhile; ?>
                </ul>
            <?php else : ?>
                <p class="float-center">
                    <?php _e('There\'s no recruitment available at the moment. Recruit now.', 'mje_recruit'); ?>
                    <a class="" href="<?php echo et_get_page_link('post-recruit'); ?>"><?php _e('Recruit now', 'mje_recruit'); ?></a>
                </p>
            <?php endif; ?>

            <?php if ($request_job->have_posts()) { ?>
                <div class="view-all-jobs-wrap">
                    <a class="btn-order waves-effect waves-light btn-submit mjob-order-action" href="<?php echo get_post_type_archive_link(MJE_REQUEST); ?>">
                        <?php _e('View all Recruitments', 'mje_recruit'); ?>
                    </a>
                    <a class="btn-submit-request float-right btn-request-abs" href="<?php echo et_get_page_link('post-recruit'); ?>"><?php _e('Recruit now', 'mje_recruit'); ?><div class="plus-circle"><i class="fa fa-plus"></i></div></a>
                </div>

                <?php wp_reset_postdata(); ?>
            <?php } ?>
        </div>

    </div>
    <?php
    wp_reset_query();
    return ob_get_clean();
}

function mjob_request_template_home($post)
{
    if (is_mje_recruit_active()) {


        $avatar = get_user_meta($post->post_author, 'et_avatar_url', true);
        if (!$avatar)
            $avatar = ae_get_option('default_avatar', '');
        if (!$avatar)
            $avatar = 'http://0.gravatar.com/avatar/6cf99d904a8800a571681d5eb9618d99?s=35&d=mm&r=G';
        if (wp_is_mobile()) {
            $recruit_short_description = wp_trim_words($post->post_content, 30, '...');
        } else {
            $recruit_short_description = wp_trim_words($post->post_content, 5, '...');
        }


    ?>
        <li class="col-lg-12 col-md-12 col-sm-12 col-mobile-12 mje-recruit-item-row">
            <div class="col-md-6 col-request-title col-sm-12">
                <p class="desktop-display-flex">
                    <img src="<?php echo $avatar; ?>" class="mje-recruit-avatar">
                    <span class="mje-recruit-name"><?php echo $post->post_title; ?></span>
                    <span class="mje-recruit-short-description"><?php echo $recruit_short_description; ?></span>
                </p>

            </div>
            <div class="col-md-2">
                <p><?php echo date(get_option('date_format'),  strtotime($post->post_date)); ?></p>
            </div>
            <!-- <div class="col-md-1">
                5
            </div> -->
            <div class="col-md-2">
                <p><?php echo ae_price_format($post->et_budget); ?></p>
            </div>
            <div class="col-md-2 col-request-link mje-view-detail-mobile">
                <a class="view-detail" href="<?php echo get_the_permalink($post); ?>"><?php _e('View Detail', 'mje_recruit'); ?></a>
            </div>
        </li>
    <?php
    }
}


//function handle recruit items of all recruits page

function mje_request_loop_template($convert)
{ ?>
    <div class="custom-flex-profile">
        <?php $recruit_author = get_userdata($convert->post_author); ?>
        <?php
        $avatar = get_user_meta($convert->post_author, 'et_avatar_url', true);
        if (!$avatar)
            $avatar = ae_get_option('default_avatar', '');
        if (!$avatar)
            $avatar = 'http://0.gravatar.com/avatar/6cf99d904a8800a571681d5eb9618d99?s=35&d=mm&r=G';
        ?>
        <div class="recruitList-avatar-area">
            <img src="<?php echo $avatar; ?>" class="mje-recruit-avatar">
        </div>
        <p><?php echo $recruit_author->display_name; ?></p>
        <div class="vote">
            <div class="rate-it star" data-score="<?php echo mje_get_total_reviews_by_user($convert->post_author); ?>"></div>
            <?php if (mje_get_total_reviews_by_user($convert->post_author) == 0) : ?>
                <p class="text-notyet text-capitalize">not rated yet</p>
            <?php else : ?>
                <p class="text-rate-score text-capitalize text-center">
                    <?php echo number_format(mje_get_total_reviews_by_user($convert->post_author), 1, '.', ' '); ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
    <div class="full custom-flex-recruit">
        <div class="full col-request-title">
            <p>
                <a class="request-loop-title" href="<?php echo get_the_permalink($convert); ?>"><?php echo $convert->post_title; ?></a>                
                <span class="custom-text-expire">
                    <?php printf(__('Posted %s', 'mje_recruit'), date(get_option('date_format'),  strtotime($convert->post_date))); ?>
                    <?php // echo show_human_time_expire_date($convert->expiration_date); ?>
                </span>
            </p>
        </div>

        <div class="full request-loop-expert">
            <?php echo wp_trim_words($convert->post_content, 62); ?>
            <div class="recruit-view-detail-btn">
                <a href="<?php echo get_the_permalink($convert); ?>">
                    View Details
                    <i class="fa fa-angle-double-right"></i>
                </a>
            </div>

            <div class="full custom-info-recruit">
                <span><i class="fa fa-credit-card"></i> Budget: <?php echo ae_price_format($convert->et_budget); ?></span>
                <span><i class="fa fa-briefcase"></i> <?php printf(__('%d offers', 'mje_recruit'), $convert->number_offers); ?></span>
                <span><i class="fa fa-clock-o"></i> Delivery: <?php echo $convert->time_delivery; ?> days</span>
                <span><i class="fa fa-list"></i> Category: <?php echo $convert->category_name; ?> </span>
            </div>

            <div class="full request-loop-tag-wrap"><?php mje_list_tax_of_request($convert->ID, '', 'skill') ?></div>
        </div>
    </div>
<?php
}

//override function archive_recruit_page of recruit plugin.
function archive_recruit_page()
{ ?>
    <div id="content">
        <?php // archive_recruits_banner(); ?>
        <!-- end banner !-->
        <div class="block-page mjob-container-control">
            <div class="container">
                <?php custom_archive_recruits_sort(); ?>
                <div class="row">

                    <!-- custom code filter option -->
                    <div class="container filter-option-container">
                        <div class="row">
                            <input type="hidden" name="custom_filters_option" id="custom_filters_option" value="filter-at-recruit-page">
                            <div class="col-md-4">
                                <div class="form-group custom-filter-option">
                                    <label>Keyword</label>
                                    <input type="text" placeholder="Search by keyword" class="form-control" id="recruit_name_filter" name="recruit_name_filter">
                                </div>
                            </div>

                           

                            <div class="col-md-4">
                                <div class="form-group custom-filter-option">
                                    <label>Categories</label>
                                    <?php
                                    ae_tax_dropdown(
                                        'mjob_category',
                                        array(
                                            'attr' => 'data-chosen-width="100%" data-chosen-disable-search=""  data-placeholder="' . __("Please select", 'enginethemes') . '"',
                                            'class' => 'chosen  tax-item required custom-recruit-filter',
                                            'hide_empty' => false,
                                            'hierarchical' => true,
                                            'id' => 'mjob_category',
                                            'show_option_all' => false,                                            
                                        )
                                    ); ?>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group custom-filter-option">
                                    <label>Delivery Dates</label>
                                    <select class="custom-filters" autocomplete="off" placeholder="Please select" id="delivery_date_number" name="delivery_date_number">
                                        <option value="">Please select</option>
                                        <option value="0,10">0 - 10</option>
                                        <option value="11,20">11 - 20</option>
                                        <option value="21,30">21 - 30</option>
                                        <option value="30">Greater than 30</option>
                                    </select>
                                </div>
                            </div>

                            <div class="clearfix">
                            </div>

                            

                            <div class="col-md-4">
                                <div class="form-group custom-filter-option">
                                    <label>Budget Range (<?php ae_currency_sign(); ?>) </label>
                                    <div class="budget-range">
                                        <input type="number" value="1" min="1" class="form-control budget-range" id="budget_range1" name="budget_range1">
                                        <input type="number" value="2000" min="1" class="form-control budget-range max-range" id="budget_range2" name="budget_range2">
                                    </div>
                                </div>

                            </div>

                            <div class="col-md-4">
                                <div class="form-group custom-filter-option">
                                    <label>Sort by</label>                                    
                                    <select class="custom-filters" autocomplete="off" name="sortdate" id="sortdate">                                        
                                        <option value="DESC">Newest</option>
                                        <option value="ASC">Oldest</option>
                                    </select>
                                </div>
                            </div>

                            <div class="clearfix">
                            </div>

                            <div class="col-md-4">
                                <div class="form-group custom-filter-option">
                                    <a href="javascript:void(0)" id="clear-custom-filters-btn" name="clear-custom-filters-btn">Clear all filters</a>
                                </div>
                            </div>

                        </div>
                    </div>
                    <!-- end custom -->

                    <!--  <div class="col-lg-3 col-md-3 col-sm-12 col-sx-12">
                        <div class="menu-left">
                            <p class="title-menu"><?php _e('Categories', 'mje_recruit'); ?></p>
                            <?php                       // mje_show_filter_categories('mjob_category', array('parent' => 0));                        
                            ?>
                        </div>
                        <div class="filter-tags">
                            <p  class="title-menu"><?php _e('Tags', 'mje_recruit'); ?></p>
                            <?php                     //mje_show_filter_tags(array('skill'), array('hide_empty' => false));                     
                            ?>
                        </div>
                    </div> !-->
                    <div class="col-lg-12 col-md-12 col-sm-12 col-sx-12">
                        <div class="block-items no-margin mjob-list-container">
                            <?php

                            list_archive_recuits();
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
}

// add music genre taxonomy to recruit fetch query_args
function filter_custom_recruit_seynou($query_args)
{
    $query = $_REQUEST['query'];

    $query_args['tax_query'];
    $query_args['meta_query'];

    if (isset($query['music_genre']) && !empty($query['music_genre'])) {

        if (isset($query_args['tax_query']) && !empty($query_args['tax_query'])) {

            $query_args['tax_query']['relation'] = 'AND';
            $query_args['tax_query'][] = array(
                'taxonomy' => 'music_genre',
                'field' => 'term_id',
                'terms' => array($query['music_genre'])
            );
        } else {
            $query_args['tax_query'] = array(
                array(
                    'taxonomy' => 'music_genre',
                    'field' => 'term_id',
                    'terms' => array($query['music_genre'])
                ),
            );
        }
    }

    if (isset($query['delivery_date_number']) && !empty($query['delivery_date_number'])) {
        if($query['delivery_date_number']==30 || $query['delivery_date_number'] =='30')
        {
            $query_args['meta_query'][] = array(
                'key' => 'time_delivery',
                'value' => array(
                    30,
                    PHP_INT_MAX,
                ),
                'type' => 'numeric',
                'compare' => 'BETWEEN'
            );
        }
        else
        {
            $delivery_date_array = explode(",", $query['delivery_date_number']);
            $delivery_date_min = intval($delivery_date_array[0]);
            $delivery_date_max = intval($delivery_date_array[1]);
            $query_args['meta_query'][] = array(
                'key' => 'time_delivery',
                'value' => array(
                    $delivery_date_min,
                    $delivery_date_max,
                ),
                'type' => 'numeric',
                'compare' => 'BETWEEN'
            );
        }
        
    }

    if ( (isset($query['budget_min']) && !empty($query['budget_min'])) 
    && (isset($query['budget_max']) && !empty($query['budget_max'])) ) {
        $min = $query['budget_min'];
        $max =  $query['budget_max'];
        $query_args['meta_query'][] = array(
            'key' => 'et_budget',
            'value' => array(
              	$min,
                $max
            ) ,
            'type' => 'numeric',
            'compare' => 'BETWEEN'
        );
    }

    if (isset($query['s']) && $query['s']) {
        $query_args['s'] = $query['s'];
    }

    if (isset($query['orderby']) && $query['orderby']) {
        $query_args['orderby'] = $query['orderby'];
        $query_args['order']=$query['order'];
    }


    return $query_args;
}
add_filter('mje_mjob_filter_query_args', 'filter_custom_recruit_seynou', 99);

function custom_archive_recruits_sort(){ ?>
	<div class="row functions-items" style="margin-top:40px !important;">
	    <div class="col-lg-6 col-md-6 col-sm-6 col-sx-12 no-padding">
	        <h2><?php _e('All Recruitments', 'mje_recruit'); ?></h2>
	    </div>
	    <div class="col-lg-6 col-md-6 col-sm-16 col-sx-12 no-padding float-right">
	        <?php
	        $type = '';
	         if( isset($_GET['orderby']) && !empty($_GET['orderby']) )
	         {
	            if( isset($_GET['sort']) && !empty($_GET['sort']) )
	            {
	                if($_GET['orderby'] == "date")
	                {
	                    if( $_GET['sort'] == 'DESC')
	                    {
	                        $type = 'DateNewest';
	                    }
	                    else{
	                        $type = 'DateOldest';
	                    }
	                }
	                else{
	                    if( $_GET['sort'] == 'DESC')
	                    {
	                        $type = 'BudgetHight';
	                    }
	                    else{
	                        $type = 'BudgetOlLow';
	                    }
	                }
	            }
	            else{
	                $type = $_GET['orderby'];
	            }
	         }
	        ?>	       
	    </div>
	</div>
<?php
   
}