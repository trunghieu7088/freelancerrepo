<?php
add_action('wp_enqueue_scripts', 'add_custom_css_social_bar');
function add_custom_css_social_bar()
{                
     wp_enqueue_style('all-custom-css', get_stylesheet_directory_uri().'/assets/css/custom-css.css');
}


function add_social_bar()
{    
    ?>
     <div class="custom-social-bar">
        <div class="social-icons-container">
            <a href="https://www.facebook.com/profile.php?id=100083169046788">
                <img src="<?php echo get_stylesheet_directory_uri().'/assets/img/facebook.png'; ?>">
            </a>

            <a href="https://www.pinterest.de/meetyourwriter/">
                <img src="<?php echo get_stylesheet_directory_uri().'/assets/img/pinterest.png'; ?>">
            </a>
            
            <a href="https://www.instagram.com/meet.your.writer/">
                <img src="<?php echo get_stylesheet_directory_uri().'/assets/img/instagram.png'; ?>">
            </a>
        </div>
        
    </div>
    <?php
}
add_action('wp_footer','add_social_bar');