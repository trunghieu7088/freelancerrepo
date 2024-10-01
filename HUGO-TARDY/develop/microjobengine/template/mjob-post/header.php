<h2>
    <span class="rendered-text">
        <?php echo $mjob_post->post_title; ?>
    </span>
    <?php
    if (MJE_MJob_Action::checkEdit($mjob_post)) :
        if ($mjob_post->post_status == 'draft') {
            $edit_term = __('RESUME POSTING', 'enginethemes');
            $edit_inline = "";
        } elseif ($mjob_post->post_status == 'archive') {
            $edit_term = __('RENEW', 'enginethemes');
            $edit_inline = "";
        } else {
            $edit_term = __('EDIT', 'enginethemes');
            $edit_inline = "edit-inline";
        }
    ?>
        <a href="<?php echo et_get_page_link('post-service') . '?id=' . $mjob_post->ID; ?>" class="edit-mjob-action <?php echo $edit_inline; ?>">
            <i class="fa fa-pencil"></i>
            <span><?php echo $edit_term; ?></span>
        </a>
    <?php
    endif;
    ?>
</h2>
<?php do_action('after_mjob_detail_title', $mjob_post); ?>
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
                if ($parent)
                    $breadcrumb = '<span class="mjob-breadcrumb"><a class="parent" href="' . get_term_link($parent) . '">' . $parent->name . '</a> <i class="fa fa-angle-right"></i> <span><a class="child" href="' . get_term_link($cats["0"]) . '">' . $cats['0']->name . '</a></span></span>';
            }
        }
        echo $breadcrumb;
        ?>
    </span>
    <span class="time-post pull-right">
        <?php _e('Last modified: ', 'enginethemes'); ?>
        <span><?php echo $mjob_post->modified_date; ?></span>
    </span>
</div>