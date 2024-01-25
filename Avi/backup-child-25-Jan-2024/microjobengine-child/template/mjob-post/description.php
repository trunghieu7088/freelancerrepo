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
<div class="clearfix">
    <div class="tags">
        <?php mje_list_tax_of_mjob( $mjob_post->ID, '', 'skill' ) ?>
    </div>
    <?php if( $user_ID != $mjob_post->post_author ): ?>
        <?php mje_render_order_button( $mjob_post ); ?>
    <?php endif; ?>
</div>
<?php 
//custom code tiktok
$tiktok_link=get_post_meta($mjob_post->ID,'tiktok_video_link',true);
$tiktok_video_id=get_post_meta($mjob_post->ID,'tiktok_video_id',true);
//end custom code tiktok
?>
<?php if($tiktok_link) : ?>
<div class="clearfix tiktokIframe">
<blockquote class="tiktok-embed" cite="<?php echo $tiktok_link; ?>" data-video-id="<?php echo $tiktok_video_id; ?>" style="max-width: 605px;min-width: 325px;" > 
	<section> 
	</section> 
</blockquote> 
</div>
<script async src="https://www.tiktok.com/embed.js"></script>
<?php endif; ?>
<?php do_action('mjob_post_after_description', $mjob_post);?>
