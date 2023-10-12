<?php
/**
 * The Template for displaying a user profile
 *
 * @package WordPress
 * @subpackage FreelanceEngine
 * @since FreelanceEngine 1.0
 */
global $wp_query, $ae_post_factory, $post, $user_ID;
$post_object      = $ae_post_factory->get( PROFILE );
$author_id        = get_query_var( 'author' );
$author_name      = get_the_author_meta( 'display_name', $author_id );
$author_available = get_user_meta( $author_id, 'user_available', true );
// get user profile id
$profile_id = get_user_meta( $author_id, 'user_profile_id', true );

/*if($author_id == get_current_user_id()){
    wp_redirect(et_get_page_link( "profile" ));
}*/

$convert = '';
if ( $profile_id ) {
	// get post profile
	$profile = get_post( $profile_id );
	if ( $profile && ! is_wp_error( $profile ) ) {
		$convert = $post_object->convert( $profile );
		$author_name = $convert->author_name;
	}
}

// try to check and add profile up current user dont have profile
if ( ! $convert && ( fre_share_role() || ae_user_role( $author_id ) == FREELANCER ) ) {
	$profile_post = get_posts( array( 'post_type' => PROFILE, 'author' => $author_id ) );
	if ( ! empty( $profile_post ) ) {
		$profile_post = $profile_post[0];
		$convert      = $post_object->convert( $profile_post );
		$profile_id   = $convert->ID;
		update_user_meta( $author_id, 'user_profile_id', $profile_id );
	} else {
		$convert = $post_object->insert( array(
				'post_status'  => 'publish',
				'post_author'  => $author_id,
				'post_title'   => $author_name,
				'post_content' => ''
			)
		);

		$convert    = $post_object->convert( get_post( $convert->ID ) );
		$profile_id = $convert->ID;
	}
}
//  count author review number
$count_review = fre_count_reviews( $author_id );

get_header();
$next_post = false;
if ( $convert ) {
	$next_post = ae_get_adjacent_post( $convert->ID, false, '', true, 'skill' );
}

$rating          = Fre_Review::employer_rating_score( $author_id );
$class_name = 'employer';
if ( fre_share_role() || ae_user_role( $author_id ) == FREELANCER ) {
	$rating          = Fre_Review::freelancer_rating_score( $author_id );
	$class_name = 'freelance';
}
$projects_worked = get_post_meta( $profile_id, 'total_projects_worked', true );
$project_posted = fre_count_user_posts_by_type( $author_id, 'project', '"publish","complete","close","disputing","disputed" ', true );
$hire_freelancer = fre_count_hire_freelancer( $user_ID );

$user      = get_userdata( $author_id );
$ae_users  = AE_Users::get_instance();
$user_data = $ae_users->convert( $user );
$hour_rate = 0;

if( isset($convert->hour_rate) )
	$hour_rate = (int) $convert->hour_rate;


//custom code here
if(ae_user_role( $author_id ) == FREELANCER)
{
   // $total_freelancer_has_rank=get_option('total_freelancer_has_rank');
    $user_rank_order=get_user_meta($author_id,'custom_rank_order',true);
}


//end


?>
    <div class="fre-page-wrapper list-profile-wrapper">
        <div class="fre-page-title">
            <div class="container">
                <h2><?php printf( __( "Profile of %s", ET_DOMAIN ), $author_name ); ?></h2>
            </div>
        </div>

        <div class="fre-page-section">
            <div class="container">
                <div class="author-<?php echo $class_name ?>-wrap">
                    <div class="fre-profile-box">
                        <div class="profile-<?php echo $class_name ?>-info-wrap">
                            <div class="profile-<?php echo $class_name ?>-info">
                                <div class="<?php echo $class_name ?>-info-avatar">
									<span class="<?php echo $class_name ?>-avatar">
										<?php echo get_avatar( $author_id, 70 ); ?>
									</span>
                                    <span class="<?php echo $class_name ?>-name"><?php echo $author_name; 

                                     //custom code for validation here
           				 			$args=array('post_type'=>'validation',
                       				 'post_status'=>'publish',
                        			'author'=>$author_id,
                                        );
                                    $validated_document=get_posts($args);
                                    if(!empty($validated_document) && get_post_meta($validated_document[0]->ID,'approve_status',true) == 'publish' )
                                    {
                                         echo '<img style="width:32px;height:32px;" src="'.get_stylesheet_directory_uri().'/assets/img/verified.png'.'"><em style="font-size:12px;color:#008000;"> Verified</em>'; 
                                    }    
                                    //end

                                    ?>
										<?php if ( fre_share_role() || ae_user_role( $author_id ) == FREELANCER ) {
											if ( $convert ) { ?>
                                                <span><?php echo $convert->et_professional_title; ?></span>
											<?php }
										} ?>
                                    </span>
                                </div>

                                <?php if ( $user_rank_order ) : ?>

                            <div style="padding-left:140px;">
                                <p style="display:inline-block;font-size:18px;font-family:arial;color:#fff;" class="label label-info">Rank : <?php echo $user_rank_order ?></p>
                            </div>
                            <?php endif; ?>
                                
                                <div class="<?php echo $class_name ?>-info-content">
                                    <div class="<?php echo $class_name ?>-rating">
                                        <span class="rate-it"
                                              data-score="<?php echo $rating['rating_score']; ?>"></span>

										<?php if ( fre_share_role() || ae_user_role( $author_id ) == FREELANCER ) { ?>
                                            <span><?php echo ! empty( $convert->experience ) ? $convert->experience : ''; ?></span>
                                            <span><?php printf( __('%s projects worked' ,ET_DOMAIN), intval($projects_worked) ); ?> </span>
										<?php } else {?>
                                            <span class=""><?php printf( __('%s projects posted',ET_DOMAIN), intval($project_posted) ); ?></span>
                                            <span> <?php printf(__('hire %s freelancers',ET_DOMAIN), intval($hire_freelancer) ); ?></span>
										<?php } ?>

										<?php
										if ( ! empty( $convert->tax_input['country'] ) ) {
											echo '<span>' . $convert->tax_input['country']['0']->name . '</span>';
										} ?>
                                    </div>

									<?php if ( ! fre_share_role() && ae_user_role( $author_id ) != FREELANCER ) { ?>
                                        <div class="employer-mem-since">
                                            <span>
                                                <?php _e( 'Member since:', ET_DOMAIN ); ?>
                                                <?php
                                                if ( isset( $user_data->user_registered ) ) {
	                                                echo date_i18n( get_option( 'date_format' ), strtotime( $user_data->user_registered ) );
                                                }
                                                ?>
                                            </span>
                                        </div>
									<?php } ?>

									<?php if ( fre_share_role() || ae_user_role( $author_id ) == FREELANCER ) { ?>
                                        <div class="<?php echo $class_name ?>-hourly">
                                            <?php
                                            if($hour_rate > 0)
                                            	echo '<span>'.sprintf( __( '<b>%s</b> /hr ',ET_DOMAIN), fre_price_format( $hour_rate ) ).'</span>'; ?>

                                            <span><?php echo $convert->earned ?></span>
                                        </div>
                                        <div class="<?php echo $class_name ?>-skill">
											<?php
											if ( isset( $convert->tax_input['skill'] ) && $convert->tax_input['skill'] ) {
												foreach ( $convert->tax_input['skill'] as $tax ) {
													echo '<span class="fre-label">' . $tax->name . '</span>';
												}
											}
											?>
                                        </div>
									<?php } ?>

									<?php if ( ! empty( $convert ) ) { ?>
                                        <div class="<?php echo $class_name ?>-about">
											<?php
											global $post;
											$post = $profile;
											setup_postdata( $profile );
											the_content();
										   	wp_reset_postdata();
									   	 ?>
                                        </div>
									<?php } ?>

	                                <?php if(function_exists('et_the_field') && ( fre_share_role() || ae_user_role( $author_id ) == FREELANCER )) {
		                                et_render_custom_field($convert);
	                                }?>
                                </div>

								<?php
								if ( ($author_available == 'on' || $author_available == '') && $convert ) { ?>

									<div class="freelance-info-edit">
										<?php
										if( ( ae_user_role( $user_ID ) == EMPLOYER  || current_user_can('manage_options') ) && $user_ID != $author_id ) { ?>
											<a href="#" data-toggle="modal"
	                                           class="fre-normal-btn primary-bg-color <?php if ( is_user_logged_in() ) {
												   echo 'invite-open';
											   } else {
												   echo 'login-btn';
											   } ?>" data-user="<?php echo $convert->post_author ?>">
												<?php _e( "Invite Me", ET_DOMAIN ) ?>
	                                        </a>
										<?php }  ?>

										<?php
										$show_btn =  apply_filters('show_btn_contact', false); // @since 1.8.5
										if( $show_btn ){ ?>
											<a href="#" data-toggle="modal"
		                                           class="fre-normal-btn contact-me"   data-user="<?php echo $convert->post_author ?>">
													<?php _e( "Contact Me", ET_DOMAIN ) ?>
		                                    </a>
	                                    <?php } ?>
	                                    <?php do_action('after_btn_invite_fre', $convert->post_author); ?>

									</div>
								<?php } ?>
                            </div>
                        </div>
                    </div>

					<?php if ( fre_share_role() || ae_user_role( $author_id ) == FREELANCER ) {
						//list portfolios
						get_template_part( 'list', 'portfolios' );
						wp_reset_query();

						// list project worked
						get_template_part( 'template/author', 'freelancer-history' );
						wp_reset_query();

						get_template_part( 'list', 'experiences' );
						get_template_part( 'list', 'certifications' );
						get_template_part( 'list', 'educations' );
						wp_reset_query();
						?>

					<?php } else {
						if ( fre_share_role() || ae_user_role( $author_id ) != FREELANCER ) {
							get_template_part( 'template/author', 'employer-history' );
						}
						?>
					<?php } ?>
                </div>
            </div>
        </div>
    </div>
<?php
get_footer();