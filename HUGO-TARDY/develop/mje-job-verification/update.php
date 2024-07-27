<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (class_exists('AE_Plugin_Updater')) {
    class MJE_Claim_Updater extends AE_Plugin_Updater
    {
        const VERSION = MJE_CLAIM_VERSION;
        public function __construct()
        {
            $this->product_slug     = plugin_basename(dirname(__FILE__) . '/mje-job-verification.php');
            $this->slug             = 'mje_job_verification';
            $this->license_key         = get_option('et_license_key', '');
            $this->current_version     = self::VERSION;
            $this->update_path         = 'https://update.enginethemes.com/?do=product-update&product=mje_job_verification&type=plugin';
            parent::__construct();
        }
    }

    new MJE_Claim_Updater();
}
