<?php
class MJOB_Requests_Front{
	protected static $_instance = null;

	function __construct() {
		$this->init_hooks();
		do_action( 'mjob_requests_loaded' );

	}

	function init_hooks(){
		add_action( 'wp_enqueue_scripts', array($this, 'mje_request_scripts') );
		add_shortcode( 'mjob_requests', array($this,'mjob_recruiments_shortcode') );
		add_shortcode( 'mje_recruits', array($this,'mje_recruits_home_page') ); // 1.2.4
		add_shortcode( 'mjob_recruitments', array($this,'mjob_recruiments_shortcode') );
		add_shortcode( 'my_recruits', array($this,'my_recruitments_shortcode') );
		add_shortcode( 'my_recruitments', array($this,'my_recruitments_shortcode') );

		add_shortcode( 'mjob_requests_form', array($this,'mjob_requests_form') );

		add_action('mje_before_user_dropdown_menu', array($this, 'mje_add_my_request_nav_link') );
		add_action('mje_after_user_sidebar_menu', array($this, 'mje_add_my_request_profile_link') );

		add_filter('mje_disable_plan', array($this, 'mje_disable_plan'),10 ,2 );
	}

	function mje_request_scripts(){


		wp_enqueue_script( 'mje-requests', MJOBREQUEST_PLUGIN_URL . '/assets/mje-recruit.js', array(), MJE_RECRUIT_VERSION, true );
		$translation_array = array(
			'archive_confirm' => __( 'Archive this post?', 'mje_recruit' ),
		);
		wp_localize_script( 'mje-requests', 'mje_request', $translation_array );

		if( is_singular(MJOB_RECRUIT) ){
			wp_enqueue_script( 'singular-orders', MJOBREQUEST_PLUGIN_URL . '/assets/custom-order.js', array(), MJE_RECRUIT_VERSION, true );
		}
		wp_enqueue_style( 'mje-request-css', MJOBREQUEST_PLUGIN_URL.'/assets/mje_recruit.css', array(), MJE_RECRUIT_VERSION );

	}
	function my_recruitments_shortcode(){
		?>
		<div class="information-items-detail box-shadow my-recruits">
	        <div class="table-wrapper">

			<a class="btn-submit-request " href="<?php echo et_get_page_link('post-recruit');?>"><?php _e('Recruit now','mje_recruit');?><div class="plus-circle"><i class="fa fa-plus"></i></div></a>

		<?php
		global $user_ID;

		ob_start();
        $args = array(
        	'post_type' => MJOB_RECRUIT,
        	'post_status' => array(
        		'publish',
        		'archive',
        		'pending',
        	),
        	'showposts' =>-1,
        	'orderby' => 'date',
        	'order' => 'DESC',
        	'author' => $user_ID,
        );
        $request_job = new WP_Query($args);
        $t = 0;
        global $ae_post_factory;
        $post_object = $ae_post_factory->get(MJOB_RECRUIT);
        ?>
            <?php if ($request_job->have_posts()): ?>

						<div class="my-request-heading table-filter clearfix hide">
							<select name="status">
							<option value="">All status</option>
							<option value="publish">Publish</option>
							<option value="archived">Archived</option>
							<option value="pending">Pending</option>
							</select>
						</div>
						<div class="table-content">
							<table>
								<?php my_request_heading_columns()?>
							    <tbody>

			                		<?php while ($request_job->have_posts()): ?>
			                    		<?php $request_job->the_post();
			                			global $post,$convert;
				                    	$convert = $post_object->convert($post);
				                   		my_request_loop($convert); ?>
			    					<?php endwhile;?>
				    			</tbody>
						    </table>
	    				</div>

	    		<?php else: ?>
    			<br />
                	<p class="float-center">
                		<?php printf( __('You don\'t have any recruitment. <a href= "%s">Recruit now</a> now.','mje_recruit') ,get_my_recruit_page_link() );?>

                	</p>
            	<?php endif;?>
            	</div>
           </div>

            <?php if($request_job->have_posts() ){?>

	        <?php }?>
	        <?php wp_reset_postdata();?>
	         <style type="text/css">
            	.btn-submit-request{
            		width: 150px;
            		position: relative;
            		float: right;
            		padding: 15px;
            	}
            	.my-recruits .plus-circle{
            		position: absolute;
            		right: 0;
            		top:15px;
            	}
	         </style>
        <?php
        wp_reset_query();

		return ob_get_clean();
	}
	/**
	 * @since 1.2.4
	 **/
	function mje_recruits_home_page($att){
		$number = isset($att['number']) ? (int) $att['number'] : 8;
		$args = array(
			'post_type' => MJOB_RECRUIT,
			'post_status' => 'publish',
			'showposts' => $number,
			'orderby' => 'date',
			'order' => 'DESC',
		);

		$skin_name = MJE_Skin_Action::get_skin_name();

		ob_start();		?>
			<div class=" mje-request-block mje-request-<?php echo $skin_name;?>">
            <div class="container ">

                <?php

                $request_job = new WP_Query($args);
                global $ae_post_factory;

                if ($request_job->have_posts()): ?>
                       <ul class="row mje-request-list mje-recruit-home">
                        <?php
                         $post_object = $ae_post_factory->get(MJOB_RECRUIT);

                        while ($request_job->have_posts()):
                        	$request_job->the_post();
                        	global $post, $convert;
		                    $convert = $post_object->convert($post);
		                   	mjob_request_template_home($convert);
	                   endwhile; ?>
                	</ul>
				
                <?php else: ?>
                	<p class="float-center">
                		<?php _e('There\'s no recruitment available at the moment. Recruit now.','mje_recruit');?>
                		<a class="" href="<?php echo et_get_page_link('post-recruit');?>"><?php _e('Recruit now','mje_recruit');?></a>
                	</p>
                <?php endif;?>
				<div class="row text-right hidden-lg hidden-md" style="margin-top:20px;">
					<a class="" href="<?php echo et_get_page_link('post-recruit');?>"><?php _e('Post a hire','mje_recruit');?></a>
				</div>
            </div>

        </div>
        <?php
        wp_reset_query();

		return ob_get_clean();
	}
	function mjob_recruiments_shortcode( $atts ){
		$args = array(
			'post_type' => MJOB_RECRUIT,
			'post_status' => 'publish',
			'showposts' => 8,
			'orderby' => 'date',
			'order' => 'DESC',
		);
		$skin_name = MJE_Skin_Action::get_skin_name();
		$mjob_title = __('Latest Recruitments', 'mje_recruit');
		ob_start();		?>
			<div class="block-items mje-request-block mje-request-<?php echo $skin_name;?>">
            <div class="container ">
                <?php if($skin_name == 'diplomat'){?>
                	<h6><?php echo $mjob_title; ?></h6>
                <?php } else { ?>
                	<p class="block-title float-center"><?php echo $mjob_title; ?></p>
                <?php } ?>
                <?php

                $request_job = new WP_Query($args);
                $t = 0;
                global $ae_post_factory;
                $post_object = $ae_post_factory->get(MJOB_RECRUIT);
                if ($request_job->have_posts()): ?>
                       <ul class="row mje-request-list">
                        <?php
                        while ($request_job->have_posts()):
                        	$request_job->the_post();
                    		global $post,$convert;
		                    $convert = $post_object->convert($post);
		                   	mjob_request_template_home($convert);
	                   endwhile; ?>
                	</ul>
                <?php else: ?>
                	<p class="float-center">
                		<?php _e('There\'s no recruitment available at the moment. Recruit now.','mje_recruit');?>
                		<a class="" href="<?php echo et_get_page_link('post-recruit');?>"><?php _e('Recruit now','mje_recruit');?></a>
                	</p>
                <?php endif;?>

                <?php if($request_job->have_posts() ){?>
	            <div class="view-all-jobs-wrap">
	                <a class="btn-order waves-effect waves-light btn-submit mjob-order-action" href="<?php echo get_post_type_archive_link(MJE_REQUEST); ?>">
	                    <?php _e('View all Recruitments', 'mje_recruit');?>
	                </a>
	                <a class="btn-submit-request float-right btn-request-abs" href="<?php echo et_get_page_link('post-recruit');?>"><?php _e('Recruit now','mje_recruit');?><div class="plus-circle"><i class="fa fa-plus"></i></div></a>
	            </div>

	            <?php wp_reset_postdata();?>
	        <?php }?>
            </div>

        </div>
        <?php
        wp_reset_query();

		return ob_get_clean();
	}
	function mje_add_my_request_nav_link(){ ?>
		<li><a href="<?php echo get_my_recruit_page_link(); ?>"><?php _e('My Recruitments', 'mje_recruit');?></a></li>
		<?php
	}
	function mje_add_my_request_profile_link(){ ?>
			 <li class="hvr-wobble-horizontal"><a href="<?php echo get_my_recruit_page_link(); ?>"><?php _e('My Recruitments', 'mje_recruit'); ?></a></li>
		<?php
	}
	function mje_disable_plan($plan, $post_type){
		if($post_type == MJOB_RECRUIT){
			$plan = false;
		}
		return $plan;
	}
	function mje_set_my_request_content($content){
		if( is_page_template('page-my-request.php') ){
			//$content =  $this->mjob_recruiments_shortcode(array());

		}
		return $content;
	}

	function get_instance(){
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}

if( ! function_exists('mjob_request_template_home') ){
	function mjob_request_template_home($post){ ?>
		 <li class="col-lg-12 col-md-12 col-sm-12 col-mobile-12">
		 	<div class="col-md-6 col-request-title ">
				<p><?php echo $post->post_title;?><?php //$author = get_userdata($post->post_author) ; echo $author->display_name; ?></p>
			</div>
			<div class="col-md-2">
				<p><?php echo date(get_option('date_format'),  strtotime($post->post_date) ); ?></p>
			</div>
			<!-- <div class="col-md-1">
				5
			</div> -->
			<div class="col-md-2">
				<p><?php echo ae_price_format($post->et_budget);?></p>
			</div>
			<div class="col-md-2 col-request-link">
				<a class="view-detail" href="<?php echo get_the_permalink($post);?>"><?php _e('View Detail','mje_recruit');?></a>
			</div>
		</li>
		<?php
	}
}
function my_request_heading_columns(){?>
	<thead>
        <tr>
            <th><?php _e('Title','mje_recruit');?> </th>
            <th class="">
                <?php _e('Date','mje_recruit');?>
            </th>
            <th class="td-w-20">
                <?php _e('Budget','mje_recruit');?>
            </th>
            <th><?php _e('Status','mje_recruit');?></th>
            <th class="text-center"><?php _e('Action','mje_recruit');?></th>
        </tr>
    </thead>
<?php }
function my_request_loop($post){ ?>
	<tr class="invoice-item">
	    <td><a href="<?php echo get_permalink($post->ID);?>"><?php echo $post->post_title;?></a></td>
	    <td><?php echo date(get_option('date_format'),  strtotime($post->post_date) ); ?></td>
	    <td><?php echo ae_price_format($post->et_budget);?></td>

	    <td><span class="st-item st-completed"><?php echo $post->post_status;?></span></td>
	    <td class="text-center"><?php if($post->post_status == 'publish'){ ?><a request-id ="<?php echo $post->ID;?>" href="#" class="btn-archive-request"><i class="fa fa-archive"></i></a><?php }?></td>
	</tr>


	<?php
}
function list_archive_recuits(){
global $ae_post_factory, $wp_query;
$post_object = $ae_post_factory->get(MJOB_RECRUIT);

?>
<ul class="request-list list-requests list-mjobs">
	<?php $post_data = array();
	    if( have_posts() ) {
	        while (have_posts()) {
	            the_post();
	            global $post;
	            $convert = $post_object->convert( $post );

	            $post_data[] = $convert;
	            echo '<li class="col-lg-12 col-md-12 col-sm-12 col-xs-12 col-mobile-12 item_js_handle archive-requesst-item '.$post->post_status.'-status ">';
	            mje_request_loop_template($convert);
	            //mje_get_template( 'template/request-item.php', array( 'current' => $convert ) );
	            echo '</li>';
	        }
	    } else {
	        ?>
	        <div class="not-found"><?php _e('There are no Requests found!', 'mje_recruit'); ?></div>
	        <?php
	    }
	?>

</ul>

<?php
	echo '<script type="data/json" class="mJob_postdata" >'.json_encode( $post_data ).'</script>';
	$wp_query->query = array_merge( $wp_query->query ,array(
                            'is_archive_request_post' => is_post_type_archive(MJE_REQUEST),
                        ) ) ;

	echo '<div class="paginations-wrapper">';
	ae_pagination($wp_query, get_query_var('paged'));
	echo '</div>';

}


if( ! function_exists('mje_request_loop_template') ){
	function mje_request_loop_template($convert){ ?>
		 	<div class="full  ">
		 		<div class="full col-request-title">
		 			<p><a class="request-loop-title" href="<?php echo get_the_permalink($convert);?>"><?php echo $convert->post_title;?></a></p>
		 		</div>

		 		<div class="full row-request-info">
		 			<span><?php printf(__('Posted %s','mje_recruit'), date( get_option('date_format'),  strtotime($convert->post_date) ) ); ?></span> |
		 			<span><?php printf(__('%d offers','mje_recruit'), $convert->number_offers) ;?> </span>|
		 			<span><?php echo ae_price_format($convert->et_budget);?></span>
		 		</div>
				<div class="full request-loop-expert">
					<?php echo wp_trim_words( $convert->post_content, 62); ?>
					<div class="full request-loop-tag-wrap"><?php mje_list_tax_of_request( $convert->ID, '', 'skill' ) ?></div>
				</div>
		<?php
	}
}
    function mje_show_list_offer($request){

        $args = array(
            'post_type' => 'mje_offer',
            'post_status' => 'publish',
            'post_parent' => $request->ID,
            'posts_per_page' => -1,
        );
        global $mrequest;
    	$mrequest = $request;
    	$json_offers = array();
        $query = new WP_Query($args);       ?>
			<div class="mjob-single-content list-offers">
				<div class="mjob-single-review mjob-single-block">
				<!-- LIST offers HERE !-->
					<div class="offer-heading">
					    <label class="offer-label"><?php printf(__('%s OFFER(s)','mje_recruit'),$query->found_posts);?></label>
					</div>
					<?php

					if( $query->have_posts() ){
					    while( $query->have_posts() ) {

					        $query->the_post();
					        global $post;
					        mje_request_offer_loop($post, $request);
					        $json_offers[] = $post;
						}
					} else {
					    	_e('There is no offers.','mje_recruit');
					}
					?>
					<input type="hidden" id="mjob_id" name="mjob_id" value="<?php echo $request->ID;?>">
					<input type="hidden" id="mjob_name" name="mjob_name" value="<?php echo $request->post_title;?>">
				<!-- END REVIEWS HERE !-->
				</div><!-- end .mjob-single-review -->
			</div>
        <?php
        echo '<script type="data/json"  id="json_offers">'. json_encode($json_offers) .'</script>';
    }
function mje_request_send_custom_order_button($offer,$request){
	global $user_ID;
	$conversation_parent = 0;
    $conversation_guid = '';
    if($conversation = mje_get_conversation( $user_ID, $offer->post_author )) {
        $conversation_parent = $conversation[0]->ID;
        $conversation_guid = $conversation[0]->guid;
    }
    $mjob_id = get_post_meta($offer->ID,'mjob_id', true);


     ?>
    <li>
	    <a data-mjob-name="<?php echo $request->post_title;?>" class="btn bt-send-custom" data-mjob="<?php echo $offer->mjob_id;?>" data-conversation-guid="<?php echo $conversation_guid;?>" data-conversation-parent="<?php echo $conversation_parent;?>" data-from-user="<?php echo $request->post_author;?>" mjob_id = "<?php echo $offer->mjob_id;?>" mjob_name="<?php echo $offer->mjob_name;?>" data-to-user="<?php echo $offer->post_author;?>" style="cursor: pointer"><?php _e('Send custom order','mje_recruit');?><i class="fa fa-paper-plane"></i></a>
	</li>
	<?php
}
function mje_request_contact_btn($to_user){ ?>
	<?php mje_show_contact_link($to_user); ?>

	<?php

}
function mje_request_offer_loop($post, $request){

	global $user_ID, $mrequest;
	$mje_offer = MJE_Offer::get_instance()->xconvert($post); ?>

	<div class="offer-item">
		<div class="col-md-2 col-offer-avatar">
		    <?php  echo mje_avatar($mje_offer->post_author, 75); ?>
		</div>
		<div class="col-md10">
		    <h3 class="offer-author"><a href="<?php echo get_author_posts_url($mje_offer->post_author);?>"> <?php echo $mje_offer->display_name;?></a></h3>
		    <p class="offer-title"><?php echo $mje_offer->custom_title;?></p>
		    <?php
		    $mjob_id = get_post_meta($mje_offer->ID,'mjob_id', true);

		    if ( $user_ID ==  $request->post_author ) {   	?>
		    	<ul class="author-request-act">
		    		<?php mje_request_contact_btn( $mje_offer->post_author );?>
		    		<?php mje_request_send_custom_order_button($mje_offer, $request);?>
		    	</ul>
		    <?php }  ?>
		</div>

	</div>
	<?php
}

if(!function_exists('ae_pagination_requests')):
/**
 * render posts list pagination link
 * @param $wp_query The WP_Query object for post list
 * @param $current if use default query, you can skip it
 * @author Dakachi
*/
function ae_pagination_requests( $query, $current = '', $type = 'page', $text = '')
{
    /**
     * posttype args
     */
    $query_var = array();
    $query_var['post_type'] = $query->query_vars['post_type'] != '' ? $query->query_vars['post_type'] : 'post';
    $query_var['post_status'] = isset($query->query_vars['post_status']) ? $query->query_vars['post_status'] : 'publish';
    $query_var['orderby'] = isset($query->query_vars['orderby']) ? $query->query_vars['orderby'] : 'date';
    // taxonomy args
    $query_var['place_category'] = isset($query->query_vars['place_category']) ? $query->query_vars['place_category'] : '';
    $query_var['location'] = isset($query->query_vars['location']) ? $query->query_vars['location'] : '';
    $query_var['showposts'] = isset($query->query_vars['showposts']) ? $query->query_vars['showposts'] : '';
    /**
     * order
     */

    $query_var['order'] = $query->query_vars['order'];

    if (!empty($query->query_vars['meta_key']))
        $query_var['meta_key'] = isset($query->query_vars['meta_key']) ? $query->query_vars['meta_key'] : 'rating_score';

    $query_var = array_merge($query_var, (array)$query->query);
    $query_var['paginate'] = $type;

    echo '<script type="application/json" class="ae_query">' . json_encode($query_var) . '</script>';

    if (($query->max_num_pages <= 1 && !et_load_mobile()) || !$type) return;
    $style = '';
    if (et_load_mobile() && $query->max_num_pages <= 1) {
        $style = "style='display:none'";
    }

    echo '<div class="request-paginations" ' . $style . '>';
    if ($type === 'page') {
        $big = 999999999; // need an unlikely integer
        echo paginate_links(array(
            'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
            'format' => '?paged=%#%',
            'current' => max(1, ($current) ? $current : get_query_var('paged')),
            'total' => $query->max_num_pages,
            'next_text' => '<i class="fa fa-angle-double-right"></i>',
            'prev_text' => '<i class="fa fa-angle-double-left"></i>',
        ));
    } else {
        if ($query->max_num_pages == $current) {
            return false;
        }

        if ($text == '') {
            $text = __("Load more", 'mje_recruit');
        }
        echo '<a id="' . $query_var['post_type'] . '-inview" class="inview load-more-post" >' . $text . '</a>';
    }

    echo '</div>';
}

endif;
function mje_btn_submit_request($blank = 0){
	$target = '';
	if( $blank){
		$target = ' target = "blank" ';
	}
	?>
 <a class="btn-submit-request float-right btn-request-abs" <?php echo $target;?> href="<?php echo et_get_page_link('post-recruit');?>"><?php _e('Recruit now','mje_recruit');?><div class="plus-circle"><i class="fa fa-plus"></i></div></a>
 <?php }


function archive_recruits_banner(){ ?>
	 <div class="banner">
        <?php
        $absolute_url = mje_get_full_url( $_SERVER );
        $title = get_theme_mod('post_job_title') ? get_theme_mod('post_job_title') : __('Get your stuffs done from $5', 'mje_recruit');

        if( is_mje_submit_page() ){
            $post_link = '#';
        }
        else {
            $post_link = et_get_page_link('post-recruit') . '?return_url=' . $absolute_url;
        }
        ?>
        <div class="container">
            <div class="search-slider float-center job-items-title">
                <h2 class="banner-title"><?php echo $title; ?></h2>
               <!-- <a href="<?php echo $post_link; ?>" class="btn-post hvr-sweep-to-left waves-effect waves-light"><p class="name-button-post"><?php _e('Recruit now', 'mje_recruit'); ?></p> <span class="cirlce-plus"><i class="fa fa-plus"></i></span></a> -->
            </div>
        </div>
        <div class="header-images">
            <?php
            $img_url = ae_get_option('post_job_banner');
            $img_theme_mod = get_theme_mod('post_job_banner');
            if(!empty($img_url)) {
                $img_url = $img_url['full']['0'];
                ?>
                <img src="<?php echo $img_url; ?>" alt="<?php _e('Recruit banner', 'mje_recruit'); ?>">
                <?php
            } elseif(false === $img_theme_mod) {
                $img_url = get_template_directory_uri() . '/assets/img/banner.png';
                ?>
                <img src="<?php echo $img_url; ?>" alt="<?php _e('Recruit banner', 'mje_recruit'); ?>">
                <?php
            } else {
                $img_url = "";
            }

            ?>
        </div>
    </div>
    <?php
}
function archive_recruits_sort(){ ?>
	<div class="row functions-items">
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

	        <div class="filter-by">
	            <span><?php _e('Sort by', 'mje_recruit'); ?><span>:</span></span>
	            <select class="status-filter" name="orderby">
	                <option value="date" data-order="DESC" <?php echo $type == 'DateNewest' ? 'selected' : ''; ?> ><?php _e('Newest', 'mje_recruit'); ?></option>
	                <option value="date" data-order="ASC"<?php echo $type == 'DateOldest' ? 'selected' : ''; ?> ><?php _e('Oldest', 'mje_recruit'); ?></option>
	            </select>
	        </div>


	    </div>
	</div>
<?php

}
if( ! function_exists('archive_recruit_page') ):
	function archive_recruit_page(){ ?>
		<div id="content">
	   	<?php archive_recruits_banner();?>
	    <!-- end banner !-->
	    <div class="block-page mjob-container-control">
	        <div class="container">
	            <?php archive_recruits_sort();?>
	            <div class="row">
	               <!--  <div class="col-lg-3 col-md-3 col-sm-12 col-sx-12">
	                    <div class="menu-left">
	                        <p class="title-menu"><?php _e('Categories', 'mje_recruit'); ?></p>
	                        <?php                       // mje_show_filter_categories('mjob_category', array('parent' => 0));                        ?>
	                    </div>
	                    <div class="filter-tags">
	                        <p  class="title-menu"><?php _e('Tags', 'mje_recruit'); ?></p>
	                        <?php                     //mje_show_filter_tags(array('skill'), array('hide_empty' => false));                     ?>
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
endif;
function post_a_recruit_form(){
	global $user_ID;

if (isset($_REQUEST['id'])) {
	$post = get_post($_REQUEST['id']);
	if ($post) {
		global $ae_post_factory;
		$post_object = $ae_post_factory->get($post->post_type);
		echo '<script type="data/json"  id="edit_postdata">' . json_encode($post_object->convert($post)) . '</script>';
	}

}


if (isset($_GET['return_url'])) {
	$return = $_GET['return_url'];
} else {
	$return = home_url();
}
$currency_code = ae_currency_code(false);
?>
<div class="step-wrapper step-post" id="step-post">
    <form class="post-job  post et-form" id="">
        <div class="form-group clearfix">
            <div class="input-group">
                <label for="post_title" class="input-label"><?php _e('Recruitment name', 'mje_recruit');?></label>
                <input type="text" class="input-item input-full" name="post_title" value="" required>
            </div>
        </div>
        <div class="form-group row clearfix <?php echo ('1' == ae_get_option('custom_price_mode') || is_super_admin($user_ID)) ? 'has-price-field' : ''; ?>">
            <?php if ('1' == ae_get_option('custom_price_mode') || is_super_admin($user_ID)): ?>
                <?php


?>
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 clearfix">
                    <div class="input-group">

                        <label for="et_budget"><?php printf(__('Your budget (%s)', 'mje_recruit'), $currency_code);?></label>
                        <input type="number" name="et_budget"  class="input-item et_budget" >

                    </div>
                </div>
            <?php endif?>

            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 delivery-area">
                <div class="input-group delivery-time">
                    <label for="time_delivery"><?php _e('Time of delivery (Day)', 'mje_recruit');?></label>
                    <input type="number" name="time_delivery" value="" class="input-item time-delivery" min="0">
                </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 category-area">
                <div class="input-group">
                    <label for="mjob_category"><?php _e('Category', 'mje_recruit');?></label>
                    <?php
                        ae_tax_dropdown('mjob_category',
                    	array('attr' => 'data-chosen-width="100%" data-chosen-disable-search=""  data-placeholder="' . __("Choose categories", 'mje_recruit') . '"',
                    		'class' => 'chosen chosen-single tax-item required',
                    		'hide_empty' => false,
                    		'hierarchical' => true,
                    		'id' => 'mjob_category',
                    		'show_option_all' => false,
                    	)
                    );?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="input-group">
                <label class="mb-20"><?php _e('Description', 'mje_recruit')?></label>
                <?php wp_editor('', 'post_content', ae_editor_settings());?>
            </div>
        </div>

       <div class="form-group skill-control">
            <label><?php _e('Tags', 'mje_recruit');?></label>
            <input type="text" class="form-control text-field skill" id="skill" placeholder="<?php _e("Enter microjob tags", 'mje_recruit');?>" name=""  autocomplete="off" spellcheck="false" >
            <ul class="skills-list" id="skills_list"></ul>
        </div>

        <div class="form-group">
            <button class="<?php mje_button_classes(array('btn-save', 'waves-effect', 'waves-light'))?>" type="submit"><?php _e('SAVE', 'mje_recruit');?></button>
            <a href="<?php echo $return; ?>" class="btn-discard"><?php _e('DISCARD', 'mje_recruit');?></a>
            <input type="hidden" class="input-item post-service_nonce" name="_wpnonce" value="<?php echo de_create_nonce('ae-mjob_post-sync'); ?>" />
            <input type="hidden" class="input-item is_submit_request" name="is_submit_request" value="1">
            <input type="hidden" class="input-item post_type" name="post_type" value="<?php echo MJOB_RECRUIT;?>">
        </div>
    </form>
</div>
<?php }
new MJOB_Requests_Front();