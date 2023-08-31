<?php
add_action('wp_enqueue_scripts', 'override_frontjs');
function override_frontjs()
{    
    wp_deregister_script('site-front');
    wp_enqueue_script('site-front', get_stylesheet_directory_uri() . '/js/front.js', array(
            'jquery',
            'underscore',
            'backbone',
            'appengine',
            'site-functions',
          ), ET_VERSION, true);

}


/*
add_action('wp_enqueue_scripts', 'override_mobile_frontjs');
function override_mobile_frontjs()
{    
    wp_deregister_script('mobile-front');
    wp_enqueue_script('mobile-front', get_stylesheet_directory_uri() . '/mobile/js/front.js', array(
            'jquery',
            'underscore',
            'backbone',
            'appengine',
            'site-functions',
          ), ET_VERSION, true);  

}
*/


add_action( 'show_user_profile', 'my_extra_user_fields' );
add_action( 'edit_user_profile', 'my_extra_user_fields' );


function my_extra_user_fields()
{

if(!empty($_REQUEST['user_id']))
{

$current_user_id=$_REQUEST['user_id'];
}
else
{

$current_user_id=get_current_user_id();
}

$university=get_user_meta($current_user_id, 'university',true);
$job_role = get_user_meta($current_user_id, 'job_role',true);
$level_education = get_user_meta($current_user_id, 'level_education',true);

?>
<h3>Univeristy</h3>
<table>
<tr>

<td>

<input placeholder="Univeristy" disabled="disabled" id="university" name="university" type="text" value="<?php echo $university; ?>">
</td>
</tr>

</table>

<h3>Level of Education</h3>

<table>
<tr>

<td>

<input placeholder="Level of education" disabled="disabled" id="level_education" name="level_education" type="text" value="<?php echo $level_education; ?>">
</td>
</tr>

</table>

<h3>Registering as</h3>
<table>
<tr>

<td>

<input placeholder="Registering as" disabled="disabled" id="job_role" name="job_role" type="text" value="<?php echo $job_role; ?>">
</td>
</tr>


</table>

<?php }