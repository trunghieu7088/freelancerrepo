<?php

function je_header_menu()

{

	global $current_user, $user_ID;

	$sel = '';

	// check if which page the user is in

	if (is_page_template('page-companies.php') || is_author()) {

		$sel = 'company';

	} elseif (is_page_template('page-dashboard.php') || is_page_template('page-profile.php') || is_page_template('page-password.php')) {

		$sel = 'dashboard';

	} else if (is_post_type_archive('job') || get_post_type() == 'job' || is_home()) {

		$sel = 'job';

		et_delete_user_new_feeds($user_ID);

	}



	$job_selected		=	'';

	$company_selected	=	'';

	if ($sel == 'job')			$job_selected		=	'current-menu-item';

	if ($sel == 'company') 	$company_selected	=	'current-menu-item';



	$notice = '';

	$new_feeds	=	count(et_get_user_new_feeds($current_user->ID));

	if ($new_feeds > 0) {

		$notice	=	'<dd><a href="#">' . $new_feeds . '</a></dd>';

	}





	$default_menu_items = apply_filters('default_menu_items', array(

		array(

			'id' 				=> 'home-menu',

			'href' 				=> get_post_type_archive_link('job'),

			'checking_callback'	=> 'et_is_job_menu',

			'label' 			=> __('JOBS', ET_DOMAIN),

			'link_attr' 		=> array('title' => __('Jobs', ET_DOMAIN)),

		), array(

			'id' 				=> 'company-menu',

			'href' 				=> et_get_page_link('companies'),

			'checking_callback'	=> 'et_is_company_menu',

			'label' 			=> __('COMPANIES', ET_DOMAIN),

			'link_attr' 		=> array('title' => __('Companies', ET_DOMAIN)),

		)

	));



	// prepare default menu items html

	$default_mi_html = '';

	foreach ($default_menu_items as $item) {

		if (

			!isset($item['id']) || !isset($item['href']) || !isset($item['checking_callback']) ||

			!isset($item['label']) || !isset($item['link_attr'])

		)

			continue;



		// build link attributes

		$link_attrs = '';

		foreach ((array)$item['link_attr'] as $key => $value) {

			$link_attrs .= " $key='$value' ";

		}



		// build link html

		$a = "<a href='{$item['href']}' {$link_attrs} >{$item['label']}</a>";



		// current

		$current = call_user_func_array($item['checking_callback'], array());



		$subitem	=	'';

		if (isset($item['sub'])) {

			foreach ($item['sub']  as $sub) {

				$sublink_attrs = '';

				foreach ((array)$sub['link_attr'] as $key => $value) {

					$sublink_attrs .= " $key='$value' ";

				}



				// build link html

				$suba = "<a href='{$sub['href']}' {$sublink_attrs} >{$sub['label']}</a>";

				$subitem .= "<li id='{$sub['id']}' >{$suba} </li>";

			}

		}

		$subitem = '<ul class="sub-menu">' . $subitem . '</ul>';



		// add to default menu html

		if ($current)

			$default_mi_html .= "<li class='current-menu-item' id='{$item['id']}'>{$a}{$subitem}</li>";

		else

			$default_mi_html .= "<li id='{$item['id']}'>{$a}{$subitem} </li>";

	}

	$menu	=	wp_nav_menu(array(

		'items_wrap'	=>	'<nav><ul class="menu-header-top" id="nav">' . $default_mi_html . '%3$s</ul></nav>',

		'theme_location' => 'et_top',

		'echo'	=> false

	));

	if (has_nav_menu('et_top') && $menu != '') {

		// echo $menu;

	} else {

		// echo "<nav><ul class='menu-header-top' id='nav'>$default_mi_html</ul></nav>";

	}

}
