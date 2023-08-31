<?php
global $wp_query, $ae_post_factory, $post;
$post_object = $ae_post_factory->get( 'project' );

//custom code here
$custom_args=array();
        $default = array(    
          		'post_type' => PROJECT,
          		'post_status' => 'publish',
              'posts_per_page' =>25,
              'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key'     => 'custom_extra_option',
                    'compare' => '=',
                   'type' => 'TEXT',
                    'value' =>'urgent',
                ),                
            ),
              'orderby'   => 'date',
        		'order'     => 'DESC',


           );
          $custom_args = wp_parse_args( $custom_args, $default );
          $wp_query= new WP_Query( $custom_args );
          //end
          
?>
<ul class="fre-project-list project-list-container">
	<?php
	$postdata = array();
	while ( have_posts() ) {
		the_post();
		$convert    = $post_object->convert( $post );
		$postdata[] = $convert;

		if ( $convert->post_status == 'publish' ) {
			get_template_part( 'template/project', 'item' );
		}
	}
	?>
</ul>
<div class="profile-no-result" style="display: none;">
    <div class="profile-content-none">
        <p><?php _e( 'There are no results that match your search!', ET_DOMAIN ); ?></p>
        <ul>
            <li><?php _e( 'Try more general terms', ET_DOMAIN ) ?></li>
            <li><?php _e( 'Try another search method', ET_DOMAIN ) ?></li>
            <li><?php _e( 'Try to search by keyword', ET_DOMAIN ) ?></li>
        </ul>
    </div>
</div>
<?php wp_reset_query(); ?>
<?php
/**
 * render post data for js
 */
echo '<script type="data/json" class="postdata" >' . json_encode( $postdata ) . '</script>';
?>
