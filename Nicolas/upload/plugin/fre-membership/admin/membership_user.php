<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . '/wp-admin/includes/class-wp-list-table.php' );
}

class Membership_User extends WP_List_Table
{
    /**
     * @var array $count_users
     */
    protected static $count_users;

    public function __construct()   {
        parent::__construct( array(
            'singular' => MEMBERSHIP_SINGULAR,
            'plural' => MEMBERSHIP_PLURAL,
            'ajax' => true
        ) );

        self::get_count_users();

        // remove _wpnonce and _wp_http_referer
        $_SERVER['REQUEST_URI'] = remove_query_arg( '_wpnonce', $_SERVER['REQUEST_URI'] );
        $_SERVER['REQUEST_URI'] = remove_query_arg( '_wp_http_referer', $_SERVER['REQUEST_URI'] );


        $per_page = 10; // $this->get_items_per_page( 'users_per_page', 5 );
        $current_page = $this->get_pagenum();

        global $wpdb;
        $tbl_member         = $wpdb->prefix . 'fre_membership';
        $tbl_subscriptions  = $wpdb->prefix . 'fre_subscriptions';

        $where = '';
        if( ! empty( $_GET['s'] ) ) {
            $search_str = trim( $_GET['s'] );
            $search_str = esc_attr( $search_str );
            $where = " WHERE user_login like '%{$search_str}'";
        }
        $sql = "SELECT * FROM $tbl_member m LEFT JOIN $tbl_subscriptions sub ON m.subscr_id = sub.id {$where} ORDER BY sub.id ";

        $offset = ($current_page-1)*$per_page;

        $in_paging = "SELECT * FROM $tbl_member m LEFT JOIN $tbl_subscriptions sub ON m.subscr_id = sub.id {$where} ORDER BY sub.id DESC LIMIT {$per_page} OFFSET $offset";


        $results = $wpdb->get_results($in_paging);

        $this->items = $results;

        $members = (object) array('results'=> 0);
        $members->results = $wpdb->get_results($sql);
        $this->_pagination_args['total_items'] = count( $members->results );
        $this->_pagination_args['total_pages']  = ceil( count($members->results) / $per_page );

    }

    /**
     * Build query args to filter users by available fund
     *
     * @return array $args
     */
    public static function filter_available_fund_args( $args ) {
        if( ! isset( $_GET['orderby'] ) ) {
            return $args;
        }

        $users = get_users( array(
            'number' => -1,
            'fields' => array( 'ID', 'user_email', 'user_login', 'user_registered' )
        ) );
        global $wpdb;
        $tbl_member         = $wpdb->prefix . 'fre_membership';
        $tbl_subscriptions  = $wpdb->prefix . 'fre_subscriptions';

        $sql = "SELECT * FROM $tbl_member m LEFT JOIN $tbl_subscriptions sub ON m.subscr_id = sub.id ";
        $members = $wpdb->get_resuts($sql);
        foreach ( $members as $user ) {

            $user_wallet = FRE_Credit_Users()->getUserWallet($user->ID);
            $user->balance = $user_wallet->balance;
        }

        // sort user
        usort( $users, function( $a, $b ) {
           if( $a->balance == $b->balance ) { return 0; }

           if( $_GET['order'] == 'asc' ) {
               return $a->balance > $b->balance ? 1 : -1;
           } else {
               return $a->balance > $b->balance ? -1 : 1;
           }
        });

        $user_ids = array();
        foreach ( $users as $user ) {
            array_push( $user_ids, $user->ID );
        }


        $args = array_merge( array(
            'include' => $user_ids,
            'orderby' => 'include'
        ), $args );

        return $args;
    }

    /**
     * Get total users
     *
     * @return mixed
     */
    public static function get_count_users() {
        self::$count_users = count_users();
        return self::$count_users;
    }

    /**
     * Get available user roles
     *
     * @return mixed
     */
    public static function get_user_roles() {
        unset( self::$count_users['avail_roles']['none'] );
        return self::$count_users['avail_roles'];
    }

    /**
     * Method for user_login column
     *
     * @param object $item
     * @return string
     */
    function column_user_login( $item ) {

        $user_profile_link = get_author_posts_url( $item->user_id );
        $title = '<strong><a href="'. $user_profile_link .'" target="_blank">' . $item->user_login . '</a></strong>';

        $actions = array(
            'view' => sprintf( __( '<a href="%s" target="_blank">View profile</a>', 'enginethemes' ), $user_profile_link )
        );

        return $title . $this->row_actions( $actions );
    }
    function column_start_date($item){
        echo $item->start_date;
        //echo date('M d, Y', $item->start_date);
    }
    function column_auto_renew($item){
        echo $item->auto_renew;

    }
    function column_expiry_date( $item ) {

        echo date('M d, Y', $item->expiry_time);

    }
    function column_pack_info( $item ) {
        $setting_url = admin_url('admin.php?page=fre-membership');

        $pack_type = get_pack_type_of_user($item->user_id);
        $pack       = membership_get_pack($item->plan_sku, $pack_type);

        if( $pack && ! is_wp_error($pack) ){   ?>
            <a href="<?php echo $setting_url;?>"><?php echo $pack->post_title;?></a>(<?php echo  fre_price_format($pack->et_price);?>)
            <?php
        } else {
            echo $item->plan_sku;
        }
    }
    function column_payment_status( $item ) {

       echo $item->payment_gw.'/'.$item->payment_status;

    }
    function column_action( $item ) {
        ?>
        <a href="#" btn="del_membership"> Del</a>
        <?php



    }
    /**
     * Render a column when no column specific method exists.
     *
     * @param array $item
     * @param string $column_name
     *
     * @return mixed
     */
    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'user_login':
            case 'user_email':
            case 'starte_date':
                return $item->$column_name;
            default:
                return print_r( $item, true ); //Show the whole array for troubleshooting purposes
        }
    }

    /**
     * Associative array of columns
     *
     * @return array
     */
    function get_columns() {
       $columns = array(
            'user_login' => __( 'Username' ),
            'user_email' => __( 'Email' ),
            'start_date' => __( 'Start Registered' ),
            'expiry_date' => __( 'Expiration Date', 'enginethemes' ),
            'pack_info' => __( 'Subscription Info', 'enginethemes' ),
            'payment_status' => __( 'Subscription Status', 'enginethemes' ),
            'auto_renew' => __( 'Auto Renew', 'enginethemes' ),
            // 'action' => __( 'Action', 'enginethemes' ),
       );

       return $columns;
    }

    /**
     * Columns to make sortable
     *
     * @return array
     */
    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'user_registered' => array( 'user_registered', false ),
            'available_fund' => array( 'available_fund', true )
        );

        return $sortable_columns;
    }

    /**
     * Generates content for a single row of the table
     *
     * @since 3.1.0
     * @access public
     *
     * @param object $item The current item
     */
    public function single_row( $item ) {
        $user_id = isset($_GET['user_id']) ?$_GET['user_id'] : 0;

        echo '<tr id="topup-user-' . $item->user_id . '" class="topup-user-row single_row">';
        $this->single_row_columns( $item );
        echo '</tr>';
    }

    /**
     * Render filter by roles
     */
    function render_roles_filter() {
        $user_roles = self::get_user_roles();

        $setting_url = FRE_Membership_List::get_instance()->get_setting_url();
        ?>
        <ul class="subsubsub hide" style="display: none;">
            <li>
                <a href="<?php echo $setting_url; ?>"
                   class="<?php echo empty( $_GET['role'] ) ? 'current' : ''; ?>"
                >
                    <?php printf( __( 'All <span class="count">(%s)</span>' ), self::$count_users['total_users'] ); ?>
                </a>
            </li>

            <?php foreach ( $user_roles as $role => $count ) : ?>
                | <li>
                    <a href="<?php echo $setting_url . '&role=' . $role; ?>"
                       class="<?php echo ( isset( $_GET['role']) && $_GET['role'] == $role ) ? 'current' : ''; ?>"
                    >
                        <?php printf( __( '%s <span class="count">(%s)</span>' ), ucfirst( $role ), $count ); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php
    }

    /**
     * Message to be displayed when there are no items
     *
     * @since 3.1.0
     * @access public
     */
    public function no_items() {
        _e( 'No users found .' );
    }

    /**
     * Handle data query anh filter, sorting and pagination.
     */
    public function prepare_items()
    {
         $this->_pagination_args['total_items'] = 1000;
         $this->_pagination_args['total_pages']  = 10;

        $this->_column_headers = $this->get_column_info();

        $per_page = $this->get_items_per_page( 'users_per_page', 20 );
        $current_page = $this->get_pagenum();
        $user_query = self::get_users( $per_page, $current_page );

        $this->items = $user_query->results;

        // if( empty( $this->items ) ) {
        //     $this->set_pagination_args( array() );
        // } else {
            // $this->set_pagination_args( array(
            //     'total_items' => 100,$user_query->total_users,
            //     'total_pages' => 10, //ceil( $user_query->total_users / $per_page ),
            //     'per_page' => 1,//$per_page,
            // ) );
        //}
    }
    // protected function set_pagination_args( $args ) {
    //     $args = wp_parse_args(
    //         $args,
    //         array(
    //             'total_items' => 100,
    //             'total_pages' => 10,
    //             'per_page'    => 10,
    //         )
    //     );

    //     // if ( ! $args['total_pages'] && $args['per_page'] > 0 ) {
    //     //     $args['total_pages'] = ceil( $args['total_items'] / $args['per_page'] );
    //     // }

    //     // // Redirect if page number is invalid and headers are not already sent.
    //     // if ( ! headers_sent() && ! wp_doing_ajax() && $args['total_pages'] > 0 && $this->get_pagenum() > $args['total_pages'] ) {
    //     //     wp_redirect( add_query_arg( 'paged', $args['total_pages'] ) );
    //     //     exit;
    //     }

    //     $this->_pagination_args = $args;
    // }
}