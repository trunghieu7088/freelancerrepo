<?php
/**
 * Template Name: Page Non Logged Nofity
 */
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
