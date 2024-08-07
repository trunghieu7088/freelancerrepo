<?php
class AE_Taxonomy_Meta extends AE_Base{
    public static $instance;
    public $tax;
    /**
     * get_instance method
     *
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    /**
     * the constructor of this class
     *
     */
    public  function __construct( $tax = 'category' ){
        $this->add_action($tax.'_add_form_fields', 'ae_add_form_fields');
        $this->add_action( 'created_'.$tax, 'ae_save_tax_meta', 10, 2 );
        $this->add_action( $tax .'_edit_form_fields', 'ae_edit_tax_group_field', 10, 2 );
        $this->add_action( 'edited_'.$tax, 'ae_update_tax_meta', 10, 2 );
        $this->add_filter('manage_edit-'.$tax.'_columns', 'ae_add_tax_column' );
        $this->add_filter('manage_'.$tax.'_custom_column', 'ae_add_tax_column_content', 10, 3 );
        $this->add_action( 'admin_enqueue_scripts', 'ae_tax_enqueue_scripts'  );
    }
    /**
     * Description
     *
     * @param void
     * @return void
     * @since 1.0
     * @package MicrojobEngine
     * @category void
     * @author JACK BUI
     */
    public function ae_add_form_fields($taxonomy) {
        global $featured_tax;
        $term_id = 0;
        // Remove image URL
        $remove_url = add_query_arg( array(
            'action'   => 'remove-wp-term-images',
            'term_id'  => $term_id,
            '_wpnonce' => false,
        ) );
        // Get the meta value
        $value = get_term_meta($term_id, 'mjob_category_image', true);
        $hidden = empty( $value )
            ? ' style="display: none;"'
            : ''; ?>
        <div class="form-field term-group">
        <label><?php _e('Taxonomy image', 'enginethemes') ?></label>
        <div>
            <img id="ae-tax-images-photo" src="<?php echo esc_url( wp_get_attachment_image_url( $value, 'full' ) ); ?>"<?php echo $hidden; ?> />
            <input type="hidden" name="<?php echo $taxonomy; ?>_image" id="<?php echo $taxonomy; ?>_image" value="<?php echo esc_attr( $value ); ?>" />
        </div>

        <a class="button-secondary ae-tax-images-media">
            <?php esc_html_e( 'Choose Image', 'enginethemes' ); ?>
        </a>

        <a href="<?php echo esc_url( $remove_url ); ?>" class="button ae-tax-images-remove"<?php echo $hidden; ?>>
            <?php esc_html_e( 'Remove', 'wp-user-avatars' ); ?>
        </a>
        <div class="clearfix"></div>
            <br/>
        <div class="featured-tax">
            <input type="checkbox" name="featured-tax" class="left margin-20 margin-top-3" value="true" />
            <label for="featured-tax" class="left"><?php _e('Featured category', 'enginethemes'); ?></label>
        </div>
        </div>
        <div class="clearfix"></div>
        <br/>
        <?php
    }
    /**
     * save tax meta
     *
     * @param integer $term_id
     * @param integer $tt_id
     * @return void
     * @since 1.0
     * @package MicrojobEngine
     * @category void
     * @author JACK BUI
     */
    public function ae_save_tax_meta( $term_id, $tt_id ){
        if( isset( $_POST['featured-tax'] ) && '' !== $_POST['featured-tax'] ){
            $group = sanitize_title( $_POST['featured-tax'] );
            add_term_meta( $term_id, 'featured-tax', $group, true );
        }
        if( isset( $_POST['mjob_category_image'] ) && '' !== $_POST['mjob_category_image'] ){
            $group = sanitize_title( $_POST['mjob_category_image'] );
            update_term_meta( $term_id, 'mjob_category_image', $group );
        }
    }
    /**
     * edit form tax
     *
     * @param object $term
     * @param string $taxonomy
     * @return void
     * @since 1.0
     * @package MicrojobEngine
     * @category void
     * @author JACK BUI
     */
    function ae_edit_tax_group_field( $term, $taxonomy ){
        global $featured_tax;
        // get current group
        $check = '';
        $featured_tax = get_term_meta( $term->term_id, 'featured-tax', true );
        if( $featured_tax ){
            $check = 'checked';
        }
        $remove_url = add_query_arg( array(
            'action'   => 'remove-ae-tax-images',
            'term_id'  => $term->term_id,
            '_wpnonce' => false,
        ) );
        $value = get_term_meta($term->term_id, 'mjob_category_image', true);
        $hidden = empty( $value )
            ? ' style="display: none;"'
            : ''; ?>
        <tr class="form-field term-group-wrap">
        <th scope="row"><label for="featured-tax"><?php _e( 'Featured', 'enginethemes' ); ?></label></th>
        <td><input type="checkbox" name="featured-tax" value="true" <?php echo $check; ?>/> <label for="featured-tax"><?php _e('Featured taxonomy', 'enginethemes'); ?></label></td>
        </tr>
        <tr>
            <th scope="row"><label for="tax-image"><?php _e( 'Thumbnail', 'enginethemes' ); ?></label></th>
            <td>
                <div>
                    <img id="ae-tax-images-photo" src="<?php echo esc_url( wp_get_attachment_image_url( $value, 'thumbnail' ) ); ?>"<?php echo $hidden; ?> />
                    <input type="hidden" name="<?php echo $taxonomy; ?>_image" id="<?php echo $taxonomy; ?>_image" value="<?php echo esc_attr( $value ); ?>" />
                </div>

                <a class="button-secondary ae-tax-images-media">
                    <?php esc_html_e( 'Choose Image', 'enginethemes' ); ?>
                </a>

                <a href="<?php echo esc_url( $remove_url ); ?>" class="button ae-tax-images-remove"<?php echo $hidden; ?>>
                    <?php esc_html_e( 'Remove', 'wp-user-avatars' ); ?>
                </a>
            </td>
        </tr>
        <?php
    }
    /**
     * save edit
     *
     * @param integer $term_id
     * @param integer $tt_id
     * @return void
     * @since 1.0
     * @package MicrojobEngine
     * @category void
     * @author JACK BUI
     */
    public function ae_update_tax_meta( $term_id, $tt_id ){
        if( isset( $_POST['featured-tax'] ) && '' !== $_POST['featured-tax'] ){
            $group = sanitize_title( $_POST['featured-tax'] );
            update_term_meta( $term_id, 'featured-tax', $group );
        }
        else{
            update_term_meta($term_id, 'featured-tax', false);
        }
        if( isset( $_POST['mjob_category_image'] ) && '' !== $_POST['mjob_category_image'] ){
            $group = sanitize_title( $_POST['mjob_category_image'] );
            update_term_meta( $term_id, 'mjob_category_image', $group );
        }
        else{
            update_term_meta($term_id, 'mjob_category_image', false);
        }
    }
    /**
     * Displaying The Term Meta Data In The Term List
     *
     * @param void
     * @return void
     * @since 1.0
     * @package MicrojobEngine
     * @category void
     * @author JACK BUI
     */
    function ae_add_tax_column( $columns ){
        $columns['featured_tax'] = __( 'Featured tax', 'enginethemes' );
        return $columns;
    }
    /**
     * update
     *
     * @param void
     * @return void
     * @since 1.0
     * @package MicrojobEngine
     * @category void
     * @author JACK BUI
     */
    function ae_add_tax_column_content( $content, $column_name, $term_id ){
        global $featured_tax, $mjob_category_image;
        if( $column_name !== 'featured_tax' || $column_name !== 'mjob_category_image' ){
            return $content;
        }
        $term_id = absint( $term_id );
        $featured_tax = get_term_meta( $term_id, 'featured-tax', true );
        if( !empty( $featured_tax ) ){
            $content .= esc_attr( $featured_tax );
        }
        $mjob_category_image = get_term_meta( $term_id, 'mjob_category_image', true );
        $content.='<img id="ae-tax-images-photo" src="'.esc_url( wp_get_attachment_image_url( $mjob_category_image, 'full' ) ).'"<?php echo $hidden; ?> />';
        return $content;
    }
    /**
     * enqueue script
     *
     * @param void
     * @return void
     * @since 1.0
     * @package MicrojobEngine
     * @category void
     * @author JACK BUI
     */
    public function ae_tax_enqueue_scripts(){
        wp_enqueue_media();
        wp_enqueue_style( 'ae-tax-images-css',  get_template_directory_uri() . '/includes/modules/AE_Taxonomy_Meta/assets/ae-tax.css', array(), ET_VERSION);
        wp_enqueue_script( 'ae-tax-images', get_template_directory_uri() . '/includes/modules/AE_Taxonomy_Meta/assets/ae-tax.js',   array(
            'jquery',
            'underscore',
            'backbone',
            'appengine'
        ), 1.0, true );
        $term_id = ! empty( $_GET['tag_ID'] )
            ? (int) $_GET['tag_ID']
            : 0;
        // Localize
        wp_localize_script( 'ae-tax-images', 'i10n_WPTermImages', array(
            'insertMediaTitle' => esc_html__( 'Choose an Image', 'wp-user-avatars' ),
            'insertIntoPost'   => esc_html__( 'Set as image',    'wp-user-avatars' ),
            'deleteNonce'      => wp_create_nonce( 'remove_ae_tax_images_nonce' ),
            'mediaNonce'       => wp_create_nonce( 'assign_ae_tax_images_nonce' ),
            'term_id'          => $term_id,
        ) );
    }

}