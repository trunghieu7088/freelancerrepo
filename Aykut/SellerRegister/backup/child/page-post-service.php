<?php
global $user_ID;
get_header();
/**
 * Template Name: Post a service
 *
 * @param void
 * @return void
 * @since 1.0
 * @package MicrojobEngine
 * @category void
 * @author JACK BUI
 */
$disable_plan = disable_plan_post_mjob();

$is_opp_bank_verified = get_user_meta( $user_ID, 'mje_opp_bank_account_verified', true );
?>
<div id="content" class="mjob-post-service">
    <div class="container float-center">
        <p class="block-title"><?php _e('POST A MJOB', 'enginethemes'); ?></p>
        
        <?php /*if ( 'yes' !== $is_opp_bank_verified ) { ?>
		<div id="blur_alert">
    		<div id="blur_alert_content">
            	<h2>Bevor Du deine Dienstleistung posten darfst,
                    musst du dich bei unserem Treuhandservice registrieren. 
                    Dieser schützt dich und den Käufer und gewährleistet eine sichere Transaktion. Weitere Informationen zu unseren Treuhandservice findest du hier.
                        </h2>
                <?php
                if ( 'yes' === get_user_meta( $user_ID, 'mje_opp_account_created', true ) ) {
					$account_creation_response = get_user_meta( $user_ID, 'mje_opp_account_creation_response', true );
					$account_creation_response = json_decode( $account_creation_response, true );
					?><h3><a style="color: #2a394e;" data-link="new" href="<?php echo siars( $account_creation_response, 'compliance/overview_url' );?>">Verifizierung starten</a></h3><?php
				}	
				?>
            </div>
		</div>
        <?php }*/ ?>
	
		<?php if( ! $disable_plan && mje_is_user_active( $user_ID ) ) : ?>
        <div class="progress-bar">
            <div class="mjob-progress-bar-item">
            <?php if(!$user_ID):
                mje_render_progress_bar(4, true);
                else:
                    mje_render_progress_bar(3, true);
                endif; ?>
            </div>
        </div>
        <?php
        endif;

        // check disable payment plan or not
        if(! $disable_plan && mje_is_user_active( $user_ID ) ) {
            get_template_part( 'template/post-service', 'step1' );
        }
        if(! $user_ID) {
            get_template_part( 'template/post-service', 'step2' );
        } elseif( ! mje_is_user_active( $user_ID ) ) {
            echo '<p class="not-found">' . __( 'Your account is pending.<br />Please check your email and confirm your account before posting the mJob.', 'enginethemes' ) . '</p>';
        } else {

            get_template_part( 'template/post-service', 'step3' );

            if( ! $disable_plan) {
                get_template_part( 'template/post-service', 'step4' );
            }
        }
        ?>
    </div>
</div>
            
<?php /*
<style type="text/css">
#blur_alert {
	position: relative;
	width: 100%;
}
#blur_alert_content {
	position: absolute;
	z-index: 111;
	text-align: center;
	width: 100%;
	margin-top: 25%;
	color: #5e8d93;
	font-size: 22px;
	text-shadow: 1px 1px #ccc;
}
#step-post {
	filter: blur(3px);
	user-select: none;
	pointer-events: none;
}
</style>
<?php
*/
            
get_template_part('template/modal', 'secure-code');
get_footer();