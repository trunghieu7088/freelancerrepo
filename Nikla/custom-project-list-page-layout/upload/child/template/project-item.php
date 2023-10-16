<?php

global $wp_query, $ae_post_factory, $post;
$post_object = $ae_post_factory->get( PROJECT );
$current     = $post_object->current_post;
$tax_input   = $current->tax_input;

//custom code
$custom_project_owner=get_userdata($current->post_author);
$project_posted = fre_count_user_posts_by_type($current->post_author, 'project', '"publish","complete","close","disputing","disputed" ', true);
//end
?>

<li class="project-item">
    <div class="project-list-wrap">
        <h2 class="project-list-title">
            <a  class="secondary-color" href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a>
        </h2>
        <div class="project-list-info">

            <span>
                <i class="fa fa-clock-o fontawesome-icon-custom"></i>
                <?php echo $current->human_readable_time; ?>
            </span>
            <span>
                <i class="fa fa-paper-plane fontawesome-icon-custom"></i>
                <?php echo $current->text_total_bid; ?>
            </span>
			<?php
			if ( ! empty( $current->text_country ) ) {
				echo "<span>";
                echo '<i class="fa fa-map-marker fontawesome-icon-custom"></i>';
				echo $current->text_country;
				echo "</span>";
			}
			?>            
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
        
        <div class="custom-project-budget-bid">                        
            <p><i class="fa fa-credit-card"></i> <?php echo $current->budget; ?></p>
            <a href="<?php the_permalink(); ?>">Send Proposal</a>
        </div>
    </div>


    <div class="project-list-custom-info">
         <div class="custom-white-space-top hidden-sm hidden-xs"></div>
        <div class="custom-white-space-bottom hidden-sm hidden-xs"></div>
        
        <div class="custom-info-employer">
            <a href="<?php echo get_author_posts_url($custom_project_owner->ID); ?>"><?php echo get_avatar($current->post_author,80); ?></a>
            <p><a href="<?php echo get_author_posts_url($custom_project_owner->ID); ?>"><?php echo $custom_project_owner->display_name; ?></a></p>
            <span class="project-posted-text"><?php echo $project_posted; ?> Project posted</span>
        </div>

        <!-- old code -->
       <!--  <div class="custom-price">
            <?php echo $current->budget; ?>
        </div>      
        <a href="<?php the_permalink(); ?>" class="fre-btn fre-post-project-next-btn primary-bg-color">Send Proposal</a> -->
         <!--  end old code -->
    </div>
</li>
