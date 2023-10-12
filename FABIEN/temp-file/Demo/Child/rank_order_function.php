<?php 
session_start();
function custom_add_rank_order_column($columns) {
    $columns['custom_rank_order'] = 'Rank';
    $columns['custom_rank_point']='point';
    return $columns;
}
add_filter('manage_users_columns', 'custom_add_rank_order_column');


// show data of rank order column
function custom_show_rank_order_column_content($value, $column_name, $user_id) {  
$rank_order_show=get_user_meta($user_id,'custom_rank_order',true) ? get_user_meta($user_id,'custom_rank_order',true) : 0;
  if ( 'custom_rank_order' == $column_name )
    //return $user_id;
    return $rank_order_show;
    return $value;
}
add_filter('manage_users_custom_column',  'custom_show_rank_order_column_content', 10, 3);

//show data of custom rank point column
function custom_show_rank_point_column_content($value, $column_name, $user_id) {  
$rank_order_point=get_user_meta($user_id,'custom_rank_point',true) ? get_user_meta($user_id,'custom_rank_point',true) : 0;
  if ( 'custom_rank_point' == $column_name )
    //return $user_id;
    return $rank_order_point;
    return $value;
}
add_filter('manage_users_custom_column',  'custom_show_rank_point_column_content', 10, 3);

// add sort option by rank order and by rank point
function make_rank_order_sortable_column( $columns ) {
    $columns['custom_rank_order'] = 'Rank';
    $columns['custom_rank_point']='point';
    return $columns;
}
add_filter( 'manage_users_sortable_columns', 'make_rank_order_sortable_column' );

// make sort function by rank order work.

function sort_by_custom_rank($query)
{
    if( ! is_admin() )  
        return;  

    $orderby = $query->get( 'orderby');  

    if( 'Rank' == $orderby ) {  
        $query->set('meta_key','custom_rank_order');
        //$query->set('meta_type','number');
        $query->set('orderby','meta_value_num');  
    }  
}

add_action('pre_get_users', 'sort_by_custom_rank');




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

$rank_order=get_user_meta($current_user_id, 'custom_rank_order',true);
$rank_point = get_user_meta($current_user_id, 'custom_rank_point',true);
$rank_time = get_user_meta($current_user_id, 'custom_rank_update_time',true);

?>
<h3>Rank Order</h3>
<table>
<tr>

<td>

<input placeholder="Rank order" disabled="disabled" id="custom_rank_order" name="custom_rank_order" type="text" value="<?php echo $rank_order; ?>">
</td>
</tr>

</table>

<h3>Rank Point</h3>

<table>
<tr>

<td>

<input placeholder="Rank point" id="custom_rank_point" name="custom_rank_point" type="text" value="<?php echo $rank_point; ?>">
</td>
</tr>

</table>

<h3>Rank Time</h3>
<table>
<tr>

<td>

<input placeholder="Rank time" id="custom_rank_update_time" name="custom_rank_update_time" type="text" value="<?php echo $rank_time; ?>">
</td>
</tr>


</table>



<?php }

add_action( 'personal_options_update', 'save_custom_rank_point_field' );
add_action( 'edit_user_profile_update', 'save_custom_rank_point_field' );

function save_custom_rank_point_field( $user_id )
{
if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }else{

$profile_id_edit=get_user_meta($user_id,'user_profile_id',true);

if(isset($_POST['custom_rank_point']) && $_POST['custom_rank_point'] != ""){
update_user_meta( $user_id, 'custom_rank_point', $_POST['custom_rank_point'] );

update_post_meta($profile_id_edit,'custom_rank_point', $_POST['custom_rank_point'] );
}

if(isset($_POST['custom_rank_update_time']) && $_POST['custom_rank_update_time'] != ""){
update_user_meta( $user_id, 'custom_rank_update_time', $_POST['custom_rank_update_time'] );
update_post_meta($profile_id_edit,'custom_rank_update_time', $_POST['custom_rank_update_time'] );
}

}
}

//add option update rank order & meta cho old users vao admin dashboard 
add_action('admin_menu', 'update_order_meta_old_users_setup_menu');
 
function update_order_meta_old_users_setup_menu(){
    add_menu_page( 'Update Rank order for old users Page', 'Update Rank point for old users', 'manage_options', 'updateMetaOldUsers', 'update_meta_option_init' );
}

//add option update rank order manually

add_action('admin_menu', 'update_rank_order_manually_setup_menu');
 
function update_rank_order_manually_setup_menu(){
    add_menu_page( 'Update Rank order manually page', 'Update Rank order manually', 'manage_options', 'updaterankOrderAllUsers', 'update_rank_order_manually' );
}


function update_meta_option_init()
{
  
    ?>
    <h3>Add rank point for old users ( based on the number of notifications, total projects worked, rating score )</h3>
    <form action="<?php echo esc_url( admin_url('admin-post.php')); ?>" method="POST" name="update-meta-old-users-form">
    <input type="hidden" name="action" value="update_meta_old_user_action">  
    <input type="submit" value="UPDATE" name="update-meta-old-users">
    </form>
   <?php 

      if(isset($_SESSION['create_function']))
    {       
        echo ' <div class="notice notice-success is-dismissible">
        <p>Update rank point for old users successfully</p>
        </div>';
    }

    unset($_SESSION['create_function']);  
}

function update_rank_order_manually()
{
    ?>  
      <h3>Update rank order manually for all users</h3>
    <form action="<?php echo esc_url( admin_url('admin-post.php')); ?>" method="POST" name="update-rank-order-manually-form">
    <input type="hidden" name="action" value="update_rank_order_manully_action">  
    <input type="submit" value="UPDATE RANK ORDER MANUALLY" name="update-rank-order-manully">
    </form>
    <?php

       if(isset($_SESSION['update_rank_order_flash_message']))
    {       
        echo ' <div class="notice notice-success is-dismissible">
        <p>Update rank order successfully</p>
        </div>';
    }

    unset($_SESSION['update_rank_order_flash_message']);  
}

//

// make sort function by rank point work.

function sort_by_custom_rank_point($query)
{
    if( ! is_admin() )  
        return;  

    $orderby = $query->get( 'orderby');  

    if( 'point' == $orderby ) {  
        $query->set('meta_key','custom_rank_point');
        //$query->set('meta_type','number');
        $query->set('orderby','meta_value_num');  
    }  
}

add_action('pre_get_users', 'sort_by_custom_rank_point');

function update_rank_order_manully()
{
    if(isset($_POST['update-rank-order-manully']))
    { 
            $args = array();
           $default = array(    
            'role' => 'freelancer',
              'number' =>-1,
              'meta_query' => array(
                'relation' => 'AND',
                'custom_rank_point_clause' => array(
                    'key'     => 'custom_rank_point',
                    'compare' => 'EXISTS',
                    'type' => 'NUMERIC',
                ),
                'custom_rank_update_time_clause' => array(
                    'key'     => 'custom_rank_update_time',
                    'compare' => 'EXISTS',
                    'type' => 'DATETIME',
                ), 
            ),
             'orderby' =>array('custom_rank_point_clause' => 'DESC',
                    'custom_rank_update_time_clause' => 'ASC',            
                  ),


           );
          $args = wp_parse_args( $args, $default );
          $user_list_query = new WP_User_Query($args);     
          $users_stuff=get_users($args);
          $rank_order=1;
          foreach($users_stuff as $user_item) 
          {   
            update_user_meta($user_item->ID,'custom_rank_order',$rank_order);
            update_user_meta($user_item->ID,'co_update','newnew');
            $profile_id_freelancer=get_user_meta($user_item->ID,'user_profile_id',true);

            update_post_meta($profile_id_freelancer,'custom_rank_order',$rank_order);
            update_post_meta($profile_id_freelancer,'co_update','newnew');
            $rank_order+=1;
            }
        $_SESSION["update_rank_order_flash_message"] = 'Update rank order successfully';
    }
   wp_redirect('admin.php?page=updaterankOrderAllUsers');
}
add_action( 'admin_post_update_rank_order_manully_action', 'update_rank_order_manully' );


function update_rank_order_old_users()
{

      if(isset($_POST['update-meta-old-users']))
      { 
           
             $args = array(
             'role' => array( 'freelancer'),
            'number' =>-1,
            //'meta_key'=>'custom_rank_point',
            //'orderby' => 'meta_value_num',
            //'order' => 'DESC',
            'meta_query' => array(
                'relation' => 'AND',
                array(                    
                'key'     => 'custom_rank_order',                
                'compare' => 'NOT EXISTS',
                'value' =>''
                ),
                 'relation' => 'AND',
                 array(
                  'key'     => 'user_profile_id',                
                'compare' => 'EXISTS',                  
                 )

            ),
            );
     $users_stuff=get_users($args);
        update_post_meta(2324,'co_user_ko',count($users_stuff));
            $set_rank_order=9999;
           foreach($users_stuff as $user_item)    
          {
              
            $profile_id=get_user_meta($user_item->ID,'user_profile_id',true);
            $total_project_completed=get_post_meta($profile_id,'total_projects_worked',true);
            $total_project_completed_point=(int)$total_project_completed*100;
            $rating_score=get_post_meta($profile_id,'rating_score',true);
            $rating_score_point=(int)$rating_score*50;

                $all_noti_query=array('post_type'=>'notify',
                                                'post_status'=>'publish',
                                                'author'=>$user_item->ID,
                                                'numberposts'=>-1
                                            );
                $all_noti=get_posts($all_noti_query);
                $all_noti_count=count($all_noti);
                $all_noti_count_point= (int)$all_noti_count*20;
                $set_rank_point=$total_project_completed_point + $rating_score_point + $all_noti_count_point;
              $datetime_rank_update = date('Y-m-d H:i:s'); 
              update_user_meta($user_item->ID,'custom_rank_order',$set_rank_order);
              update_user_meta($user_item->ID,'custom_rank_point',$set_rank_point);
              update_user_meta($user_item->ID,'custom_rank_update_time',$datetime_rank_update);
             
              update_post_meta($profile_id,'custom_rank_order',$set_rank_order);
              update_post_meta($profile_id,'custom_rank_point',$set_rank_point);
              update_post_meta($profile_id,'custom_rank_update_time',$datetime_rank_update);

          }    
          $_SESSION["create_function"] = 'Update successfully';
      }
      wp_redirect('admin.php?page=updateMetaOldUsers');
}
add_action( 'admin_post_update_meta_old_user_action', 'update_rank_order_old_users' );


function set_rank_order_base_on_rank_point()
{
  $args = array();
   $default = array(    
    'role' => 'freelancer',
      'number' =>-1,
      'meta_query' => array(
        'relation' => 'AND',
        'custom_rank_point_clause' => array(
            'key'     => 'custom_rank_point',
            'compare' => 'EXISTS',
            'type' => 'NUMERIC',
        ),
        'custom_rank_update_time_clause' => array(
            'key'     => 'custom_rank_update_time',
            'compare' => 'EXISTS',
            'type' => 'DATETIME',
        ), 
    ),
     'orderby' =>array('custom_rank_point_clause' => 'DESC',
            'custom_rank_update_time_clause' => 'ASC',            
          ),


   );
  $args = wp_parse_args( $args, $default );
  $user_list_query = new WP_User_Query($args);     
  $users_stuff=get_users($args);
  $rank_order=1;
  foreach($users_stuff as $user_item) 
  {   
    update_user_meta($user_item->ID,'custom_rank_order',$rank_order);
    update_user_meta($user_item->ID,'co_update','newnew');
    $profile_id_freelancer=get_user_meta($user_item->ID,'user_profile_id',true);

    update_post_meta($profile_id_freelancer,'custom_rank_order',$rank_order);
    update_post_meta($profile_id_freelancer,'co_update','newnew');
    $rank_order+=1;
  }
  $total_freelancer_has_rank=count($users_stuff);
//  update_option('total_freelancer_has_rank',$total_freelancer_has_rank);
  do_action('set_rank_order_base_on_rank_point');


}

add_action('set_rank_init_cron', 'set_rank_order_base_on_rank_point');

function set_rank_init_cron_register()
{
   if (!wp_next_scheduled('set_rank_init_cron')) {
        wp_schedule_event(time(), 'nam_seconds', 'set_rank_init_cron');
    }

}

add_action('wp', 'set_rank_init_cron_register');


function test_cron( $schedules ) { 
    $schedules['nam_seconds'] = array(
        'interval' => 120,
        'display'  => 'Five Seconds',
    );
    return $schedules;
}

add_filter( 'cron_schedules', 'test_cron',999);



function add_test_user()
{
  $datetime_rank_update = date('Y-m-d H:i:s'); 
  $rank_point_test=10;
  for($a=1001;$a<=9000;$a++)
  {
    $result=wp_create_user('test'.$a,'123456', 'test'.$a.'@gmail.com' );
    
       $user = new WP_User( $result );
        $user->set_role( 'freelancer' );    
    update_user_meta($result,'custom_rank_order',0);
    update_user_meta($result,'custom_rank_point',$rank_point_test);
    update_user_meta($result,'custom_rank_update_time',$datetime_rank_update);
    

        $my_post = array(
      'post_title'    => $user->display_name,
      'post_content'  => 'day la description '.$user->display_name,
      'post_status'   => 'publish',
      'post_author'   => $result,
      'post_type' =>'fre_profile'
    );

    // Insert the post into the database.
     $profile_id=wp_insert_post( $my_post );
      update_post_meta($profile_id,'custom_rank_order',0);
      update_post_meta($profile_id,'custom_rank_point',0);
      update_post_meta($profile_id,'custom_rank_update_time',$datetime_rank_update);
    $rank_point_test+=10;
  }
}

//add_action('init','add_test_user');


function count_freelancer_updated()
{
  $args = array();
   $default = array(    
    'role' => 'freelancer',
      'number' =>-1,
    //  'meta_key' =>'co_update',
      'meta_query' => array(
        'relation' => 'AND',
        array(
            'key'     => 'co_update',
            'value'=>'newnew',
            'compare' => '=', 
        ),
       
    ),

   );
  $args = wp_parse_args( $args, $default );
  $user_list_query = new WP_User_Query($args);     
  $users_stuff=get_users($args);
  echo count($users_stuff);
 // var_dump($users_stuff);



}

//add_action('init','count_freelancer_updated');




function get_the_lowest_rank( $string ) {
         $query_args = array(
        'post_type' => 'fre_profile' ,
        'post_status' => 'publish' ,
        'posts_per_page' => 4,  
        'meta_query' =>  array(
        'relation' => 'AND',
           'custom_rank_order_clause' =>  array(
            'key'   => 'custom_rank_order',
              'compare' => 'EXISTS',
              'type' => 'NUMERIC',
             )
         ),
        'orderby'  => array(
          'custom_rank_order_clause' => 'DESC',
          
        ),
      ) ;
      $lowest_rank = get_posts($query_args);        
      return get_post_meta($lowest_rank[0]->ID,'custom_rank_order',true);
}
add_filter( 'get_the_lowest_rank_filter', 'get_the_lowest_rank', 10, 3 );


