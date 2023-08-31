<?php
/**
 * The Template for displaying all single posts
 *
 * @package WordPress
 * @subpackage FreelanceEngine
 * @since FreelanceEngine 1.0
 */

get_header();
global $wp_query, $ae_post_factory, $post, $user_ID;
$post_object = $ae_post_factory->get( PROJECT );
$convert     = $post_object->convert( $post );
if ( have_posts() ) {
	the_post(); 

	//custom code here
	//neu la project owner thi cho xem hoac freelancer duoc moi hoac la admin thi duoc xem
	if(get_post_meta($convert->ID,'private_extra_option',true)=='private')
	{
		if($convert->post_author==get_current_user_id() || fre_check_invited( get_current_user_id(), $convert->ID ) || current_user_can('manage_options'))
		{
			$is_accessed=true;
		}
		else
		{
			$is_accessed=false;
		}
	}
	else
	{
		$is_accessed=true;
	}
	

	//end

	?>

    <div class="fre-page-wrapper">
        <div class="container">
            <div class="fre-project-detail-wrap">
				<?php
				if($is_accessed)
				{
					if ( isset( $_REQUEST['workspace'] ) && $_REQUEST['workspace'] ) {
					get_template_part( 'template/project-workspace', 'info' );
					get_template_part( 'template/project-workspace', 'content' );
					} else {
						if ( isset( $_REQUEST['dispute'] ) && $_REQUEST['dispute'] ) {
							get_template_part( 'template/project', 'report' );
						}else{
							get_template_part( 'template/single-project', 'info' );
							get_template_part( 'template/single-project', 'content' );
							get_template_part( 'template/single-project', 'bidding' );
	                    }
					}
					echo '<script type="data/json" id="project_data">' . json_encode( $convert ) . '</script>';
				}
				else
				{
					
					echo '<div class="project-detail-box">
    					<div class="project-detail-info">
        			<div class="row">';
        			echo '<h2>This project is private</h2>';
        			echo '</div></div></div>';
				}
				?>
            </div>
        </div>
    </div>
<?php }
get_footer();