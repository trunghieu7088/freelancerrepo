<?php
	$query = new WP_Query(array(
        'post_type' => 'testimonial',
        'showposts' => -1,
        'orderby'   => 'date',
        'order'     => 'DESC',
    ));
?>

<div class="owl-carousel owl-carousel-stories">
	<?php
		global $post;
        if($query->have_posts()){
            while($query->have_posts()){
                $query->the_post();
    ?>
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
				</div>
    <?php
            }
        }
        wp_reset_query();
	?>

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