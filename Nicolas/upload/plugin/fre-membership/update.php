<?php
if( !class_exists( 'Fremembership_Update' ) && class_exists( 'AE_Plugin_Updater' ) ) {
	class Fremembership_Update extends AE_Plugin_Updater {
		const VERSION = FRE_MEMBERSHIP_VER;

		// setting updater
		public function __construct() {
			$this->plugin_slug = plugin_basename( dirname( __FILE__ ) . '/fre_membership.php' );
			$this->slug = 'fre_membership';
			$this->license_key = get_option( 'et_license_key' );
			$this->update_path = 'http://update.enginethemes.com/?do=product-update&product=fre-membership&type=plugin';

			parent::__construct();
		}
	}

	new Fremembership_Update();
}