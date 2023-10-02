<?php
use Carbon_Fields\Container;
use Carbon_Fields\Field;

add_action( 'after_setup_theme', 'crb_load',999,0 );
function crb_load() {    
    require __DIR__ . '/carbonvendor/autoload.php';
    \Carbon_Fields\Carbon_Fields::boot();
}

add_action( 'carbon_fields_register_fields', 'carousel_profile_settings',999,0 );

function carousel_profile_settings() {
    

    Container::make( 'theme_options', __( 'Carousel Profile Settings', 'crb' ) )
    ->set_icon( 'dashicons-admin-generic')
    ->set_page_menu_title( 'Carousel Profile Settings' )
    ->set_page_menu_position(4)
    ->add_tab( __( 'Settings' ), array(
        Field::make( 'color', 'slider_item_bg_color', 'Slider Item background color' )->set_default_value('#F7F7F7'),        
        
        Field::make( 'color', 'profile_title_text_color', 'Profile name color ( John Doe )' )->set_default_value('#337ab7'),        
        
        Field::make( 'color', 'profession_title_text_color', 'Profession color ( Seo Expert) ' )->set_default_value('#ffffff')->set_width( 50 ),        
        Field::make( 'color', 'profession_title_bg_color', 'Profession background color ( Seo Expert) ' )->set_default_value('#10a2ef')->set_width( 50 ),        

        Field::make( 'color', 'profile_description_text_color', 'Description color' )->set_default_value('#000000'),        
        
        Field::make( 'color', 'view_profile_button_text_color', 'View Profile button text color' )->set_default_value('#10a2ef')->set_width( 30 ),
        Field::make( 'color', 'view_profile_button_border_color', 'View Profile button border color' )->set_default_value('#10a2ef')->set_width( 30 ),                                
        Field::make( 'color', 'view_profile_button_bg_color', 'View Profile button background color' )->set_width( 30 ),
        
        Field::make( 'color', 'view_profile_button_text_color_hover', 'View Profile button text color when hover' )->set_default_value('#ffffff')->set_width( 30 ),                                
        Field::make( 'color', 'view_profile_button_border_color_hover', 'View Profile button border color when hover' )->set_default_value('#10a2ef')->set_width( 30 ),                                
        Field::make( 'color', 'view_profile_button_bg_color_hover', 'View Profile button background color when hover' )->set_default_value('#10a2ef')->set_width( 30 ),
        
        Field::make( 'text', 'carousel_profile_title', __( 'Carousel Profile Title' ) )->set_default_value('Carousel Profile'),
        Field::make( 'text', 'number_profiles_carousel', __( 'Number profiles' ) )->set_default_value(30),      

    ));


  
}

function carousel_profile_list_settings_init()
{
    add_menu_page( 'Carousel Profile List', 'Carousel Profile List', 'manage_options', 'carousel-profile-list', 'carousel_profile_callback','dashicons-admin-generic',3 );
}

add_action('admin_menu', 'carousel_profile_list_settings_init');

function carousel_profile_callback()
{
    $number_of_profiles=(carbon_get_theme_option('number_profiles_carousel')) ? carbon_get_theme_option('number_profiles_carousel') : 30 ;
   // $profile_list=get_all_mjob_profile();

    $position_list=get_option('carousel_list_profile');
    
    if(isset($position_list) && !empty($position_list))
    {
       $position_list_array=explode(' ',$position_list);       
    }
    if(isset($position_list_array))
    {
        $position_list_count=count($position_list_array);
    }
   
    $profile_position=1;
    ?>
    <div class="wrap">
        <h1>Carousel Profile Lists</h1>
        <h4>Please set the profiles which will be displayed in the carousel profile</h4>
        <form style="margin-top:10px;" id="profileListForm">
            <?php if(!$position_list) : ?>
            <?php for($profile_position=1; $profile_position<= $number_of_profiles; $profile_position++) : ?>
            <div class="options" style="width:60%;margin-top:20px;">
                <p><?php echo '#'.$profile_position; ?></p>						
				<select id="<?php echo 'profilelist'.$profile_position ?>" name="<?php echo 'profilelist'.$profile_position ?>" style="width:100%">
                    <?php echo get_all_mjob_profile(); ?>
                </select>
                
			</div>
            <?php endfor; ?> 
            <?php endif; ?>

            <?php if($position_list_array) : ?>
            <?php foreach($position_list_array as $position_item) : ?>
            <div class="options" style="width:60%;margin-top:20px;">
                <p><?php echo '#'.$profile_position; ?></p>						
				<select id="<?php echo 'profilelist'.$profile_position ?>" name="<?php echo 'profilelist'.$profile_position ?>" style="width:100%">
                <?php echo get_all_mjob_profile($position_item); ?>
                </select>
               
			</div>
            <?php $profile_position+=1; ?>
            <?php endforeach; ?> 
            <?php endif; ?>

            <?php if($position_list_array && $position_list_count && $position_list_count < $number_of_profiles) : ?>
                <?php for($profile_position; $profile_position<= $number_of_profiles; $profile_position++) : ?>
            <div class="options" style="width:60%;margin-top:20px;">
                <p><?php echo '#'.$profile_position; ?></p>						
				<select id="<?php echo 'profilelist'.$profile_position ?>" name="<?php echo 'profilelist'.$profile_position ?>" style="width:100%">
                    <?php echo get_all_mjob_profile(); ?>
                </select>
                
			</div>
            <?php endfor; ?> 
            <?php endif; ?>

            <input style="margin-top:30px;" type="submit" class="button button-secondary " id="save_list_profile_btn" name="save_list_profile_btn" value="Save"/>
        </form>     
    </div>

    <script type="text/javascript">
    (function($) {
        $(document).ready(function()
        {            
            var selectedValues = [];
            var number_profile = <?php echo $number_of_profiles; ?>;
                     
            
            $("#profileListForm").submit(function(event){
                event.preventDefault();
                for (var i = 1; i <= number_profile; i++) 
                {
                    var selectId = 'profilelist' + i;
                    selectedValues.push($('#' + selectId).val());                
                }

                $.ajax({
                    type: "post",
                    url: ae_globals.ajaxURL,
                    dataType: 'json',
                    data: 
                    {  
                        selected_profiles: selectedValues,
                        action: 'save_profile_list',
                    },
                    beforeSend: function() {
                        //console.log(selectedValues);
                    },
                    success: function (response) 
                    {                                
                        alert(response.data);
                    }

                }); 

            });                        

        });

    })(jQuery);
    </script>

                
    <?php
}

add_action( 'wp_ajax_save_profile_list', 'save_profile_list_init' );

function save_profile_list_init()
{
    $selected_values = array();
    $number_of_profiles=(carbon_get_theme_option('number_profiles_carousel')) ? carbon_get_theme_option('number_profiles_carousel') : 30 ;
    $selected_profiles=$_POST['selected_profiles'];
    /* foreach($selected_profiles as $profile)
    {
        update_option('carousel_list_profile', $profile);
    } */
    $converted_list=implode(" ",$selected_profiles);
    update_option('carousel_list_profile', $converted_list);
    wp_send_json_success('Values saved successfully');
    die();
}

function get_all_mjob_profile($profile_id=0)
{
    $mjob_profile_args = array(
        'post_type' => 'mjob_profile',      
        'numberposts' => -1,      
        'post_status' => 'publish'
    );
    $all_mjob_profiles=get_posts($mjob_profile_args);
    $mjob_profile_lists='';
    

    if($all_mjob_profiles)
    {
        foreach($all_mjob_profiles as $mjob_profile)
        {
            $user_profile=get_userdata($mjob_profile->post_author);
            if($profile_id==$mjob_profile->ID)
            {
                $mjob_profile_lists.= '<option selected value="'.$mjob_profile->ID.'">';
            }
            else
            {
                $mjob_profile_lists.= '<option value="'.$mjob_profile->ID.'">';
            }
            
            $mjob_profile_lists.=  $user_profile->display_name.' - '.$user_profile->user_email;
            $mjob_profile_lists.= '</option>';
        }
    }
    return  $mjob_profile_lists;
}

add_shortcode('custom_carousel_slider', 'custom_carousel_slider_init');

function custom_carousel_slider_init()
{
    ob_start();
    $carousel_profile_title=(carbon_get_theme_option('carousel_profile_title')) ? carbon_get_theme_option('carousel_profile_title') : 'Carousel Profile' ;
    $carousel_profile_number=(carbon_get_theme_option('number_profiles_carousel')) ? carbon_get_theme_option('number_profiles_carousel') : 30 ;
    
    $slider_item_bg_color=(carbon_get_theme_option('slider_item_bg_color')) ? carbon_get_theme_option('slider_item_bg_color') : '' ;
    $profile_title_text_color=(carbon_get_theme_option('profile_title_text_color')) ? carbon_get_theme_option('profile_title_text_color') : '' ;
    
    $profession_title_text_color=(carbon_get_theme_option('profession_title_text_color')) ? carbon_get_theme_option('profession_title_text_color') : '' ;
    $profession_title_bg_color=(carbon_get_theme_option('profession_title_bg_color')) ? carbon_get_theme_option('profession_title_bg_color') : '' ;
   
    $profile_description_text_color=(carbon_get_theme_option('profile_description_text_color')) ? carbon_get_theme_option('profile_description_text_color') : '' ;
    
    $view_profile_button_text_color=(carbon_get_theme_option('view_profile_button_text_color')) ? carbon_get_theme_option('view_profile_button_text_color') : '' ;
    $view_profile_button_border_color=(carbon_get_theme_option('view_profile_button_border_color')) ? carbon_get_theme_option('view_profile_button_border_color') : '' ;
    $view_profile_button_bg_color=(carbon_get_theme_option('view_profile_button_bg_color')) ? carbon_get_theme_option('view_profile_button_bg_color') : '' ;
    
    $view_profile_button_text_color_hover=(carbon_get_theme_option('view_profile_button_text_color_hover')) ? carbon_get_theme_option('view_profile_button_text_color_hover') : '' ;
    $view_profile_button_border_color_hover=(carbon_get_theme_option('view_profile_button_border_color_hover')) ? carbon_get_theme_option('view_profile_button_border_color_hover') : '' ;
    $view_profile_button_bg_color_hover=(carbon_get_theme_option('view_profile_button_bg_color_hover')) ? carbon_get_theme_option('view_profile_button_bg_color_hover') : '' ;

   
    ?>
    <style>
        .carousel-profile .carousel-view-profile:hover,
        .carousel-view-profile[style]:hover
        {
            <?php 
                if( $view_profile_button_text_color_hover) echo 'color:'.$view_profile_button_text_color_hover.' !important;';  
                if( $view_profile_button_bg_color_hover) echo 'background-color:'.$view_profile_button_bg_color_hover.' !important;';  
                if( $view_profile_button_border_color_hover) echo 'border:1px solid '.$view_profile_button_border_color_hover.' !important;';  
            ?>        
        }
    </style>
    <div class="block-items carousel-profile">
        <div class="container">
            <p class="block-title float-center" style="display:none !important;"><?php echo $carousel_profile_title; ?></p>      
                <div class="row swiper carouselswiper" style="position:relative;margin-left:auto !important;margin-right:auto !important;  -ms-overflow-style: none;scrollbar-width: none;">
                   <div class="swiper-wrapper">
                       
                        
                       <?php 
                             $carousel_profile_list=get_option('carousel_list_profile');
                             if(isset($carousel_profile_list) && !empty($carousel_profile_list))
                             {
                                $carousel_profile_array=explode(' ',$carousel_profile_list);
                             }
                        ?>
                        
                        <?php  
                        if(isset($carousel_profile_array) && !empty($carousel_profile_array)) :
                            foreach($carousel_profile_array as $profile_item): ?>
                                <?php 
                                    $mjob_profile=get_post($profile_item);
                                    $profile_info=get_userdata($mjob_profile->post_author);
                                    $profession=get_post_meta($profile_item,'profession',true) ? get_post_meta($profile_item,'profession',true) : 'none';
                                    $description=get_post_meta($profile_item,'profile_description',true) ? get_post_meta($profile_item,'profile_description',true) : 'none';
                                    if($description != 'none')
                                    {
                                        $description=wp_trim_words($description,8,'..');
                                    }
                                    $profile_link=get_author_posts_url($profile_info->ID);  
                                   
                                    $seller = AE_Users::get_instance();
                                    $avatar = $seller->get_avatar($profile_info->ID, 160,'');                                                               
                                ?>
                                <div class="col-md-4 col-sm-6 col-xs-12 swiper-slide">
                                    <div class="carousel-item" style="<?php if($slider_item_bg_color) echo 'background-color:'.$slider_item_bg_color; ?>">
                                        
                                        <div class="col-md-6 col-sm-6 col-xs-12 carousel-left">
                                            <a href="<?php echo $profile_link; ?>"><img class="img-responsive carousel-profile-avatar" src="<?php echo $avatar; ?>"></a>                           
                                        </div>

                                        <div class="col-md-6 col-sm-6 col-xs-12 carousel-right">
                                            <a href="<?php echo $profile_link; ?>">
                                                <h1 class="carousel-profile-title" style="<?php if($profile_title_text_color) echo 'color:'.$profile_title_text_color.';'; ?>"><?php echo $profile_info->display_name; ?></h1>
                                            </a>
                                            <p class="carousel-profile-profession"><span style="<?php if($profession_title_text_color) echo 'color:'.$profession_title_text_color.';'; if($profession_title_bg_color) echo 'background-color:'.$profession_title_bg_color.';'; ?>"><i class="fa fa-briefcase"></i> <?php echo $profession; ?></span></p>
                                            <p class="carousel-description" style="<?php if($profile_description_text_color) echo 'color:'.$profile_description_text_color.';' ?>"><?php echo $description; ?></p>                            
                                            <a class="carousel-view-profile" style="<?php if($view_profile_button_text_color) echo 'color:'.$view_profile_button_text_color.';'; if($view_profile_button_bg_color) echo 'background-color:'.$view_profile_button_bg_color.';';
                                             if($view_profile_button_border_color) echo 'border:1px solid '.$view_profile_button_border_color.';'; ?>" href="<?php echo $profile_link; ?>">View Profile <i class="fa fa-user"></i></a>
                                        </div>

                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>       
                     
                   </div>      
                   
                   

                </div>   
                
           <!-- next and previous button of slider -->
       <!--    <img src="<?php echo get_stylesheet_directory_uri().'/assets/img/next-icon-blue.png';  ?>" class="profile-next-icon-css" name="profile_nextslidep" id="profile_nextslidep" >

            <img src="<?php echo get_stylesheet_directory_uri().'/assets/img/previous-icon-blue.png';  ?>" class="profile-previous-icon-css" name="profile_prevslidep" id="profile_prevslidep" >   -->              

            <!-- end  next and previous button of slider -->
                                    
		</div>
	</div>
    <?php
     wp_reset_query();
     return ob_get_clean();
}

