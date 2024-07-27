<?php
class MJE_Claim_Post_Type extends MJE_Post
{
    public static $instance;

    public static function get_instance()
    {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor of class
     * @param string $post_type
     * @param array $taxs
     * @param array $meta_data
     * @param array $localize
     */
    public function __construct( $post_type = '', $taxs = array(), $meta_data = array(), $localize = array() )
    {
        $this->post_type = 'mje_claims';
        parent::__construct( $this->post_type, $taxs, $meta_data, $localize );
    }

    /**
     * Init post type
     * @param void
     * @return void
     * @since 1.3
     * @author Tat Thien
     */
    public function init_post_type ()
    {
        $support_arr=array('title','author');
		$args = array(
            'labels' => array(
                'name' => __( 'Job Verification', 'mje_verification'),
                'singular_name' => __( 'Claim', 'mje_verification' ),
                'add_new' => __( 'Add New', 'mje_verification' ),
                'add_new_item' => __( 'Add New Job Verification', 'mje_verification' ),
                'edit_item' => __( 'Edit Job Verification', 'mje_verification' ),
                'new_item' => __( 'New Job Verification', 'mje_verification' ),
                'all_items' => __( 'All Job Verification', 'mje_verification' ),
                'view_item' => __( 'View Job Verification', 'mje_verification' ),
                'search_items' => __( 'Search Job Verification', 'mje_verification' ),
                'not_found' => __( 'No Claims found', 'mje_verification' ),
                'not_found_in_trash' => __( 'No Job Verification found in Trash', 'mje_verification' ),
                'parent_item_colon' => '',
                'menu_name' => __( 'Job Verification', 'mje_verification' ),
            ),
			'supports'=>$support_arr,
			'menu_icon' => MJE_CLAIM_URL.'assets/images/cup.png',
        );
        $this->register_posttype($args);

    }
}

add_action( 'init', function() {
    $new_instance = MJE_Claim_Post_Type::get_instance();
    $new_instance->init_post_type();
} );