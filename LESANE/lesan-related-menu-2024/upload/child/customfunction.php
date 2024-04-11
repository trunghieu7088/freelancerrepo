<?php
function categoryID_meta_box()
{
	if(get_post_type()=='page')
	{
		add_meta_box( 'thong-tin', 'Category Skill', 'categoryID_options', 'page');		
	}
 
}
add_action( 'add_meta_boxes', 'categoryID_meta_box' );

function categoryID_options($post)
{

	$categoryID=get_post_meta($post->ID,'categoryID',true);
	//echo '<input type="text" id="categoryID" name="categoryID" value="'.$categoryID.'" placeholder="Category ID">';
	$terms = get_terms('skill', array('hide_empty' => 0));
	echo '<select id="categoryID" name="categoryID">';
	if($terms)
	{	
      foreach ($terms as $key => $value)
      {
      	if($categoryID == $value->term_id)
      	{
      		echo '<option selected value="'.$value->term_id.'">';
      		
      	}
      	else
      	{
      		echo '<option value="'.$value->term_id.'">';
      	}
      	
      	echo $value->name;
      	echo '</option>';
         //echo '<li><a class="fre-skill-item" name="'.$value->slug.'" href="">'.$value->name.'</a></li>';
      }
                                
	}
	echo '</select>';
}


add_action('edit_post_page','update_categoryID',10,4);

function update_categoryID($post_id, $post)
{
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	update_post_meta($post_id,'categoryID',$_POST['categoryID']);
}

add_action('save_post_page','add_categoryID',10,4);

function add_categoryID($post_id,$post)
{
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	update_post_meta($post_id,'categoryID',$_POST['categoryID']);
}



//custom code for elementor new block here

add_shortcode('fre_custom_button_see_all','fre_customize_button');

function fre_customize_button($atts)
{
	
	global $post;		
		$category_skill=get_post_meta($post->ID,'categoryID',true);
		if(get_post_type()=='page' && $category_skill)
		{
			$skill=get_term($category_skill);
			$custom_link=site_url('/profiles/?catskill='.$skill->slug);
		}
		else
		{
			$custom_link=get_post_type_archive_link( PROFILE );
		}
		$custom_link=str_replace(array('http://','https://'),'', $custom_link);
		return $custom_link;
}
//end

//custom code 4th April 2024

require('custom_related_skills.php');
//end