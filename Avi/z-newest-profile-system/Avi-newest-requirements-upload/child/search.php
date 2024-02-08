<?php
global $wp_query, $ae_post_factory, $post;
$post_object = $ae_post_factory->get('mjob_post');
get_header();
?>
	<div id="content" class="search.php">
		<?php // get_template_part('template/content', 'page');?>       
        <!-- custom code banner 8th feb 2024 -->
        <?php 
            $main_headline=carbon_get_theme_option('session_page_headline');
            $second_headline=carbon_get_theme_option('session_page_second_headline');
        ?>
        <div class="customTextBannerArea container">       
        <p class="subheadline"><?php echo $second_headline; ?> </p>
        <p class="mainheadline"><?php echo $main_headline; ?></p>
        </div>
        <!-- end custom code banner 8th feb 2024 -->
		<div class="block-page mjob-container-control search-result">
			<div class="container">
				<h2 class="block-title">
					<p class="block-title-text" data-prefix="<?php _e('in', 'enginethemes'); ?>">
						<?php
						$term_id = (isset($_GET['mjob_category']) && !empty($_GET['mjob_category'])) ? $_GET['mjob_category'] : '';
						$term = get_term($term_id);
						// Get search result
						$search_result = $wp_query->found_posts;

                         //custom code 8th feb 2024 --> hide this
                         /*
						if($search_result == 1) {
							printf(__('<span class="search-result-count">%s</span> <span class="search-text-result">MJOB AVAILABLE</span>', 'enginethemes'), $search_result);
						} else {
							printf(__('<span class="search-result-count">%s</span> <span class="search-text-result">MJOBS AVAILABLE</span>', 'enginethemes'), $search_result);
						}*/

						?>
                        <!-- custom code 8th feb 2024 -->
                        <!-- add this line to make css good  -->
                        <span class="search-result-count"></span> <span class="search-text-result" style="visibility:hidden !important;">Mjob</span>
                        <!-- end custom code 8th feb 2024 -->
					</p>
					<div class="visible-lg visible-md">
						<?php get_template_part('template/sort', 'template'); ?>
					</div>
					<div class="show-filter-wrap hidden-lg hidden-md">
						<a class="filter-open-btn" href=""><?php _e('FILTER MJOB', 'enginethemes'); ?> <i class="fa fa-chevron-right" aria-hidden="true"></i></a>
					</div>
				</h2>
				<div class="row search-content">
					<div class="col-lg-3 col-md-3 col-sm-12 col-sx-12">
						<div class="mje-col-left-wrap">
							<div class="mje-col-left">
								<div class="header-filter-wrap hidden-lg hidden-md">
									<a class="filter-close-btn" href=""><i class="fa fa-chevron-left" aria-hidden="true"></i><?php _e('BACK', 'enginethemes'); ?></a>
								</div>
								<div class="hidden-lg hidden-md">
								 	<a class="clear-filter-btn" href= "<?php echo get_site_url() . '/?s'?> "><?php _e('CLEAR ALL FILTER', 'enginethemes'); ?></a>
								</div>
								<div class="hidden-lg hidden-md">
									<?php get_template_part('template/sort', 'template'); ?>
								</div>
								<div class="menu-left">
									<p class="title-menu"><?php _e('Categories', 'enginethemes'); ?></p>
									<?php
										mje_show_filter_categories( 'mjob_category', array('parent' => 0), $term_id);
									?>
								</div>
								<?php get_sidebar('filter');?>
							</div>
						</div>
					</div>
					<div class="col-lg-9 col-md-9 col-sm-12 col-sx-12">
						<div class="block-items no-margin mjob-list-container">
							<?php
							get_template_part('template/list', 'mjobs-search');
							$filter = apply_filters( 'mje_mjob_param_filter_query', $_GET );
                            $wp_query->query = array_merge( $wp_query->query, $filter);

							echo '<div class="paginations-wrapper">';
							ae_pagination($wp_query, get_query_var('paged'));
							echo '</div>';
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
get_footer();
?>