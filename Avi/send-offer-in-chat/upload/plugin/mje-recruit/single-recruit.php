<?php
do_action("before_single_place");
get_header();
global $ae_post_factory, $post, $mjob_post;



$mjob_object = $ae_post_factory->get( 'mjob_post' );
$mjob_post = $mjob_object->convert( $post );

$is_edit = false;
if( isset($_GET['action']) && $_GET['action'] = 'edit'){
    $is_edit = MJE_MJob_Action::checkEdit( $mjob_post );
}
$mjob_post->is_edit = $is_edit;

//custom code chat offer

global $user_ID;
$user_id = $mjob_post->post_author;

if($user_id == $user_ID) {
    $seller_id = get_post_meta($post->ID, 'seller_id', true);
    if(!empty($seller_id)) {
        $user_id = $seller_id;
    }
}
//end
?>
    <div id="content" class="mjob-single-page">
        <?php get_template_part('template/content', 'page');?>
        <div class="container mjob-single-primary">
            <div class="row">
                <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
                    <div class="mjob-single-content box-shadow">
                         <div class="mjob-single-header mjob-single-block pad-lr-30">
                                <h2>
                                    <span class="rendered-text">
                                        <?php echo $mjob_post->post_title; ?>
                                    </span>

                                </h2>
                                <div class="mjob-single-meta clearfix">
                                    <span class="mjob-cat-breadcrumb">
                                        <?php
                                        $cats = $mjob_post->tax_input['mjob_category'];
                                        $breadcrumb = '';
                                        if (!empty($cats)) {
                                            $parent = $cats['0']->parent;
                                            $breadcrumb = '<span class="mjob-breadcrumb"><a class="parent" href="' . get_term_link($cats["0"]) . '">' . $cats["0"]->name . '</a></span>';
                                            if ($parent != 0) {
                                                $parent = get_term_by('ID', $parent, 'mjob_category');
                                                if($parent)
                                                $breadcrumb = '<span class="mjob-breadcrumb"><a class="parent" href="' . get_term_link($parent) . '">' . $parent->name . '</a> <i class="fa fa-angle-right"></i> <span><a class="child" href="' . get_term_link($cats["0"]) . '">' . $cats['0']->name . '</a></span></span>';
                                            }
                                        }
                                        echo $breadcrumb;
                                        ?>
                                    </span>
                                    <span class="time-post pull-right">
                                        <?php _e('Last modified: ', 'mje_recruit'); ?>
                                        <span><?php echo $mjob_post->modified_date; ?></span>
                                    </span>
                                </div>

                        </div><!-- end .mjob-single-header -->



                        <div class="mjob-single-share mjob-single-block">
                            <?php mje_get_template( 'template/mjob-post/share.php', array( 'mjob_post' => $mjob_post ) ) ?>
                        </div><!-- end .mjob-single-share -->

                        <div class="mjob-single-description mjob-single-block pad-lr-30">



                            <h3 class="title"><?php _e( 'Description', 'mje_recruit' ) ;?></h3>
                            <div class="post-detail description">
                                <div class="blog-content">
                                    <div class="post-content">
                                        <?php echo $mjob_post->post_content; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix">
                                <div class="tags">
                                    <?php mje_list_tax_of_mjob( $mjob_post->ID, '', 'skill' ) ?>
                                </div>
                               <!--  <?php if( $user_ID != $mjob_post->post_author ): ?>
                                    <?php mje_render_order_button( $mjob_post ); ?>
                                <?php endif; ?> -->
                            </div>

                             <!-- send offer custom code -->                          
                            <div class="clearfix custom-contact-btn">
                                <div class="personal-profile">
                                    <div class="link-personal">
                                        <ul style="text-align:left !important;">                                    
                                            <?php mje_show_contact_link($user_id); ?>                                        
                                        </ul>
                                    </div>
                               </div>
                            </div>
                             <!-- end send offer custom code -->

                        </div><!-- end .mjob-single-description -->
                    </div><!-- end .mjob-single-content -->

                    <?php // mje_submit_offer_form($mjob_post); ?>
                    <?php //mje_show_list_offer($mjob_post);?>

                    <div class="mjob-edit-content">
                        <?php get_template_part('template-js/template', 'edit-mjob');?>
                    </div>
                    <!-- offer block !-->


                    <!-- offer form block  end !-->
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                    <div class="mjob-single-aside">
                        <?php require_once('templates/aside-block.php'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- end .mjob-single-page -->
<?php
echo '<input type="hidden" name="amount" value="' . $mjob_post->et_budget . '">';
echo '<script type="text/template" id="mjob_single_data" >'.json_encode( $mjob_post ).'</script>';

get_template_part( 'template/modal-send-custom-order' );
get_footer();