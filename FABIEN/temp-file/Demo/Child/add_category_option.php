<?php 

function custom_add_form_fields($taxonomy)
{
     ?>
      <label><?php _e('Project category image', 'enginethemes') ?></label>
        <div>
            <img id="ae-tax-images-photo" class="custom_media_image" src="" style="width:150px;height: 150px;display:none;"/>
            <input  class="custom_media_id" type="hidden" name="custom_media_id" id="custom_media_id" value="" />

        </div>
        <p>
               <input type="button" class="button button-secondary ct_tax_media_button" id="ct_tax_media_button" name="ct_tax_media_button" value="<?php _e( 'Add Image', 'hero-theme' ); ?>" />
               <input type="button" class="button button-secondary " id="remove_img" name="remove_img" value="remove image" style="display: none;"/>
           </p>

           <?php
}
add_action('project_category_add_form_fields', 'custom_add_form_fields',10,2);

function custom_edit_form_fields($term,$taxonomy)
{

    if(isset($_GET['tag_ID']))
    {
        $term_id=$_GET['tag_ID'];
        $attachment_id=get_term_meta($term_id,'category_image_id',true);
        if($attachment_id)
        {
            $args_img=array('name'=>'custom_media_image_edit','id'=>'custom_media_image_edit');
            $img_category=wp_get_attachment_image($attachment_id,'thumbnail',false, $args_img);
        }
      
    }

    ?>
    <tr class="form-field">
        <th scope="row">Project category image</th>
        <td>
        <?php  
            if(!empty($img_category))
            {
                echo $img_category;
                $style_remove_btn='style="display:inline"';
            } 
            else
            {
                echo '<img scr="" width="150" height="150" class="attachment-thumbnail size-thumbnail" loading="lazy" decoding="async" name="custom_media_image_edit" id="custom_media_image_edit" src="" style="display:none">';
                 $style_remove_btn='style="display:none"';
            }
        ?>
           <br>
            <input  class="custom_media_id_edit" type="hidden" name="custom_media_id_edit" id="custom_media_id_edit" value="<?php if($attachment_id) echo $attachment_id; ?>" />
               <input type="button" class="button button-secondary " id="add_img_edit" name="add_img_edit" value="Add image"/>
               <input type="button" class="button button-secondary " id="remove_img_edit" name="remove_img_edit" value="Remove image" <?php echo $style_remove_btn; ?>/>
        </td>
    </tr>
    


    <?php

}

add_action('project_category_edit_form_fields','custom_edit_form_fields',10,2);

function custom_enqueue_scripts()
{
     wp_enqueue_media();
}
add_action('admin_enqueue_scripts','custom_enqueue_scripts');

function custom_script_for_upload()
{
    ?>
    <script>
        (function ($) {
                 $("#remove_img_edit").click(function(){
                $('#custom_media_image_edit').attr('src','').css('display','none');
                 $('#custom_media_id_edit').val('');
                 $("#remove_img_edit").css('display','none');
            });

            $("#remove_img").click(function(){
                $('.custom_media_image').attr('src','').css('display','none');
                 $('.custom_media_id').val('');
                 $("#remove_img").css('display','none');
            });

$('#add_img_edit').click(function(e) {
    e.preventDefault();

    var custom_uploader_edit = wp.media({
        title: 'Upload image for the category',
        button: {
            text: 'Set as image'
        },
        library:  { type: 'image' },
        multiple: false  // Set this to true to allow multiple files to be selected
    })
    .on('select', function() {
        var attachment = custom_uploader_edit.state().get('selection').first().toJSON();
        $('#custom_media_image_edit').attr('src', attachment.url).css('display','inline');       
        $('#custom_media_id_edit').val(attachment.id);
        $('#remove_img_edit').css('display','inline');
        console.log(attachment.url);
    })
    .open();
});

$('.ct_tax_media_button').click(function(e) {
    e.preventDefault();

    var custom_uploader = wp.media({
        title: 'Upload image for the category',
        button: {
            text: 'Set as image'
        },
        library:  { type: 'image' },
        multiple: false  // Set this to true to allow multiple files to be selected
    })
    .on('select', function() {
        var attachment = custom_uploader.state().get('selection').first().toJSON();
        $('.custom_media_image').attr('src', attachment.url).css('display','inline');
       // $('.custom_media_url').val(attachment.url);
        $('.custom_media_id').val(attachment.id);
        $('#remove_img').css('display','inline');
        
    })
    .open();
});
})(jQuery);
</script>
<?php
}
add_action('admin_footer','custom_script_for_upload');


function save_custom_image($term_id, $tt_id)
{
    if(isset($_POST['custom_media_id']) && $_POST['custom_media_id']!='')
    {
           $image = $_POST['custom_media_id'];
     add_term_meta( $term_id, 'category_image_id', $image, true );
    }
  
}
add_action('created_project_category','save_custom_image',10,2);

function edit_custom_image($term_id, $tt_id)
{
    if(isset($_POST['custom_media_id_edit']) && $_POST['custom_media_id_edit']!='')
    {
           $image = $_POST['custom_media_id_edit'];
        update_term_meta( $term_id, 'category_image_id', $image);
    }
    else
    {
        delete_term_meta( $term_id, 'category_image_id','');

    }
}
add_action('edited_project_category','edit_custom_image',10,2);

function return_parent_categories_function()
{
    $args=array('taxonomy'=>'project_category','parent'=>0,'hide_empty' => false);
    $project_parent_categories=get_terms($args);
    return $project_parent_categories;
}

add_filter('return_parent_categories','return_parent_categories_function',10,0);

function test_my_code2()
{
    $a=apply_filters('return_parent_categories','test');
    echo count($a);
}

//add_action('init','test_my_code2');

add_action( 'wp_ajax_get_sub_categories', 'get_sub_categories_init' );


function get_sub_categories_init()
{
    $result[]=array();
    $parent_id=$_POST['parent_category_id'];
    $flag_parent=true;
    foreach($parent_id as $parent_item)
    {
        $parent_category_info=get_term_by('id',$parent_item,'project_category');
      
        $result[]='<optgroup label="'.$parent_category_info->name.'">';
       
        $child_item=get_term_children($parent_item,'project_category');
        foreach($child_item as $item)
        {
            $term = get_term_by( 'id', $item, 'project_category' );
       
           $result[]= '<option value="'.$term->term_id.'">'.$term->name.'</option>';
        }
        $result[]= '</optgroup>';

    }
    //$child_item=get_term_children(,'project_category');
    
   
   
    wp_send_json_success($result);
 
    die();
}

