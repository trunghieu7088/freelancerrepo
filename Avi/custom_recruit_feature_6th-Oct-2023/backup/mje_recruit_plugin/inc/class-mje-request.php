<?php
class MJE_Request extends AE_Post_Custom{


	public static $instance;
	public $post_type;
    public $post_type_singular;
    public $post_type_regular;

	function __construct(){
        parent::__construct();
		$this->post_type = MJOB_RECRUIT;
        $taxs = array('mjob_category','skill');
        $meta_data = array('et_budget','time_delivery');
        parent::__construct( $this->post_type, $taxs, $meta_data, $localize = array() );
        // $this->post_type_singular = 'Microjob Extra';
        // $this->post_type_regular = 'Microjob Extras';
        $this->meta = array(
            'et_budget','time_delivery'
        );

		$this->init_hook();
	}
	function init_hook(){
		add_action('init',array($this, 'register_post_type' ));

        //add_filter('ae_convert_recruit',array($this,'convert_recruit') );
	}
	public static function get_instance()    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    function convert($post, $thumbnail = 'medium_post_thumbnail', $excerpt = true, $singular = false){
        $post->number_offers = (int) get_post_meta( $post->ID, 'number_offers',true );
        //$post->number_offers = count_offers_of_request($post->ID);
        $post->number_offers_txt = sprintf(__('%s offers','mje_recruit'),$post->number_offers);
        $post->date_txt =  sprintf(__('Posted %s','mje_recruit'), date( get_option('date_format'),  strtotime($post->post_date) ) );

        $post->tag_txt = mre_get_list_tax_of_request($post->ID, '', 'skill' );
        $post->post_excerpt =  wp_trim_words( $post->post_content, 62);
        $post->budget_txt = ae_price_format($post->et_budget);
        $post->permalink = get_permalink($post->ID);
        $post->redirect_url = $post->permalink;
        return $post;

    }

	function register_post_type(){
		$labels = array(
			'name'               => _x( 'Recruit', 'post type general name', 'mje_recruit' ),
			'singular_name'      => _x( 'Recruit', 'post type singular name', 'mje_recruit' ),
			'menu_name'          => _x( 'Recruits', 'admin menu', 'mje_recruit' ),
			'name_admin_bar'     => _x( 'Recruit', 'add new on admin bar', 'mje_recruit' ),
			'add_new'            => _x( 'Add New', 'Recruit', 'mje_recruit' ),
			'add_new_item'       => __( 'Add New Recruitment', 'mje_recruit' ),
			'new_item'           => __( 'New recruitment', 'mje_recruit' ),
			'edit_item'          => __( 'Edit', 'mje_recruit' ),
			'view_item'          => __( 'View recruitment', 'mje_recruit' ),
			'all_items'          => __( 'All recruitments', 'mje_recruit' ),
			'search_items'       => __( 'Search recruitments', 'mje_recruit' ),
			'parent_item_colon'  => __( 'Parent recruitment', 'mje_recruit' ),
			'not_found'          => __( 'No Recruitment post found.', 'mje_recruit' ),
			'not_found_in_trash' => __( 'No recruitment found in the trashh.', 'mje_recruit' )
		);

		$args = array(
			'labels'             => $labels,
	                'description'        => __( 'Description.', 'mje_recruit' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => MJOB_RECRUIT ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' )
		);

		register_post_type( $this->post_type, $args );

        $labels = array(
            'name'               => _x( 'Offers', 'post type general name', 'mje_recruit' ),
            'singular_name'      => _x( 'Offer', 'post type singular name', 'mje_recruit' ),
            'menu_name'          => _x( 'Offers', 'admin menu', 'mje_recruit' ),
            'name_admin_bar'     => _x( 'Offer', 'add new on admin bar', 'mje_recruit' ),
            'add_new'            => _x( 'Add New', 'Offer', 'mje_recruit' ),
            'add_new_item'       => __( 'Add New Offer', 'mje_recruit' ),
            'new_item'           => __( 'New Offer', 'mje_recruit' ),
            'edit_item'          => __( 'Edit Offer', 'mje_recruit' ),
            'view_item'          => __( 'View Offer', 'mje_recruit' ),
            'all_items'          => __( 'All Offers', 'mje_recruit' ),
            'search_items'       => __( 'Search Offers', 'mje_recruit' ),
            'parent_item_colon'  => __( 'Parent Offer', 'mje_recruit' ),
            'not_found'          => __( 'No Offers found.', 'mje_recruit' ),
            'not_found_in_trash' => __( 'No Offers found in Trash.', 'mje_recruit' )
        );

        $args = array(
            'labels'             => $labels,
                    'description'        => __( 'Description.', 'mje_recruit' ),
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'offer' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' )
        );

        register_post_type( 'offer', $args );

        register_taxonomy_for_object_type('mjob_category', MJOB_RECRUIT);


		 // global $ae_post_factory;
        //$ae_post_factory->set( $this->post_type, new MJOB_Request( $this->post_type, $this->taxs, $this->meta ) );
	}
	public function insert($args) {
        global $current_user, $user_ID;

        // strip tags
        foreach ($args as $key => $value) {
            if ((in_array($key, $this->meta) || in_array($key, $this->convert)) && is_string($args[$key]) && $key != 'post_content') {
                $args[$key] = strip_tags($args[$key]);
            }
        }

        // pre filter filter post args
        //$args = apply_filters('ae_pre_insert_' . $this->post_type, $args);
        if (is_wp_error($args)) return $args;

        $args = wp_parse_args($args, array(
            'post_type' => $this->post_type
        ));

        // Only use for mjob_post
        if( $this->post_type == MJE_REQUEST ) {
            /*if admin disable plan set status to pending or publish*/
            $pending = ae_get_option('use_pending', false);
            $pending = apply_filters( 'use_pending', $pending, $this->post_type );
            $disable_plan = true;

            /*if admin disable plan set status to pending or publish*/
            if ($disable_plan) {
                // Change Status Publish places that posted by Admin
                if(is_super_admin()){
                    // Publish post
                    $args['post_status'] = 'publish';
                } else {
                    // disable plan
                    if ($pending) {
                        // pending post
                        $args['post_status'] = 'pending';
                    } else {
                        // disable pending post
                        $args['post_status'] = 'publish';
                    }
                }
            }
        }

        if (!isset($args['post_status'])) {
            $args['post_status'] = 'draft';
        }

        // could not create with an ID
        if (isset($args['ID'])) {
            return new WP_Error('invalid_data', __("The ID already existed!", 'mje_recruit'));
        }

        if (!isset($args['post_author']) || empty($args['post_author'])) $args['post_author'] = $current_user->ID;

        if ( empty( $args['post_author'] ) ) return new WP_Error('missing_author', __('You must login to submit listing.', 'mje_recruit'));

        //custom code
        $pending_option=ae_get_option('mje_my_recruit_pending_option', false);
        if($pending_option)
        {
            $args['post_status']='pending';
        }

        //

        // filter tax_input
        $args = $this->_filter_tax_input($args);
        if(isset($args['post_content'])){
            // filter post content strip invalid tag
            $args['post_content'] = $this->filter_content($args['post_content']);
        }
        /**
         * insert post by wordpress function
         */
        $result = wp_insert_post($args, true);

        /**
         * update custom field and tax
         */
        if ($result != false && !is_wp_error($result)) {
            $this->update_custom_field($result, $args);
            $args['ID'] = $result;
            $args['id'] = $result;

            /**
             * do action ae_insert_{$this->post_type}
             * @param Int $result Inserted post ID
             * @param Array $args The array of post data
             */
            do_action('ae_insert_' . $this->post_type, $result, $args);

            $result = (object)$args;


            //do_action('ae_insert_post', $result);

            // localize text for js
            if (!empty($this->localize)) {
                foreach ($this->localize as $key => $localize) {
                    $a = array();
                    foreach ($localize['data'] as $loc) {
                        array_push($a, $result->$loc);
                    }

                    $result->$key = vsprintf($localize['text'], $a);
                }
            }


            $result->permalink = get_permalink($result->ID);
            $result->redirect_url = $result->permalink;

            if (current_user_can('manage_options') || $result->post_author == $user_ID) {

                /**
                 * featured image not null and should be in carousels array data
                 */
                if (isset($args['featured_image'])) {
                    set_post_thumbnail($result->ID, $args['featured_image']);
                }
            }
        }

       // $result =  apply_filters('ae_convert_after_insert_' . $this->post_type, $result);
        return $result;
    }

}
new MJE_Request();


?>