<?php
	$query_args = array(
		'post_type' => PROJECT ,
        'post_status' => 'publish' ,
        'posts_per_page' => 5,
        'orderby'   => 'date',
        'order'     => 'DESC',
        'is_block'  => 'projects',
         'meta_query' => array(
             'relation' => 'AND',
                array(
                    'key'     => 'custom_extra_option',
                    'compare' => 'NOT EXISTS',
                  // 'type' => 'TEXT',
                    'value' =>'',
                ),         

              'relation' => 'AND',   
               array(
                    'key'     => 'private_extra_option',
                    'compare' => 'NOT EXISTS',
                  // 'type' => 'TEXT',
                    'value' =>'',
                ),         

            ),
    ) ;
    query_posts( $query_args);

    //custom code here

    $custom_args=array();
        $default = array(    
          		'post_type' => PROJECT,
          		'post_status' => 'publish',
              'posts_per_page' =>5,
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
          $urgent_projects=get_posts($custom_args);
          //var_dump( $urgent_projects);
          $query_urgent_pj = new WP_Query( $custom_args );
  /*        while ( $query_urgent_pj->have_posts() ) 
          {
				$query_urgent_pj->the_post();
				echo '<li>' . get_the_title() . '</li>';
		  }
	*/
    //end custom
?>
<ul class="fre-jobs-list">
	<?php
	global $wp_query, $ae_post_factory, $post;
		$post_object = $ae_post_factory->get('project');
		while ( $query_urgent_pj->have_posts()) { 
			$query_urgent_pj->the_post();
	        $convert = $post_object->convert($post);
	        $postdata[] = $convert;
	?>
		<li>
				<div class="jobs-title">
					<p><?php echo $convert->post_title; 
							if(get_post_meta($post->ID,'custom_extra_option',true))
							{
								//echo '<span style="color:#fff;margin-left:10px;padding:5px;border-radius:25px;background-color:#d9534f">'.'Urgent'.'</span>';
								echo '<span style="margin-left:10px;" class="label label-danger">'.get_option('urgentlabel').'</span>';
							}
					 ?></p>
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
			</li>
	<?php } ?>

	<?php
		//global $wp_query, $ae_post_factory, $post;
		$post_object = $ae_post_factory->get('project');
		while (have_posts()) { the_post();
			//custom code here
			
	        $convert = $post_object->convert($post);
	        //end
	        $postdata[] = $convert;
	?>
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
			</li>
	<?php }  ?>
</ul>

<?php wp_reset_query();?>
<div class="fre-jobs-online-more">
<?php if(ae_user_role($user_ID) == freelancer OR ae_user_role($user_ID) == administrator){ ?>
<a class="fre-btn-o primary-color" href="<?php echo get_post_type_archive_link( PROJECT ); ?>"><?php _e('See all jobs', ET_DOMAIN)?></a>
<?php }else{ ?>
<a class="fre-btn-o primary-color" href="https://flexitwork.com/register-2/"><?php _e('See all jobs', ET_DOMAIN)?></a>
<?php } ?>
</div>
