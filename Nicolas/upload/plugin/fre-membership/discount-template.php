<?php
/*
 * Template Name: Discount List Page
 
 */
global $wp_query, $ae_post_factory, $post, $current_user, $user_ID;
get_header();

 
          $args = array();
                         $default = array(
                        'post_type' => 'discountfre',                                             
                        'post_status' => array('publish'),              
                        'posts_per_page'=>-1,        
                        'meta_key' => 'user_id_discount',                                                 
                        'meta_query' =>array(
                            'relation' => 'AND', 
                            array(
                                    'key'=> 'user_id_discount',
                                    'value' => get_current_user_id(),
                                    'compare' => '='
                                    ),                           
                        ),                                           
                    );     
                          $args = wp_parse_args( $args, $default );
                              $discount_list = new WP_Query($args)
?>
<div class="fre-page-wrapper list-profile-wrapper">
    <div class="fre-page-title">
        <div class="container">
            <h2><?php _e( 'Your Discount Code List', ET_DOMAIN ) ?></h2>
        </div>
    </div>


    <div class="fre-page-section">
        <div class="container" style="min-height: 500px;background-color: #fff;">
        	<div class="page-notification-wrap" id="fre_notification_container" style="padding:50px;">
        		<div class="row">
        			<div class="table-responsive">          
						  <table class="table table-bordered">
						    <thead>
						      <tr>
						        <th>#</th>
						        <th>Discount Code</th>
						        <th>Discount %</th>
						        <th>Status</th>						     
						      </tr>
						    </thead>
						    <tbody>
						    <?php 
						    if($discount_list->have_posts())
						    {
						    	$order=1;
						    	  while($discount_list->have_posts())
 								  {
 								  	$discount_list->the_post();
 								  	$discount_code_name=get_post_meta($post->ID,'discount_code',true);
 								  	$discount_percent=get_post_meta($post->ID,'discount_percent',true);
 								  	$discount_code_used=get_post_meta($post->ID,'used',true) ? get_post_meta($post->ID,'used',true) : 'available';  
                                    if($discount_code_used == 'available')                          
                                        $class_text=  'text-success';
                                    else
                                        $class_text ='text-danger';
 								  	echo '<td>'.$order.'</td>';
 								  	echo '<td>'.$discount_code_name.'</td>';
 								  	echo '<td>'.$discount_percent.'</td>';
 								  	echo '<td class="text-uppercase '.$class_text.'"><strong>'.$discount_code_used.'</strong></td>';

 								  	echo '</tr>';
 								  	$order+=1;
 								  }
						    }
						    	?>						      
						    </tbody>
						  </table>
					</div>

           		</div>
          	</div>
        </div>
    </div>

</div>


<?php
get_footer();
?>