<?php

//add_action('after_header_menu','custom_add_related_menu_item_by_skills');

function custom_add_related_menu_item_by_skills()
{
    $custom_current_user=wp_get_current_user();
    if(in_array('freelancer',$custom_current_user->roles))
    {
        $is_show_menu=true;
    }
    else
    {
        $is_show_menu=false;
    }
    $user_profile_id=get_user_meta($custom_current_user->ID,'user_profile_id',true);
    if( $user_profile_id)
    {
        $skills=wp_get_post_terms($user_profile_id,'skill');
    }
    else
    {
        $skills=false;
    }
   
    ?>
    <?php if($is_show_menu): ?>
        <ul class="fre-menu-main">
        <li class="fre-menu-page dropdown">
            <a>My Related Skills<i class="fa fa-caret-down" aria-hidden="true"></i></a>
            <ul class="dropdown-menu">           
                    <?php if($skills): ?>
                        <?php foreach($skills as $skill): ?>
                            <li class="menu-item">
                                <a href="<?php echo site_url('/projects/?catskill=').$skill->slug; ?>">
                                    <?php echo $skill->name; ?>
                                </a>
                            </li>
                        <?php endforeach;  ?>
                    <?php endif; ?>                            
            </ul>
        </li>
    </ul>
    <?php endif; ?>  
    <?php
}

add_action('wp_footer','add_custom_relate_skills_menu_by_js',999);

function add_custom_relate_skills_menu_by_js()
{
    $custom_current_user=wp_get_current_user();
    if(in_array('freelancer',$custom_current_user->roles))
    {
        $is_show_menu=true;
    }
    else
    {
        $is_show_menu=false;
    }
    $user_profile_id=get_user_meta($custom_current_user->ID,'user_profile_id',true);
    if( $user_profile_id)
    {
        $skills=wp_get_post_terms($user_profile_id,'skill');
    }
    else
    {
        $skills=false;
    }

       
        if($skills && $is_show_menu)
        {

            $skill_list='';
             $skill_list.=' <ul class="fre-menu-main">';
             $skill_list.='<li class="fre-menu-page dropdown">';
                     

        $skill_list.='<a class="custom-menu-related-job-links">Related Jobs<i class="fa fa-caret-down" aria-hidden="true"></i></a>';
        $skill_list.='<ul class="dropdown-menu">';          
            foreach($skills as $skill)
            {
                $skill_list.='<li class="menu-item custom-related-item">';
                $skill_list.='<a href="'.site_url('/projects/?catskill=').$skill->slug.'">'.$skill->name.'</a>';
                $skill_list.='</li>';
            }

             $skill_list.='</ul>';
     
            $skill_list.='</li>';
        $skill_list.='</ul>';
        }
       
   
    ?>
    <script type="text/javascript">
        (function ($) {
             $(document).ready(function () {
                    let menu_item_content='<?php echo $skill_list; ?>';
                   
                    $(".fre-menu-top").append(menu_item_content);
             });
         })(jQuery);
    </script>
    <?php
    
}

