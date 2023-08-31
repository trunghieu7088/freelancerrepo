<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class FRE_Membership_List
{
    private static $_instance;

    public $membership_table = '';

    /**
     * @return MJE_Topup $_instance
     */
    public static function get_instance()
    {
        if( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct()
    {
        // register menu
        add_action( 'admin_menu', array( $this, 'register_menu' ) );
        add_action( 'admin_footer', array( $this, 'add_template_js' ) );
        define( 'MEMBERSHIP_SINGULAR', __( 'Membership ', 'enginethemes' ) );
        define( 'MEMBERSHIP_PLURAL', __( 'Membership', 'enginethemes' ) );

    }

    public function register_menu() {
        $hook = add_menu_page(
            MEMBERSHIP_SINGULAR,
            MEMBERSHIP_PLURAL,
            'manage_options',
            'membership-view.php',
            array( $this, 'render_setting_page' ),
            '',
            59
        );

        add_action( "load-$hook", array( $this, 'screen_option' ) );

    }

    /**
     * Screen options
     */
    public function screen_option() {
        $option = 'per_page';
        $args = array(
            'label' => __( 'Number of users per page', 'enginethemes' ),
            'default' => 20,
            'option' => 'users_per_page'
        );

        add_screen_option( $option, $args );

        $this->membership_table = new Membership_User();
    }

    public function add_template_js() {
       ?>
       <style type="text/css">
           .current.toplevel_page_membership-view .dashicons-before:before,
           #toplevel_page_membership-view .dashicons-before:before{
                content: "\f110" !important;
           }
       </style>
       <?php
    }

    /**
     * Setting page for Topup
     */
    public function render_setting_page() {
        // mje_get_template( 'template/topup/settings.php', array( 'membership_table' => $this->membership_table ) );
        ?>
        <div class="wrap" id="js-membership-view">
            <h2><?php echo MEMBERSHIP_PLURAL; ?>
                <?php if( ! empty( $_GET['s'] ) ) : ?>
                <span class="subtitle"><?php printf( __( 'Search results for "%s"' ), $_GET['s'] ); ?></span>
                <?php endif; ?>
            </h2>
            <form method="GET">
                <input type="hidden" name="page" value="membership-view.php">
                <?php
                    $membership_table = $this->membership_table;
                    $membership_table->search_box( __( 'Search Users' ), 'user' );
                    $membership_table->render_roles_filter();
                    $membership_table->display();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Getting setting page url
     *
     * @return string
     */
    public function get_setting_url() {
        return admin_url( 'admin.php?page=membership-view.php' );
    }

}

$mebershipList = FRE_Membership_List::get_instance();