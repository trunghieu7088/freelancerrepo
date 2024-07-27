<?php
class MJE_Claim_Admin extends AE_Base
{
    public static $instance;

    public static function get_instance() {
        if( self::get_instance() == null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
		$this->add_filter('manage_mje_claims_posts_columns', 'add_colum_mje_claims',11);
		$this->add_action('manage_mje_claims_posts_custom_column', 'add_content_colum_mje_claims', 10, 2);
        $this->add_action('ae_admin_menu_pages', 'add_admin_menu_pages' );
		$this->add_action('add_meta_boxes', 'boot_add_option_mjob');
		$this->add_action('save_post', 'boot_save_option_mjob');
		$this->add_filter('views_edit-mje_claims', 'meta_views_mje_claims', 10, 1 );
		$this->add_action('init', 'mje_claim_registy_new_status' );
		$this->add_action('admin_footer', 'add_seletor_hide_footer');
		$this->add_ajax('mje_change_status_claim_backend','mje_change_status_claim_backend_function');
		$this->add_ajax('mje_restore_status_claim_backend','mje_restore_status_claim_backend_function');
		$this->add_ajax('mje_remove_notice_claim_backend','mje_remove_notice_claim_backend_function');
		$this->add_ajax('mje_undo_notice_claim_backend','mje_undo_notice_claim_backend_function');


		$this->add_filter('fre_default_setting_option', 'filter_default_options');
		$this->add_action( 'admin_menu', 'add_notice_claim_menu' );
		$mje_claim=new MJE_Claim;
		//$mje_claim->registry_post_type('Claims','mje_claims',array('title','author'));
		if(!ae_get_option('claim_page') or get_post_status(ae_get_option('claim_page'))<>"publish"){
			
			$query = new WP_Query(
				array(
					'post_type'              => 'page',
					'title'                  => 'Job Verification',
					'posts_per_page'         => 1,
					'no_found_rows'          => true,
					'ignore_sticky_posts'    => true,
					'update_post_term_cache' => false,
					'update_post_meta_cache' => false,
					'post_status'			=> 'publish'
				)
			);
			if ( ! empty( $query->post ) ) {
				$page_job_verification = $query->post;
				$pageid=$page_job_verification->ID;
			}
			else {
				$my_post = array(
				  'post_title'    => 'Job Verification',
				  'post_content'  => '[mje_list_claims]',
				  'post_status'   => 'publish',
				  'post_type' => 'page'
				);
				$pageid=wp_insert_post( $my_post );
				update_post_meta($pageid,'_wp_page_template','page-user-default.php');
			}
			if($pageid){
				ae_set_option('claim_page',$pageid);
			}
		}

		if(!ae_get_option('claim_term') or get_post_status(ae_get_option('claim_term'))<>"publish"){
			
			$query = new WP_Query(
				array(
					'post_type'              => 'page',
					'title'                  => 'Terms of Service and Privacy Policy',
					'posts_per_page'         => 1,
					'no_found_rows'          => true,
					'ignore_sticky_posts'    => true,
					'update_post_term_cache' => false,
					'update_post_meta_cache' => false,
					'post_status'			=> 'publish'
				)
			);
			if ( ! empty( $query->post ) ) {
				$page_tos = $query->post;
				$pageid=$page_tos->ID;
			}
			else {
				$my_post = array(
				  'post_title'    => 'Terms of Service and Privacy Policy',
				  'post_content'  => '',
				  'post_status'   => 'publish',
				  'post_type' => 'page'
				);
				$pageid=wp_insert_post( $my_post );
			}
			if($pageid){
				ae_set_option('claim_term',$pageid);

			}
		}

		if(!ae_get_option('claim_page_detail') or get_post_status(ae_get_option('claim_page_detail'))<>"publish"){
			
			$query = new WP_Query(
				array(
					'post_type'              => 'page',
					'title'                  => 'Job Verification Decision',
					'posts_per_page'         => 1,
					'no_found_rows'          => true,
					'ignore_sticky_posts'    => true,
					'update_post_term_cache' => false,
					'update_post_meta_cache' => false,
					'post_status'			=> 'publish'
				)
			);
			if ( ! empty( $query->post ) ) {
				$page_job_verification_decision = $query->post;
				$pageid=$page_job_verification_decision->ID;
			}
			else {
				$my_post = array(
				  'post_title'    => 'Job Verification Decision',
				  'post_content'  => '[mje_claim_detail]',
				  'post_status'   => 'publish',
				  'post_type' => 'page'
				);
				$pageid=wp_insert_post( $my_post );
			}
			if($pageid){
				ae_set_option('claim_page_detail',$pageid);

			}
		}

    }

	public function filter_default_options( $default_options ) {
		$mje_claim=new MJE_Claim;


		$default_options['claim_tooltip_pending'] = 'Your request has been submitted. Please wait...';
		$default_options['claim_tooltip_verifying'] = 'Your request is being reviewed. Please wait...';
		$default_options['claim_tooltip_approved'] = 'Congrats! Your job verification request has been approved.';

		$default_options['claim_notice_verifying_seller'] = "Your request is being verified. Please wait and do not edit your mJob!";
		$default_options['claim_notice_approved_seller'] = 'Congrats! Your job is verified.';
		$default_options['claim_notice_declined_seller'] = 'Your request is declined by admin. Contact him or visit Job Verification for more details.';


		$default_options['claim_notice_verifying_admin'] = 'You are verifying this job verification request, please consider to approve or decline it.';
		$default_options['claim_notice_approved_admin'] = 'This job is successfully verified.';
		$default_options['claim_notice_declined_admin'] = 'This job verification request is declined. Visit Job Verification for more details.';

		$default_options['claim_mail_payment_cash'] = $mje_claim->claim_mail_cash();
		$default_options['claim_mail_payment'] = $mje_claim->claim_mail();
		$default_options['claim_mail_free'] = $mje_claim->claim_mail_free();

		$default_options['claim_government'] = $mje_claim->claim_government();


		return $default_options;
	}

	/* ajax remove notice in menu claim*/
	public function mje_remove_notice_claim_backend_function(){
		$id=$_POST['id'];
		$mje_claim=new MJE_Claim;
		if($mje_claim->is_admin()){
			if(update_post_meta($id,'notice_meta','')){
			?>
			<script>
				jQuery("#claim_notice_<?php echo $id; ?>").html('<a class="undo_notice" data-id="<?php echo $id; ?>" data-toggle="tooltip" data-placement="top" title="<?php _e('Undo this notice','mje_verification'); ?>"><i class="fa fa-undo" aria-hidden="true"></i></a>');
				jQuery('[data-toggle="tooltip"]').tooltip();
				blockUi.unblock();
				<?php
				$notice_count=$mje_claim->get_count_notice_claim();
				if($notice_count>0){
					?>
					jQuery('.claim_notice_count').html('<span class="pending-count"><?php echo $notice_count; ?></span>');
					<?php
				}
				else{
					?>
					jQuery('.claim_notice_count').html('');
					<?php
				}
				?>
			</script>
			<?php
			}
		}
		exit;
	}

	/* ajax undo removed notice in menu claim*/
	public function mje_undo_notice_claim_backend_function(){
		$id=$_POST['id'];
		$mje_claim=new MJE_Claim;
		if($mje_claim->is_admin()){
			if(update_post_meta($id,'notice_meta',1)){
			?>
			<script>
				jQuery("#claim_notice_<?php echo $id; ?>").html('<a href="<?php echo get_edit_post_link($id); ?>" data-toggle="tooltip" data-placement="top" title="<?php _e('View price change history ','mje_verification'); ?>" ><i class="fa fa-exclamation-triangle" aria-hidden="true"></i></a><span class="claim_notice_del" data-toggle="tooltip" data-placement="top" title="<?php _e('Hide this notice','mje_verification'); ?>" data-id="<?php echo $id ?>"><i class="fa fa-times-circle" aria-hidden="true"></i></span>');
				jQuery('[data-toggle="tooltip"]').tooltip();
				blockUi.unblock();
				<?php
				$notice_count=$mje_claim->get_count_notice_claim();
				if($notice_count>0){
					?>
					jQuery('.claim_notice_count').html('<span class="pending-count"><?php echo $notice_count; ?></span>');
					<?php
				}
				else{
					?>
					jQuery('.claim_notice_count').html('');
					<?php
				}
				?>
			</script>
			<?php
			}
		}
		exit;
	}
	/* registry new status for claim*/
	public function mje_claim_registy_new_status(){
			$mje_claim=new MJE_Claim;
			$arrs=$mje_claim->get_status_claim();
			foreach($arrs as $val=>$name){
				register_post_status( $val, array(
					'label'                     => _x( $name, 'mje_claims' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( $name.' <span class="count">(%s)</span>', $name.' <span class="count">(%s)</span>' ),
				) );
			}
		}
	/*-----filter status---*/
	public function meta_views_mje_claims($views){
		//wp_reset_query();
		$mje_claim=new MJE_Claim;
		$arrs=$mje_claim->get_status_claim();
		foreach($arrs as $slug=>$name){
			$arg=array(
				'post_type'=>'mje_claims',
				'posts_per_page'=>-1,
				'post_status' => $slug,
			);
			$count = count(get_posts($arg));
			if($count>0){
				$views[$slug]='<a href="edit.php?post_status='.$slug.'&post_type=mje_claims">'.$name.' <span class="count">('.$count.')</span></a>';
			}
		}
		return $views;
	}

	/*-----option claim------*/
	public function boot_add_option_mjob(){
		add_meta_box('Job Verification Option', 'Job Verification Option', 'MJE_Claim_Admin::boot_show_option_mjob_meta', 'mje_claims','normal','high');
	}

	public static function boot_show_option_mjob_meta() {
		global $post;
		echo '<input type="hidden" name="boot_mje_claim_option_nonce" value= "' . wp_create_nonce(basename(__FILE__)) . '"/>';
	?>
	<div class="claim-option">
		<?php
			$mjob=get_post($post->post_parent);
			$mje_claim=new MJE_Claim;
			$arrs=array(
				'mjob_meta'=>array(
					'name'=>'Mjob',
					'type'=>'link',
					'html'=>$mjob->post_title,
					'href'=>get_permalink($mjob->ID),
					'target'=>'_blank'
				),
				'claim_status_meta'=>array(
					'name'=>'Status',
					'type'=>'status',
					'data'=>$mje_claim->get_status_claim()
				),

				'new_name_meta'=>array('name'=>'Name'),
				'skype_meta'=>array('name'=>'SkypeID'),
				'pri_email_meta'=>array('name'=>'Primary email'),
				'alt_email_meta'=>array('name'=>'Alternate email'),
				'decline_reason_meta'=>array(
					'name'=>'Decline reason',
					'type'=>'textarea',
				),
				'photo_meta'=>array('name'=>'Photo','type'=>'photo'),
				'claim_fee'=>array('name'=>'Job Verification Fee','type'=>'text_percent'),
				'mjob_price'=>array('name'=>'Mjob Price','type'=>'text_price'),

				'price_history_meta'=>array(
					'name'=>'Price history',
					'type'=>'json_price',
				),
				'status_history_meta'=>array(
					'name'=>'Status history',
					'type'=>'json_status',
				),
				'notice_meta'=>array(
					'name'=>'Notice',
					'type'=>'hidden'
				),

			);
			echo $mje_claim->claim_option_meta($arrs);
		?>
	</div>
	<?php
	}
	public function boot_save_option_mjob($post_id) {
		global $custom_meta_fields;
		$mje_claim=new MJE_Claim;
		if(isset($_POST['boot_mje_claim_option_nonce'])){
				if (!wp_verify_nonce($_POST['boot_mje_claim_option_nonce'], basename(__FILE__)))
						return $post_id;
				$metas=array('claim_status_meta','mjob_price','claim_fee','new_name_meta','skype_meta','pri_email_meta','alt_email_meta','photo_meta','price_history_meta','status_history_meta','decline_reason_meta','notice_meta');
				foreach($metas as $meta){
					update_post_meta($post_id, $meta, $_POST[$meta]);
				}
				$p=get_post($post_id);
				if($p->post_status<>$_POST['claim_status_meta']){
					$mje_claim->update_post_status($post_id,$_POST['claim_status_meta']);
				}

		}
	}
	/*--- end optionclaim---*/

	public function add_content_colum_mje_claims($column_name, $post_ID) {
		$post=get_post($post_ID);
		$mje_claim=new MJE_Claim;
        switch ($column_name) {
            case "status_claim":
                $arrs=$mje_claim->get_status_claim();
                ?>
                <select class="choosen_status" data-id="<?php echo $post_ID ?>">
                    <?php
                    foreach($arrs as $val=>$name){
                        $selected=($val==$post->post_status)?'selected="selected"':'';
                        ?>
                        <option value="<?php echo $val; ?>" <?php echo $selected; ?>><?php echo $name; ?></option>
                        <?php
                    }
                    ?>
                </select>
                <?php
                break;
            case "payment_claim":
                $payment_method_txt_arr = mje_render_payment_name();
                echo (get_post_meta($post_ID,'payment_meta',true)<>"")?$payment_method_txt_arr[get_post_meta($post_ID,'payment_meta',true)]:'-';
                break;
            case "mjob_id":
                ?>
                <a href="<?php echo get_permalink($post->post_parent); ?>" target="_blank"><?php echo $post->post_parent; ?></a>
                <?php
                break;
			case "mjob_finish":
                ?>
                <span><?php echo $mje_claim->get_count_finished($post->post_parent)  ?></span>
                <?php
                break;
            case "claim_warming":
                $arrs=array('mje_pending','mje_verifying','mje_approved');
                if(in_array($post->post_status,$arrs) and get_post_meta($post_ID,'notice_meta',true)){
                    ?>
                    <div id="claim_notice_<?php echo $post_ID ?>" class="contain_claim_notice">
                        <a href="<?php echo get_edit_post_link($post_ID); ?>" data-toggle="tooltip" data-placement="top" title="<?php _e('View price change history','mje_verification'); ?>" ><i class="fa fa-exclamation-triangle" aria-hidden="true"></i></a>
                        <span class="claim_notice_del" data-toggle="tooltip" data-placement="top" title="<?php _e('Hide this notice','mje_verification'); ?>" data-id="<?php echo $post_ID ?>"><i class="fa fa-times-circle" aria-hidden="true"></i></span>
                    </div>
                    <?php
                }
                break;

        }

	}

	public function add_colum_mje_claims($defaults) {
		unset($defaults['date']);
		$defaults['mjob_id'] = __('mJob ID','mje_verification');
		$defaults['mjob_finish'] = __('Order finished','mje_verification');
		$defaults['status_claim'] = __('Status','mje_verification');
        $defaults['payment_claim'] =__('Payment type','mje_verification');
		$defaults['claim_warming'] = __('Price change history','mje_verification');
		$defaults['date']="Date";
		return $defaults;
	}

    public function add_admin_menu_pages( $pages ) {
        $options = AE_Options::get_instance();
        $temp = array();
        $sections = array();
        $sections['general'] = $this->get_general_section();
        $sections['content'] = $this->get_content_section();
        // Generate sections
        foreach ( $sections as $section ) {
            $temp[] = new AE_section( $section['args'], $section['groups'], $options );
        }

        // Create container
        $stripe_container = new AE_Container( array(
            'class' => '',
            'id' => 'settings'
        ), $temp, '' );

        // Create page
        $pages['mje-claim'] = array(
            'args' => array(
                'parent_slug' => 'et-welcome',
                'page_title' => __( 'mJob Verification', 'mje_verification' ),
                'menu_title' => __( 'mJob Verificatio', 'mje_verification' ),
                'cap' => 'administrator',
                'slug' => 'et-mje-claim',
                'icon' => 'fa fa-trophy',
                'desc' => __( 'An extension for MicrojobEngine', 'mje_verification' ),
            ),
            'container' => $stripe_container
        );

        return $pages;
    }

	public function add_notice_claim_menu(){
		  global $menu;
		  $mje_claim=new MJE_Claim;
		  $notice_count = $mje_claim->get_count_notice_claim();
		  foreach ( $menu as $key => $value ) {
			  if ( $menu[$key][2] == 'edit.php?post_type=mje_claims' ) {
				if($notice_count>0){
					$menu[$key][0] .= '<span class="awaiting-mod count-1 claim_notice_count"><span class="pending-count">'.$notice_count.'</span></span>';
				}
				return;
			  }
		  }
	}

	public function get_general_section () {
        $mje_claim=new MJE_Claim;
		$et_admin= new ET_Admin;
		$default_option=$et_admin->get_default_options();
		$sections = array(
            'args' => array(
                'title' => __( 'General Settings', 'mje_verification' ),
                'id' => 'ms-general',
                'class' => '',
                'icon' => '',
            ),
            'groups' => array(
                array(
                    'args' => array(
                        'title' => __( 'Config', 'mje_verification' ),
                        'id' => '',
                        'class' => 'claim_admin_config',
                        'desc' => '',
                    ),
                    'fields' => array(
                        array(
                            'id' => 'claim_order_finished',
                            'class' => 'check_integer_validate',
                            'type' => 'number',
                            'title' => __( 'Order Finished', 'mje_verification' ),
                            'desc' => __( 'Set the minimum orders that sellers must finish on a mJob to be able to submit the verification request for that mjob.', 'mje_verification'),
                            'name' => 'claim_order_finished',
							'default'=>2,
                        ),						
						array(
                            'id' => 'claim_fee',
							'name'=> 'claim_fee',
                            'class' => 'check_integer_validate_positive',
                            'type' => 'number',
                            'title' => __( 'Job Verification Commission Fee', 'mje_verification' ),
                            'desc' => __( 'Set up the commission fee as the percentage (%) of mJob price for a job verification (job verification commission = % commission * mJob price).', 'mje_verification'),
							'default'=>10,
                        ),						
						array(
                            'id' => 'claim_number',
							'name'=> 'claim_number',
                            'class' => 'check_integer_validate',
                            'type' => 'number',
                            'title' => __( 'Job Verification Display', 'mje_verification' ),
                            'desc' => __( "The number of job verification that is displayed in the user's dashboard.", 'mje_verification'),
							'default'=>10,
                        ),
						//custom code 18th jun 2024
						array(
                            'id' => 'claim_price_fixed_value',
							'name'=> 'claim_price_fixed_value',
                            'class' => 'check_integer_validate',
                            'type' => 'number',
                            'title' => __( 'Job Verification Fixed Value', 'mje_verification' ),
                            'desc' => __( "The fixed price for verifying service", 'mje_verification'),
							'default'=>10,
                        ),
						//end custom code
						array(
                            'id' => 'claim_page',
							'name'=> 'claim_page',
                            'class' => '',
                            'type' => 'select',
                            'title' => __( 'User Job Verification Page', 'mje_verification' ),
                            'desc' => __( 'Choose a page to display all job verification of a seller.', 'mje_verification'),
							'data' =>$mje_claim->get_page_list(),
                        ),
						array(
                            'id' => 'claim_page_detail',
							'name'=> 'claim_page_detail',
                            'class' => '',
                            'type' => 'select',
                            'title' => __( 'Job Verification Page Detail', 'mje_verification' ),
                            'desc' => __( 'Choose a page displaying all informations of a job verification.', 'mje_verification'),
							'data' =>$mje_claim->get_page_list(),
                        ),
						array(
                            'id' => 'claim_term',
							'name'=> 'claim_term',
                            'class' => '',
                            'type' => 'select',
                            'title' => __( 'Terms of Service and Privacy Policy Page', 'mje_verification' ),
                            'desc' => __( 'Choose a page displaying as the Terms of Service and Privacy Policy pages.', 'mje_verification'),
							'data' =>$mje_claim->get_page_list()
                        ),


                    )

                ),
            )
        );

        return $sections;
    }


	public function get_content_section () {
        $mje_claim=new MJE_Claim;
		$et_admin= new ET_Admin;
		$default_option=$et_admin->get_default_options();
		$sections = array(
            'args' => array(
                'title' => __( 'Content Settings', 'mje_verification' ),
                'id' => 'ms-content',
                'class' => '',
                'icon' => '',
            ),
            'groups' => array(
				array(
                    'args' => array(
                        'title' => __( 'Job Verification Submission Form', 'mje_verification' ),
                        'id' => '',
                        'class' => '',
                        'desc' => ''
                    ),
                    'fields' => array(
                        array(
                            'id' => 'claim_government',
							'name'=> 'claim_government',
                            'class' => '',
                            'type' => 'editor',
                            'title' => __( 'Photo ID Upload Guidelines', 'mje_verification' ),
                            'desc' => __( 'Set up the instructions and requirements for photo ID submission. This text will display on the basic information form when seller requests for his job verification.', 'mje_verification'),
							'default'=> $default_option['claim_government'],
							'reset' => true
                        ),
                    ),
                ),
                array(
                    'args' => array(
                        'title' => __( 'Tooltip', 'mje_verification' ),
                        'id' => '',
                        'class' => '',
                        'desc' => ''
                    ),
                    'fields' => array(
                        array(
                            'id' => 'claim_tooltip_pending',
							'name'=> 'claim_tooltip_pending',
                            'class' => '',
                            'type' => 'editor',
                            'title' => __( 'Pending Tooltip', 'mje_verification' ),
                            'desc' => __( 'Set up the tooltip for pending status. This tooltip will be shown on the front-end when the job verification is pending.', 'mje_verification'),
							'default'=> $default_option['claim_tooltip_pending'],
							'reset' => true
                        ),

						array(
                            'id' => 'claim_tooltip_verifying',
							'name'=> 'claim_tooltip_verifying',
                            'class' => '',
                            'type' => 'editor',
                            'title' => __( 'Verifying Tooltip', 'mje_verification' ),
                            'desc' => __( 'Set up the tooltip for verifying status. This tooltip will be shown on the front-end when the job verification is under admin review.', 'mje_verification'),
							'default'=> $default_option['claim_tooltip_verifying'],
							'reset' => true
                        ),
						array(
                            'id' => 'claim_tooltip_approved',
							'name'=> 'claim_tooltip_approved',
                            'class' => '',
                            'type' => 'editor',
                            'title' => __( 'Approved Tooltip', 'mje_verification' ),
                            'desc' => __( 'Set up the tooltip for approved status. This tooltip will be shown on the front-end when the job verification is approved by admin.', 'mje_verification'),
							'default'=> $default_option['claim_tooltip_approved'],
							'reset' => true,
                        ),
                    ),
                ),
                array(
                    'args' => array(
                        'title' => __( 'Notice', 'mje_verification' ),
                        'id' => '',
                        'class' => '',
                        'desc' => ''
                    ),
                    'fields' => array(
                        /* notice seller ***/
						array(
                            'id' => 'claim_notice_verifying_seller',
							'name'=> 'claim_notice_verifying_seller',
                            'class' => '',
                            'type' => 'editor',
                            'title' => __( 'Verifying Notification For Seller', 'mje_verification' ),
                            'desc' => __( 'Set up the notification for the verifying status, which will be shown on the front-end when the job is being verified by admin.', 'mje_verification'),
							'default'=> $default_option['claim_notice_verifying_seller'],
							'reset' => true
                        ),
						array(
                            'id' => 'claim_notice_approved_seller',
							'name'=> 'claim_notice_approved_seller',
                            'class' => '',
                            'type' => 'editor',
                            'title' => __( 'Approved Notification For Seller', 'mje_verification' ),
                            'desc' => __( 'Set up the notification for the approved status, which will be shown on the front-end when the job verification is approved by admin.', 'mje_verification'),
							'default'=> $default_option['claim_notice_approved_seller'],
							'reset' => true,
                        ),
						array(
                            'id' => 'claim_notice_declined_seller',
							'name'=> 'claim_notice_declined_seller',
                            'class' => '',
                            'type' => 'editor',
                            'title' => __( 'Declined Notification For Seller', 'mje_verification' ),
                            'desc' => __( 'Set up the notification for the declined status, which will be shown on the front-end when the job verification is declined by admin.', 'mje_verification'),
							'default'=> $default_option['claim_notice_declined_seller'],
							'reset' => true
                        ),
						/* notice admin ***/
						array(
                            'id' => 'claim_notice_verifying_admin',
							'name'=> 'claim_notice_verifying_admin',
                            'class' => '',
                            'type' => 'editor',
                            'title' => __( 'Verifying Notification For Admin', 'mje_verification' ),
                            'desc' => __( 'Set up the notification for the verifying status, which will be shown on the front-end when you are verifying a job.', 'mje_verification'),
							'default'=> $default_option['claim_notice_verifying_admin'],
							'reset' => true
                        ),
						array(
                            'id' => 'claim_notice_approved_admin',
							'name'=> 'claim_notice_approved_admin',
                            'class' => '',
                            'type' => 'editor',
                            'title' => __( 'Approved Notification For Admin', 'mje_verification' ),
                            'desc' => __( 'Set up the notification for the approved status, which will be shown on the front-end when you approve a job verification.', 'mje_verification'),
							'default'=> $default_option['claim_notice_approved_admin'],
							'reset' => true,
                        ),
						array(
                            'id' => 'claim_notice_declined_admin',
							'name'=> 'claim_notice_declined_admin',
                            'class' => '',
                            'type' => 'editor',
                            'title' => __( 'Declined Notification For Admin', 'mje_verification' ),
                            'desc' => __( 'Set up the notification for the declined status, which will be shown on the front-end when you decline a job verification.', 'mje_verification'),
							'default'=> $default_option['claim_notice_declined_admin'],
							'reset' => true
                        ),
                    )
                ),
                array(
                    'args' => array(
                        'title' => __( 'Email content', 'mje_verification' ),
                        'id' => '',
                        'class' => '',
                        'desc' => ''
                    ),
                    'fields' => array(
                        array(
                            'id' => 'claim_mail_payment_cash',
							'name'=> 'claim_mail_payment_cash',
                            'class' => '',
                            'type' => 'editor',
                            'title' => __( 'Cash payment receipt notification', 'mje_verification' ),
                            'desc' => __( 'Send to user when he pays by cash', 'mje_verification'),
							'default'=> $default_option['claim_mail_payment_cash'],
							'reset' => true
                        ),

						array(
                            'id' => 'claim_mail_payment',
							'name'=> 'claim_mail_payment',
                            'class' => '',
                            'type' => 'editor',
                            'title' => __( 'Payment receipt notification', 'mje_verification' ),
                            'desc' => __( 'Send to user when he pays via other payment gateways (exclude Cash)', 'mje_verification'),
							'default'=> $default_option['claim_mail_payment'],
							'reset' => true
                        ),

						array(
                            'id' => 'claim_mail_free',
							'name'=> 'claim_mail_free',
                            'class' => '',
                            'type' => 'editor',
                            'title' => __( 'Job Verification Detailed Information', 'mje_verification' ),
                            'desc' => __( 'Send to the seller after he submits his job verification request.', 'mje_verification'),
							'default'=> $default_option['claim_mail_free'],
							'reset' => true
                        ),
                    )
                )
            )
        );

        return $sections;
    }

	public function add_seletor_hide_footer() {
		ob_start();
		?>
		<div style="display:none">
			<div id="hide_me"></div>
		</div>
		<?php
		echo ob_get_clean();
	}

	public function mje_change_status_claim_backend_function(){
		$mje_claim=new MJE_Claim;
		if($mje_claim->update_post_status($_POST['id'],$_POST['stt'])){
			if($_POST['stt']=="mje_declined"){
				update_post_meta($_POST['id'],'decline_reason_meta',$_POST['rs']);
			}
		?>
		<script>
			jQuery('.choosen_status[data-id="<?php echo $_POST['id'] ?>"]').parents(".column-status_claim").append('<i class="fa fa-check" aria-hidden="true"></i>');
			timeout = setTimeout(function() {
				jQuery('.choosen_status[data-id="<?php echo $_POST['id'] ?>"]').parents(".column-status_claim").find('.fa-check').remove();
			}, 2000);

			blockUi.unblock();
		</script>
		<?php
		}
	exit;
	}

	public function mje_restore_status_claim_backend_function(){
		$claim=get_post($_POST['id']);
		$stt=$claim->post_status;
		?>
		<script>
			jQuery('.choosen_status[data-id="<?php echo $_POST['id'] ?>"]').val('<?php echo $stt ?>');
		</script>
		<?php
		exit;
	}

}

new MJE_Claim_Admin();
?>