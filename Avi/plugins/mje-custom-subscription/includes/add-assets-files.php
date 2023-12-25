<?php
function addAssetsFiles()
{
    global $post;
    if(isset($post) && !empty($post))
	{
        $template_page=get_post_meta($post->ID,'_wp_page_template',true);
		if($template_page=='custom-subscription-template.php' || $template_page=='custom-detail-subscription-plan.php'  || $template_page=='my-subscription-template.php')
		{
            wp_enqueue_style( 'custom-subscription-style', CUSTOM_SUBSCRIPTION_MJE_URL. 'assets/css/custom-subscription.css', array(), CUSTOM_SUBSCRIPTION_MJE_VERSION ) ;
            wp_enqueue_script('custom-subscription-js', CUSTOM_SUBSCRIPTION_MJE_URL.'assets/js/custom-subscription.js', array(), CUSTOM_SUBSCRIPTION_MJE_VERSION, true);
        }

        if($template_page=='custom-manage-subscription.php')
		{
            wp_enqueue_style( 'custom-admin-subscription-style', CUSTOM_SUBSCRIPTION_MJE_URL. 'assets/css/admin-custom-subscription.css', array(), CUSTOM_SUBSCRIPTION_MJE_VERSION ) ;
            wp_enqueue_script('admin-subscription-js', CUSTOM_SUBSCRIPTION_MJE_URL.'assets/js/admin-subscription.js', array(), CUSTOM_SUBSCRIPTION_MJE_VERSION, true);

            wp_enqueue_style( 'custom-admin-subscription-datatable-style', CUSTOM_SUBSCRIPTION_MJE_URL. 'assets/datatables/datatables.min.css', array(), CUSTOM_SUBSCRIPTION_MJE_VERSION ) ;
            wp_enqueue_script('custom-admin-subscription-datatable-js', CUSTOM_SUBSCRIPTION_MJE_URL.'assets/datatables/datatables.min.js', array(), CUSTOM_SUBSCRIPTION_MJE_VERSION, true);
        }
        
    }
}

add_action('wp_enqueue_scripts', 'addAssetsFiles',999);