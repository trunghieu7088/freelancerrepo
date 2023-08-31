<?php
function custom_popup_settings(){
    add_menu_page( 'Delete Language', 'Delete Language', 'manage_options', 'delete-language-profiles', 'delete_language_init','dashicons-schedule',10 );

}

add_action('admin_menu', 'custom_popup_settings');

function delete_language_init()
{
	

	$terms = get_terms( array( 
    'taxonomy' => 'language',
     'number'=>5000,
    'hide_empty' => false
) );
	//var_dump($terms);
	foreach($terms as $term)
	{
		//echo $term->name;
		//echo '<br>';
		wp_delete_term($term->term_id,'language');
	} 


/*
for($i=1;$i<=10000;$i++)
	{
			wp_insert_term(
			'lang'.$i,   // the term 
	'language', // the taxonomy
	array(
		'description' => 'lang description '.$i,
		'slug'        => 'lang'.$i,
		
	));
	}
	*/

}