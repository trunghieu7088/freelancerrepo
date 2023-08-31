<?php

function fre_list_projects( $atts ) {
    $atts = shortcode_atts( array(
        'number' => '10',
    ), $atts, 'fre_projects' );

    $args = array(
    	'post_type' => 'project',
    	'post_status' => 'publish',
    	'posts_per_page' => $atts['number'],
    );

    $query = new WP_Query($args);

    ob_start();
    if($query->have_posts()){
    	//echo '<div class="container">';
    	echo '<ul class=" fre-job-online-elementor fre-jobs-list">';
	    	while($query->have_posts()){
	    		$query->the_post();
				global $wp_query, $ae_post_factory, $post;
				$post_object = $ae_post_factory->get('project');

		        $convert = $post_object->convert($post);
		        $postdata[] = $convert; ?>
				<li>
					<div class="jobs-title">
						<p><?php echo $convert->post_title;?></p>
					</div>
					<div class="jobs-date">
						<p><?php echo $convert->post_date;?></p>
					</div>
					<div class="jobs-price">
						<p><?php echo fre_price_format($convert->et_budget);?></p>
					</div>
					<div class="jobs-view">
						<a href="<?php the_permalink();?>"><?php _e('View details', ET_DOMAIN)?></a>
					</div>
				</li> <?php
			} ?>
		</ul><?php
	}
	//echo '</div>';

	wp_reset_query();
	return ob_get_clean();
}


add_shortcode( 'fre_projects', 'fre_list_projects' );

function fre_list_profiles($atts){
	$args = shortcode_atts( array(
        'number' => '4',
    ), $atts, 'fre_profiles' );

	/**
	 * List profiles
	 */
	$query_args = array(
		'post_type' => PROFILE ,
		'post_status' => 'publish' ,
		'posts_per_page' => $atts['number'],
		'meta_key' => 'rating_score',
		'meta_query' =>  array(
	    	array(
	 			'key'   => 'user_available',
	    		'value'   => 'on',
	    		'compare' => '='
	       )
	   ),
		'orderby'  => array(
			'meta_value_num' => 'DESC',
			'post_date'      => 'DESC',
		),
	) ;
	$loop = new WP_Query( $query_args);
	global $wp_query, $ae_post_factory, $post;
	$post_object = $ae_post_factory->get( PROFILE );
	ob_start();

	if($loop->have_posts()) {
		$postdata = array();
		foreach ($loop->posts as $key => $value) {
			$post = $value;
		    $convert = $post_object->convert($post);
		    $postdata[] = $convert;
		    $hou_rate = (int) $convert->hour_rate; // from 1.8.5 ?>
			<div class="col-lg-6 col-md-12">
				<div class="fre-freelancer-wrap">
					<a class="free-avatar" href="<?php echo get_author_posts_url( $convert->post_author ); ?>">
						<?php echo $convert->et_avatar;?>
					</a>
					<h2><a href="<?php echo get_author_posts_url( $convert->post_author ); ?>"><?php echo$convert->author_name; ?></a></h2>
					<p class="secondary-color"><?php echo $convert->et_professional_title;?></p>
					<div class="free-rating rate-it" data-score="<?php echo $convert->rating_score ; ?>"></div>
					<?php if( $hou_rate > 0) { ?>
						<div class="free-hourly-rate">
							<?php printf(__('%s/hr', ET_DOMAIN), "<span>".fre_price_format($convert->hour_rate)."</span>");?>
						</div>
					<?php } ?>
					<div class="free-experience">
						<span><?php echo $convert->experience; ?></span>
						<span><?php echo $convert->project_worked; ?></span>
					</div>
					<div class="free-skill">
					<?php
						if(isset($convert->tax_input['skill']) && $convert->tax_input['skill']){
							$skills = $convert->tax_input['skill'];
                            for ($i = 0; $i <= 2; $i++){
                            	if(isset($skills[$i])){
                                	echo '<span class="fre-label"><a href="'.get_post_type_archive_link( PROFILE ).'?skill_profile='.$skills[$i]->slug.'">'.$skills[$i]->name.'</a></span>';
                            	}
                         	}
                        }
					?>
					</div>
				</div>
			</div> <?php
		}
	}

	wp_reset_query();
	return ob_get_clean();
}
add_shortcode( 'fre_profiles', 'fre_list_profiles' );
function fre_testimonials_block($atts){

	$args = shortcode_atts( array(
        'number' => '4',
    ), $atts, 'testimonials' );
	$query = new WP_Query(array(
        'post_type' => 'testimonial',
        'showposts' => -1,
        'orderby'   => 'date',
        'order'     => 'DESC',
    ));
	ob_start(); ?>
	<div class="owl-carousel owl-carousel-stories">
		<?php

	    if($query->have_posts()){
	        while($query->have_posts()){
	            $query->the_post(); global $post; ?>
				<div class="item">
					<div class="fre-stories-wrap">
						<?php if(has_post_thumbnail($post)){ ?>
							<div class="stories-img">
								<?php the_post_thumbnail( 'large' );?>
							</div>
						<?php } ?>
						<div class="stories-content">
							<div class="fre-quote"> <?php fre_quote_svg_icon();?> </div>
							<p><?php echo $post->post_content;?></p>
							<br/>
							<p><?php echo $post->post_title;?></p>
							<?php
								$position = get_post_meta( $post->ID, '_test_category', true ); ;
								if($position){
									echo '<p>'.$position.'</p>';
								}
							?>
						</div>
					</div>
				</div> <?php
	        }
	    }
	    wp_reset_query();?>

	</div>
	<script type="text/javascript">
		(function($){
			$(document).ready(function() {
				/** testmonial slider in home page */
				$('.owl-carousel-stories').owlCarousel({
		            // loop: true,
		            margin: 50,
		            responsiveClass: true,
		            navText: ["<span></span>","<span></span>"],
		            dots: false,
		            responsive: {
		              	0: {
			                items: 1,
			                nav: true
		              	},
		              	600: {
			                items: 1,
			                nav: false
		              	},
		              	1000: {
			                items: 1,
			                nav: true,
			                loop: false,
			                margin: 50
		              	}
		            }
		        });
			});
		})(jQuery);
	</script>
	<?php
	return ob_get_clean();
}
add_shortcode( 'fre_testimonials', 'fre_testimonials_block' );
?>