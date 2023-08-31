<?php
function fre_setup_recruit_pack_type() {
    register_post_type( 'recruit_pack', array(
		'labels'             => array(
			'name'               => __( 'Recruit Package', ET_DOMAIN ),
			'singular_name'      => __( 'Recruit Package', ET_DOMAIN ),
			'add_new'            => __( 'Add New', ET_DOMAIN ),
			'add_new_item'       => __( 'Add New Bid plan', ET_DOMAIN ),
			'edit_item'          => __( 'Edit Bid plan', ET_DOMAIN ),
			'new_item'           => __( 'New Bid plan', ET_DOMAIN ),
			'all_items'          => __( 'All   Recruit Package', ET_DOMAIN ),
			'view_item'          => __( 'View Recruit Package', ET_DOMAIN ),
			'search_items'       => __( 'Search Recruit Package', ET_DOMAIN ),
			'not_found'          => __( 'No Recruit Package found', ET_DOMAIN ),
			'not_found_in_trash' => __( 'No Recruit Package found in Trash', ET_DOMAIN ),
			'parent_item_colon'  => '',
			'menu_name'          => __( 'Recruit Packages', ET_DOMAIN )
		),
		'public'             => false,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => true,

		'capability_type' => 'post',
		// 'capabilities' => array(
		//     'manage_options'
		// ) ,
		'has_archive'     => 'packs',
		'hierarchical'    => false,
		'menu_position'   => null,
		'supports'        => array(
			'title',
			'editor',
			'author',
			'custom-fields'
		)
	) );

    $meta_fields = apply_filters('recruit_pack',array(
		'sku',
		'et_price',
		'et_number_posts',
		'order',
		'et_featured',
		'et_duration',
	));
	$package = new AE_Pack( 'recruit_pack', $meta_fields,
		array(
			'backend_text' => array(
				'text' => __( '%s for %d bids', ET_DOMAIN ),
				'data' => array(
					'et_price',
					'et_number_posts',

				)
			)
		) );
	global $ae_post_factory;
	$ae_post_factory->set( 'recruit_pack', $package );
}
add_action( 'init', 'fre_setup_recruit_pack_type' );
