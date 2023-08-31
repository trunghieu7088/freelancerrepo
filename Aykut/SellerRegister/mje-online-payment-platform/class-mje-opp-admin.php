<?php

class MJE_OPP_Admin extends AE_Base {

	public static $instance;

	public static function get_instance() {
		if ( self::get_instance() == null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		$this->add_filter( 'mje_payment_gateway_setting_sections', 'add_sections' );
	}

	/**
	 * Add settings page for Online Payment Plateform
	 *
	 * @param array $pages
	 * @return array $pages
	 * @since 1.0.0
	 * @author Tat Thien
	 */
	public function add_sections( $sections ) {
		$sections['mje-opp'] = $this->get_general_section();
		return $sections;
	}

	/**
	 * Generate general settings section
	 *
	 * @param void
	 * @return array $sections
	 * @since 1.1.4
	 * @author Tat Thien
	 */
	public function get_general_section() {
		$sections = array(
			'args'   => array(
				'title' => __( 'Online Payment Plateform', 'mje_opp' ),
				'id'    => 'mje-opp',
				'class' => '',
				'icon'  => '',
			),
			'groups' => array(
				array(
					'args'   => array(
						'title' => __( 'Online Payment Plateform', 'mje_opp' ),
						'id'    => '',
						'class' => '',
						'desc'  => '',
					),
					'fields' => array(
						array(
							'id'    => 'mje-opp-enable',
							'class' => '',
							'type'  => 'switch',
							'title' => __( 'Using Online Payment Plateform', 'mje_opp' ),
							'desc'  => __( 'Enabling this will activate Online Payment Plateform payment gateway.', 'mje_opp' ),
							'name'  => 'mje_opp_enable',
						),
						/* API key */
						array(
							'id'       => 'mje-opp-api',
							'class'    => '',
							'type'     => 'combine',
							'title'    => __( 'Online Payment Plateform API', 'mje_opp' ),
							'desc'     => __( 'The Online Payment Plateform API by providing one of your API keys in the request.', 'mje_opp' ),
							'name'     => '',
							'children' => array(
								array(
									'id'    => 'mje-opp-test-merchant-uid',
									'class' => 'opp-test-key',
									'type'  => 'text',
									'title' => __( 'Test merchant uid', 'mje_opp' ),
									'desc'  => '',
									'name'  => 'mje_opp_test_merchant_uid',
								),
								array(
									'id'    => 'mje-opp-test-api-key',
									'class' => 'opp-test-key',
									'type'  => 'text',
									'title' => __( 'Test API key', 'mje_opp' ),
									'desc'  => '',
									'name'  => 'mje_opp_test_api_key',
								),
								array(
									'id'    => 'mje-opp-test-notification-secret',
									'class' => 'opp-test-key',
									'type'  => 'text',
									'title' => __( 'Test notification secret', 'mje_opp' ),
									'desc'  => '',
									'name'  => 'mje_opp_test_notification_secret',
								),
								array(
									'id'    => 'mje-opp-live-merchant-uid',
									'class' => 'opp-live-key',
									'type'  => 'text',
									'title' => __( 'Live merchant uid', 'mje_opp' ),
									'desc'  => '',
									'name'  => 'mje_opp_live_merchant_uid',
								),
								array(
									'id'    => 'mje-opp-live-api-key',
									'class' => 'opp-live-key',
									'type'  => 'text',
									'title' => __( 'Live API key', 'mje_opp' ),
									'desc'  => '',
									'name'  => 'mje_opp_live_api_key',
								),
								array(
									'id'    => 'mje-opp-live-notification-secret',
									'class' => 'opp-live-key',
									'type'  => 'text',
									'title' => __( 'Live publishable key', 'mje_opp' ),
									'desc'  => '',
									'name'  => 'mje_opp_live_notification_secret',
								),
							),
						),
						array(
							'id'    => 'mje-opp-production-mode',
							'class' => '',
							'type'  => 'switch',
							'title' => __( 'Production Mode', 'mje_opp' ),
							'desc'  => __( 'Enabling this will allow you to use the minify version of CSS or Javascript.', 'mje_opp' ),
							'name'  => 'mje_opp_production_mode',
						),
					),
				),
			),
		);

		return $sections;
	}
}

new MJE_OPP_Admin();
