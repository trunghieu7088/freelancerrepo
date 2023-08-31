<?php
// Add order number of kindwork to the taxonomy term edit page
function add_order_number_to_kindwork_add_page($term) {
    // Check if the taxonomy is 'kindwork' and the post type is 'mjob_post'
    if ($term == 'kindwork' && isset($_GET['post_type']) && $_GET['post_type'] === 'mjob_post') {        
        ?>
        <tr class="form-field">
            <th scope="row" valign="top">
                <label for="OrderKindwork">Order Number</label>
            </th>
            <td>
                <input type="number" name="OrderKindwork" id="OrderKindwork" value="0" required>
                <p class="description">Enter a value for the Order.</p>
            </td>
        </tr>
        <?php
    }
}
add_action('kindwork_add_form_fields', 'add_order_number_to_kindwork_add_page');

function add_order_number_to_kindwork_edit_page($term,$taxonomy) {
    // Check if the taxonomy is 'kindwork' and the post type is 'mjob_post'
    if(isset($_GET['tag_ID']))
    {
        $term_id=$_GET['tag_ID'];
        $OrderKindwork=get_term_meta($term_id,'Orderkindwork',true);        
        if(!$OrderKindwork)
        $OrderKindwork=0;
    }

        ?>
        <tr class="form-field">
            <th scope="row" valign="top">
                <label for="OrderKindwork">Order Number</label>
            </th>
            <td>
                <input required="required" type="number" name="OrderKindwork" id="OrderKindwork" value="<?php echo $OrderKindwork; ?>">
                <p class="description">Enter a value for the Order.</p>
            </td>
        </tr>
        <?php
    
}
add_action('kindwork_edit_form_fields', 'add_order_number_to_kindwork_edit_page',10,2);

// Save order of kindwork  when the term is edited
function save_orderNumber_kindwork($term_id) {
    // Check if the taxonomy is 'kindwork' and the post type is 'mjob_post'
    if (isset($_POST['taxonomy']) && $_POST['taxonomy'] == 'kindwork' && isset($_POST['post_type']) && $_POST['post_type'] === 'mjob_post') 
    {
        
        if (isset($_POST['OrderKindwork'])) {
            $OrderKindworkvalue = sanitize_text_field($_POST['OrderKindwork']);
            update_term_meta($term_id, 'Orderkindwork', $OrderKindworkvalue);
        }      
    }
}
add_action('created_kindwork', 'save_orderNumber_kindwork');


function save_orderNumber_kindwork_edit($term_id,$tt_id) {
    // Check if the taxonomy is 'kindwork' and the post type is 'mjob_post'
   
        if (isset($_POST['OrderKindwork'])) 
        {         
            $OrderKindworkvalue = sanitize_text_field($_POST['OrderKindwork']);   
            update_term_meta($term_id, 'Orderkindwork', $OrderKindworkvalue);
        }         
}
add_action('edited_kindwork', 'save_orderNumber_kindwork_edit',10,2);

function remove_parent_dropdown_field($args, $taxonomy) {
    // Check if the taxonomy is 'kindwork' and the post type is 'mjob_post'
    $args['parent'] = 0; // Set the parent to 0 to remove the dropdown field
    return $args;
}
add_filter('kindwork_parent_dropdown_args', 'remove_parent_dropdown_field', 10, 2);




function add_kindwork_column_to_term_list($columns) {
    $columns['Orderkindwork'] = 'Order';
    return $columns;
}
add_filter('manage_edit-kindwork_columns', 'add_kindwork_column_to_term_list');

// Populate custom column with meta values
function populate_kindwork_column_with_meta_values($content, $column_name, $term_id) {
    if ($column_name === 'Orderkindwork') {
        $meta_value = get_term_meta($term_id, 'Orderkindwork', true) ? get_term_meta($term_id, 'Orderkindwork', true) : 0;
        $content = $meta_value;
    }
    return $content;
}
add_filter('manage_kindwork_custom_column', 'populate_kindwork_column_with_meta_values', 10, 3);

function make_kindwork_column_sortable($sortable_columns) {
    $sortable_columns['Orderkindwork'] = 'Order';
    return $sortable_columns;
}
add_filter('manage_edit-kindwork_sortable_columns', 'make_kindwork_column_sortable');

add_action( 'parse_term_query', 'order_column_orderby' );
function order_column_orderby( WP_Term_Query $query ) {
    // Check whether we are at wp-admin/edit-tags.php?taxonomy=weight-class
    if ( ! is_admin()) 
    {
        return;
    }

    $taxonomies = (array) $query->query_vars['taxonomy'];

    // Modify the args, if `weight` is the `orderby` value, and that the query
    // is for your custom `weight-class` taxonomy.
    if ( 'Order' === $query->query_vars['orderby']) 
    {
        $query->query_vars['meta_key'] = 'Orderkindwork';
        $query->query_vars['orderby']  = 'meta_value_num';
    }
}
