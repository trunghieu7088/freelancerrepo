<?php

global $wp_query, $ae_post_factory, $post;
$post_object = $ae_post_factory->get( PROJECT );
$current     = $post_object->current_post;
$tax_input   = $current->tax_input;
?>

<li class="project-item">
    <div class="project-list-wrap">
        <h2 class="project-list-title">
            <a  class="secondary-color" href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a>

             <?php 
            //custom code extra option here
            if(get_post_meta($current->ID,'custom_extra_option',true)=='urgent'){
                    echo '<span style="margin-left:10px;" class="label label-danger">'.get_option('urgentlabel').'</span>';
            }
                   //end   
             ?>
             
        </h2>
        <div class="project-list-info">
            <span><?php printf( __( 'Posted %s', ET_DOMAIN ), get_the_date() ); ?></span>
            <span><?php echo $current->text_total_bid; ?></span>
			<?php
			if ( ! empty( $current->text_country ) ) {
				echo "<span>";
				echo $current->text_country;
				echo "</span>";
			}
			?>
            <span><?php echo $current->budget; ?></span>
        </div>
        <div class="project-list-desc">
            <p><?php echo $current->post_content_trim; ?></p>
        </div>
		<?php
		echo $current->list_skills;
		?>
        <!-- <div class="project-list-bookmark">
            <a class="fre-bookmark" href="">Bookmark</a>
        </div> -->
    </div>
</li>
