<?php
    global $user_ID;
?>
<h3 class="title"><?php _e( 'Description', 'enginethemes' ) ;?></h3>
<div class="post-detail description">
    <div class="blog-content">
        <div class="post-content mjob_post_content">
            <?php the_content(); ?>
        </div>
    </div>
</div>
<!-- custom code 6th may 2024 -->
<?php 
$attached_video=get_attached_media('video',$mjob_post->ID);
$video_url='';
if($attached_video)
{
    foreach($attached_video as $video_item)
    {
        $video_url=wp_get_attachment_url($video_item->ID);  
        $video_mime_type=$video_item->post_mime_type;     
    }
}
?>
<?php if($video_url): ?>
<button data-toggle="collapse" class="btn btn-info" data-target="#custom-video-service-container"><i class="fa fa-play"></i> Show Video</button>
<div id="custom-video-service-container" class="custom-video-player-service collapse">
<video disablepictureinpicture  class="customized-video-player" id="video-player-single-service" playsinline controls>
  <source src="<?php echo $video_url; ?>" type="<?php echo $video_mime_type;  ?>" />    
</video>
</div>
<?php endif; ?>
<!-- end custom code 6th may 2024 -->
<div class="clearfix">
    <div class="tags">
        <?php mje_list_tax_of_mjob( $mjob_post->ID, '', 'skill' ) ?>
    </div>
    <?php if( $user_ID != $mjob_post->post_author ): ?>
        <?php mje_render_order_button( $mjob_post ); ?>
    <?php endif; ?>
</div>
<?php do_action('mjob_post_after_description', $mjob_post);?>

<script type="text/javascript">
(function ($) {

    $(document).ready(function () {    
            const video_player=new Plyr("#video-player-single-service",{
                controls: ['play-large','progress','current-time','mute','volume','fullscreen'],                
            });
     });     

})(jQuery);

</script>