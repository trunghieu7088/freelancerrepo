<?php
/*
Template Name: Custom Manage Subscription Template
*/
if(!is_super_admin())
{
    wp_redirect(site_url());
    die();
}
?>
<?php 
get_header();
?>
<div class="subscription-manage-wrapper">
     <div class="container subscription-content-wrapper">
         <!-- admin menu subscription -->
        <div class="col-md-3 col-sm-12 subscription-menu-wrapper">
           <?php echo do_shortcode('[subscription_menu_admin]'); ?>
        </div>
         <!-- end admin menu subscription -->

         <!-- admin task section -->
         <div class="col-md-9 col-sm-12 subscription-task-area">
            <?php 
            if(isset($_GET['task']))
            {
               echo do_shortcode('['.$_GET['task'].']');
            }
            else
            {
               echo do_shortcode('[paypalinfo]');
            }
            
            ?>

         </div>
          <!--end admin task section -->

     </div>
</div>
<?php get_footer(); ?>