<?php
add_action('wp_head','pass_bad_words_toJS',999);

function pass_bad_words_toJS()
{
    $bad_words = (ae_get_option('filter_bad_words'))?ae_get_option('filter_bad_words'):'';
    ?>
      <script type="text/javascript">
            let custom_bad_words='<?php echo $bad_words; ?>';
           
        </script>
    <?php
}