<?php
/**
 * Template Name: Page Non Logged Nofity
 */
//fix bug 12th Nov 2023
//fix bug sau khi login thanh cong thi bi login ve lai trang nay --> sai --> dung la phai login ve trang home
if(is_user_logged_in())
{
    wp_redirect(site_url());
}
//end
global $current_user, $user_ID;
get_header();
?>
<script type="text/javascript">
    (function ($) {
    $(document).ready(function () {
        setTimeout(function()
        {  
            $(".open-signin-modal").trigger('click');
        }, 2000);
    })
    })(jQuery);
</script>
<div id="content" style="min-height:500px !important;display:flex;align-items:center;justify-content:center;">    
        <h2>Please login to view the content</h2>            
</div>
<?php
get_footer();
?>
