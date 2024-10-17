<?php
class Moving_Platform_Main
{
    public static $instance;

    function __construct(){        
		       
		$this->init_hook();     

	}

    function init_hook(){

		add_action('init',array($this, 'register_post_type_moving_request' ));
        
        add_action('init',array($this, 'register_custom_taxonamy_city' ));
        add_action('init',array($this, 'moving_generate_role' ));
        add_shortcode('choosing_role_area',array($this, 'choosing_role_shortcode' ),99);
        add_action('wp_head',array($this, 'set_up_info_frontend' ),10);
        
        add_action('wp_ajax_moving_save_role',array($this,'moving_save_role_action'),99);
        add_action('wp_ajax_nopriv_request_image_uploader',array($this,'request_image_uploader_action'),99);
        add_action('wp_ajax_delete_image_on_server',array($this,'delete_image_on_server_action'),99);
        add_action('wp_ajax_nopriv_submit_moving_request',array($this,'submit_moving_request_action'),99);

        add_action('wp_ajax_nopriv_search_cities',array($this,'search_cities_action'),99);

        //change title by value from admin dashboard
        //add_filter('document_title_parts', array($this, 'set_up_title' ),999);        
      

        //simple cart system
        //init session
        add_action('init', array($this,'custom_init_session'),999);
        add_action('wp_ajax_add_request_cart',array($this,'add_request_to_cart_action'),99);
        add_action('wp_ajax_remove_request_cart',array($this,'remove_request_to_cart_action'),99);
        
        //init option for add custom value to cities in admin dashboard
        add_action( 'city_add_form_fields', array($this,'add_cities_custom_value_form'),999 );   
        add_action( 'city_edit_form_fields', array($this,'edit_cities_custom_value_form'),999 ); 
        
        add_action( 'created_city', array($this,'save_custom_value_city'));
        add_action('edited_city', array($this,'updated_custom_value_city'), 999, 1);
        
	}

    public static function get_instance()    
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    function add_cities_custom_value_form()
    {
        ?>
        <div class="form-field">

            <label for="postal_code">Code Postal</label>
            <input type="text" id="postal_code" name="postal_code">

            <label for="detail_address">Adresse</label>
            <input type="text" id="detail_address" name="detail_address">

            <label for="code_commune">Code Commune</label>
            <input type="text" id="code_commune" name="code_commune">

        </div>
        <?php
    }

    function edit_cities_custom_value_form($term)
    {
        $code_commune=get_term_meta($term->term_id,'code_commune',true) ? : '';
        $postal_code=get_term_meta($term->term_id,'postal_code',true) ? : '';
        $detail_address=get_term_meta($term->term_id,'detail_address',true) ? : '';
        ?>
        <tr class="form-field">                    
            <th scope="row" valign="top"><label for="postal_code">Code Postal</label></th>
            <td>
            <input type="text" id="postal_code" name="postal_code" value="<?php echo $postal_code ?>">
            </td>            
        </tr>

        <tr class="form-field"> 
            <th scope="row" valign="top"><label for="detail_address">Adresse</label></th>            
            <td>
                <input type="text" id="detail_address" name="detail_address" value="<?php echo $detail_address ?>">
            </td>
        </tr>

        <tr class="form-field"> 
            <th scope="row" valign="top"><label for="code_commune">Code Commune</label></th>            
            <td>
                <input type="text" id="code_commune" name="code_commune" value="<?php echo $code_commune ?>">
            </td>
        </tr>                        

        </div>
        <?php
    }

    function save_custom_value_city($term_id)
    {   
        if(isset($_POST['postal_code']) && !empty($_POST['postal_code']))
        {
            update_term_meta( $term_id, 'postal_code',$_POST['postal_code']);
        }

        if(isset($_POST['code_commune']) && !empty($_POST['code_commune']))
        {
            update_term_meta( $term_id, 'code_commune',$_POST['code_commune']);
        }

        if(isset($_POST['detail_address']) && !empty($_POST['detail_address']))
        {
            update_term_meta( $term_id, 'detail_address',$_POST['detail_address']);
        }
    }

    function updated_custom_value_city($term_id)
    {
        if(isset($_POST['postal_code']) && !empty($_POST['postal_code']))
        {
            update_term_meta( $term_id, 'postal_code',$_POST['postal_code']);
        }

        if(isset($_POST['code_commune']) && !empty($_POST['code_commune']))
        {
            update_term_meta( $term_id, 'code_commune',$_POST['code_commune']);
        }

        if(isset($_POST['detail_address']) && !empty($_POST['detail_address']))
        {
            update_term_meta( $term_id, 'detail_address',$_POST['detail_address']);
        }
    }


    //generate moving request custom post type
    function register_post_type_moving_request()
    {
        $labels = array(
            'name'               => _x( 'Moving Request', 'post type general name', 'textdomain' ),
            'singular_name'      => _x( 'Moving Request', 'post type singular name', 'textdomain' ),
            'menu_name'          => _x( 'Moving Requests', 'admin menu', 'textdomain' ),
            'name_admin_bar'     => _x( 'Moving Request', 'add new on admin bar', 'textdomain' ),
            'add_new'            => _x( 'Add New', 'Moving Request', 'textdomain' ),
            'add_new_item'       => __( 'Add New Moving Request', 'textdomain' ),
            'new_item'           => __( 'New Moving Request', 'textdomain' ),
            'edit_item'          => __( 'Edit Moving Request', 'textdomain' ),
            'view_item'          => __( 'View Moving Request', 'textdomain' ),
            'all_items'          => __( 'All Moving Requests', 'textdomain' ),
            'search_items'       => __( 'Search Moving Request', 'textdomain' ),
            'not_found'          => __( 'No Moving Requests found', 'textdomain' ),
            'not_found_in_trash' => __( 'No Moving Requests found in trash', 'textdomain' ),
        );
    
        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'moving_request' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array( 'title', 'editor', 'thumbnail', 'custom-fields' ), // Adjust as needed
        );
    
        register_post_type( 'moving_request', $args );
    }

   

    //apply city taxonomy
    function register_custom_taxonamy_city()
    {
        $city_taxonomy_args = array(
            'hierarchical'      => false,
            'labels'            => array(
                'name'                       => _x('City', 'taxonomy general name'),
                'singular_name'              => _x('City', 'taxonomy singular name'),
                'search_items'               => __('Search City'),
                'popular_items'              => __('Popular City'),
                'all_items'                  => __('All City'),
                'parent_item'                => null,
                'parent_item_colon'          => null,
                'edit_item'                  => __('Edit City'),
                'update_item'                => __('Update City'),
                'add_new_item'               => __('Add New City'),
                'new_item_name'              => __('New City'),                
                'add_or_remove_items'        => __('Add or remove City'),
                'choose_from_most_used'      => __('Choose from the most used City'),
                'menu_name'                  => __('City'),
            ),
            'rewrite'           => array('slug' => 'city','hierarchical'=>false),
            'show_admin_column' => true,
            'query_var'         => true,
        );
            
        register_taxonomy('city', array('moving_request'), $city_taxonomy_args);
    }

    //generate roles for moving platform
    function moving_generate_role()
    {
        $role_info=get_role('author'); //get capabilities of author
        add_role('customer','Customer',$role_info->capabilities );
        add_role('um_custom_role_1','Service Provider',$role_info->capabilities );
        //need to re-check all service_provider and replace
    }

    //generate shortcode choosing roles for profile section
    function choosing_role_shortcode()
    {
        ob_start();
        $current_user=wp_get_current_user();
        $is_defined_role=get_role_by_user_id($current_user->ID);
        ?>
        <div class="moving-platform-role-area">
            <p class="role-headline">
                <?php 
                if($is_defined_role==false)
                {
                    _e('Please choose your role','moving_platform');
                }
                else
                {
                    _e('You have choosen the role','moving_platform');
                }
                ?>              
            </p>
            <?php if($is_defined_role==false): ?>
            <form class="identify-role-form" id="moving-identify-role"> 
                <input type="hidden" name="action" value="moving_save_role">
                <div class="form-role-item choosing-dropdown">
                    <select class="moving-role-selector" name="role_type" id="select-role" autocomplete="off">                     
                        <option value="um_custom_role_1"><?php _e('Service Provider','moving_platform'); ?></option>
                        <option value="customer"><?php _e('Customer','moving_platform'); ?></option>
                    </select>
                </div>

                <div class="form-role-item btn-save-area">
                    <button class="btn-save-role" id="btn-save-role" type="submit">Save</button>
                </div>

                <div class="form-role-item role-notice-text">
                    <?php _e('The customer can only post the requests. The service provider can only purchase contact information','moving_platform'); ?>
                </div>
                
            </form> 
            <?php else: ?>
                <?php if($is_defined_role=='customer'): ?>
                    <p class="defined-role-text"><?php _e('As a customer, you can post moving requests.','moving_platform'); ?>
                    <br>
                        <a href="<?php echo site_url('moving-post-request') ?>"><?php _e('Post request.','moving_platform'); ?></a>
                    </p>                      
                <?php endif; ?>

                <?php if($is_defined_role=='um_custom_role_1'): ?>
                    <p class="defined-role-text">
                        <?php _e('As a service provider, you can access the contact information for posted moving requests.','moving_platform'); ?>
                        <br>
                        <a href="<?php echo site_url('all-requests') ?>"><?php _e('Browse the list.','moving_platform'); ?></a>
                    </p>  
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
   

    function set_up_info_frontend()
    {
        $admin_data=AdminData::get_instance();
        
        $stripe_public_key=$admin_data->getValue('moving_stripe_pk');
        $moving_request_price=$admin_data->getValue('moving_request_price');
        $ajax_url=admin_url('admin-ajax.php');
        $error_ajax_message=__('Something went wrong! Please refresh !','moving_platform');
        $max_files_upload=(int)$admin_data->getValue('max_upload_image'); //load from admin dashboard carbon fields

        $message_upload=__('You could only upload ','moving_platform');
        $message_upload_file=__('files','moving_platform');
        $message_uploading=__('Uploading files ','moving_platform');
        $required_validation_message=__('This field is required','moving_platform');
        $number_type_validation=__('This must be a number','moving_platform');
        $accept_tos_message=__('Please accept term of service to submit','moving_platform');

        //stripe error messages & other messages
        $card_invalid=__('Please enter a valid card number','moving_platform');
        $expiry_invalid=__(' Please enter a valid expiration date.','moving_platform');
        $cvc_invalid=__('Please enter a valid CVC.','moving_platform'); 
        $sending_payment=__('Sending Payment !!','moving_platform'); 
                   
        ?>
        <script src="https://js.stripe.com/v3/"></script>
        <script type="text/javascript">
            let stripe_public_key="<?php echo $stripe_public_key; ?>";            

            const moving_request_price=<?php echo $moving_request_price; ?>;
            let moving_ajaxURL="<?php echo $ajax_url; ?>"; 
            let error_ajax_message="<?php echo $error_ajax_message; ?>";   
            let message_upload="<?php echo $message_upload; ?>";   
            let message_upload_file="<?php echo $message_upload_file; ?>";   
            let message_uploading="<?php echo $message_uploading; ?>";   
            let required_validation_message="<?php echo $required_validation_message; ?>";   
            let number_type_validation="<?php echo $number_type_validation; ?>";   
            let accept_tos_message="<?php echo $accept_tos_message; ?>";   
            let max_file_upload_request_form=<?php echo $max_files_upload; ?>;   

             //stripe error message
            let card_invalid_error="<?php echo $card_invalid; ?>"; 
            let expiry_invalid_error="<?php echo $expiry_invalid; ?>"; 
            let cvc_invalid_error="<?php echo $cvc_invalid; ?>"; 
            let sending_payment="<?php echo $sending_payment; ?>"; 


        </script>
        <?php
    }

    function moving_save_role_action()
    {
        if(!is_user_logged_in())
        {
            die('something went wrong');
        }
        extract($_POST);
        $custom_user_info=wp_get_current_user();
        $custom_user_info->add_role($role_type);
        $data['success']='true';
        $data['message']=__('Updated role successfully','moving_platform');   
        if(wp_get_referer())
        {
            $data['redirect_url']=wp_get_referer();
        }
        else
        {
            $data['redirect_url']=site_url('/profil/');
        }

        wp_send_json($data);
        die();
    }

    function request_image_uploader_action()
    {
      /*  if(!is_user_logged_in())
        {
            die();
        } */

        if (!check_ajax_referer('request_image_uploader_nonce', $_POST['_ajax_nonce'])) {
            die();
        } 

        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        $upload_dir = wp_upload_dir();
        $upload_overrides = array( 'test_form' => false );
    
        if (!empty($_FILES['file'])) {
            $uploaded_file = $_FILES['file'];
        }
    
        $movefile = wp_handle_upload( $uploaded_file, $upload_overrides );

        $attachment = array(       
            'guid'  => $upload_dir['url'].'/'.sanitize_file_name($uploaded_file['name']), 
            'post_mime_type' => $uploaded_file['type'],
            'post_title'     =>  sanitize_file_name($uploaded_file['name']),
            'post_content'   => 'images of custom moving request',
            'post_status'    => 'inherit'
        );
    
        $attach_id = wp_insert_attachment($attachment, $uploaded_file['name']); 

        if($attach_id)
        {
            update_post_meta($attach_id,'_wp_attached_file',substr($upload_dir['subdir'],1).'/'.sanitize_file_name($uploaded_file['name']));                      
            update_post_meta($attach_id,'custom_attachment_type','moving_request_attach_item');        
            update_post_meta($attach_id,'is_temp','yes');        
        }

        if ( $movefile && ! isset( $movefile['error'] ) ) {
            $data['success']='true';
            $data['attach_id']= $attach_id;    
            $data['url_uploaded']=wp_get_attachment_image_url($attach_id,'thumbnail');        
        } 
        else
        {
            $data['success']='false';
        }
        wp_send_json($data);

    }

    function delete_image_on_server_action()
    {
        if (!is_user_logged_in()) {
            die();
        }
        $result_delete=wp_delete_attachment($_POST['attach_file_id_delete'],true);
        if($result_delete)
        {
            $response = array(
                'success' => true,
                'message' => __('File has been deleted.','moving_platform'),           
            );
        }
        else{
            $response = array(
                'success' => false,                
                'message' => __('Failed to delete file','moving_platform'),            
            );
        }
    
        wp_send_json($response);
        die();
    }

    function submit_moving_request_action()
    {
        //allow guest user to submit
       /* if (!is_user_logged_in()) {
            die();
        } */
        if (!wp_verify_nonce($_POST['submit_moving_request_nonce'],'submit_moving_request_nonce')) {
            die('something went wrong');
        } 
        extract($_POST);    
        $admin_data=AdminData::get_instance();

        $moving_request=array(
                        'post_title'=>sanitize_text_field($request_title),
                        'post_content'=>wp_strip_all_tags($request_description_content),
                        'post_status'=> $admin_data->getValue('moving_request_status'), //set status base on the status from admin dashboard
                        'post_type'=>'moving_request',                       
        );
        //set post author =0 if non-logged user
        if(!is_user_logged_in())
        {
            $moving_request['post_author']=0;
        }
        $inserted_request=wp_insert_post($moving_request);
        if($inserted_request && !is_wp_error($inserted_request))
        {
           update_post_meta($inserted_request,'last_name',$last_name);
           update_post_meta($inserted_request,'first_name',$first_name);

           //handle date
           $convert_departure_date = DateTime::createFromFormat('Y-M-d', $departure_date);
           $convert_arrival_date = DateTime::createFromFormat('Y-M-d', $arrival_date);

           update_post_meta($inserted_request,'departure_date',$convert_departure_date->format('Y-m-d'));
           update_post_meta($inserted_request,'arrival_date',$convert_arrival_date->format('Y-m-d'));

           update_post_meta($inserted_request,'departure_address',$departure_address);
           update_post_meta($inserted_request,'arrival_address',$arrival_address);

           update_post_meta($inserted_request,'postal_code_depart',$postal_code_depart);
           update_post_meta($inserted_request,'postal_code_arrival',$postal_code_arrival);

           update_post_meta($inserted_request,'phone_number',$contact_method);
           update_post_meta($inserted_request,'email_notification',$email_notification);
           update_post_meta($inserted_request,'contact_method',$contact_method.' | '.$email_notification);
           update_post_meta($inserted_request,'budget',$budget);
          

           //update city
           update_post_meta($inserted_request,'departure_city_id',$city_selector_depart);
           update_post_meta($inserted_request,'arrival_city_id',$city_selector_arrival);    
           wp_set_post_terms($inserted_request,array((int)$city_selector_depart,(int)$city_selector_arrival),'city');
           

           if(!empty($_POST['image_attachment_ids']))
           {
                $list_images=explode(',',$_POST['image_attachment_ids']);
                foreach($list_images as $img_attachment_id )
                {
                    $image_attach_update = array('ID' => $img_attachment_id, 'post_parent' => $inserted_request);
                    wp_update_post($image_attach_update);
                    //remove temp meta after set moving request ID
                    delete_post_meta($img_attachment_id,'is_temp','yes');
                }       
           }

           //create hook for future custom
            do_action('custom_hook_after_insert_request',$inserted_request);

           $data['message']=__('Created moving request successfully','moving_platform');
           $data['success']='true';
           $data['redirect_url']=$admin_data->getValue('after_post_request_redirect'); // get value from admin dashboard
        }
        else
        {
            $data['message']=__('Failed to create request','moving_platform');
            $data['success']='false';
        }
        wp_send_json($data);
        die();

    }
    public function get_moving_requests($page_number=1,$options=array())
    {
      //  global $post;        
        //$options array include is_admin, 
        if(is_array($options) && !empty($options))
        {
            extract($options);
        }

        $admin_data=AdminData::get_instance();
        $number_request_each_pages= $admin_data->getValue('moving_request_per_page');

        $request_args=array(
            'post_type' => 'moving_request',    
            'posts_per_page' => $number_request_each_pages, 
            'paged'  => $page_number,
            'post_status' =>'publish', // can be changed with search params
            'order' => 'desc', // can be changed with search params
            'orderby' => 'date', // can be changed with search params
        );

        //handle them order va date

        //include pending post.
        if(isset($is_admin) && $is_admin==true)
        {
            $request_args['post_status']=array('publish','pending');
        }   

        //handle sort by param
        if(isset($sort_by) && !empty($sort_by))
        {
            $request_args['order']=$sort_by;
            $request_args['orderby']='date'; 
        }

       
        //when user turn on mine option --> clear all other filters
        if(isset($mine) && $mine=='yes')
        {
            $current_user_role=get_role_by_user_id(get_current_user_id());
            //handle only my requests for CUSTOMER
            if($current_user_role=='customer')
            {
                $request_args['author']=get_current_user_id();
            }
            //handle my paid list for SERVICE PROVIDER
            if($current_user_role=='um_custom_role_1')
            {
                $paid_list=get_paid_list_by_user_id(get_current_user_id());
                $request_args['post__in']=$paid_list;
                $request_args['orderby']='post__in';
            }
        }
        else
        {
            //handle search string, this will apply to post title and post content
            if(isset($search_string) && !empty($search_string))
            {
                $request_args['s']=$search_string;
            }

            //handle filter arrival city & departure city
            if(isset($arrival_city_param) && !empty($arrival_city_param))
            {
                $request_args['meta_query'][]=array(
                    'key'     => 'arrival_city_id',
                    'value'   => $arrival_city_param,          
                    'compare' => '=',   
                );
            }

            if(isset($departure_city_param) && !empty($departure_city_param))
            {
                $request_args['meta_query'][]=array(
                    'key'     => 'departure_city_id',
                    'value'   => $departure_city_param,          
                    'compare' => '=',   
                );
            }

            //handle filter arrival date & departure date
            if(isset($arrival_date_param) && !empty($arrival_date_param))
            {
                $converted_arrival_date=DateTime::createFromFormat('Y-M-d', $arrival_date_param);

                $request_args['meta_query'][]=array(
                    'key'     => 'arrival_date',
                    'value'   => $converted_arrival_date->format('Y-m-d'),          
                    'compare' => '=',   
                );
            }

            if(isset($departure_date_param) && !empty($departure_date_param))
            {
                $converted_departure_date=DateTime::createFromFormat('Y-M-d', $departure_date_param);

                $request_args['meta_query'][]=array(
                    'key'     => 'departure_date',
                    'value'   => $converted_departure_date->format('Y-m-d'),          
                    'compare' => '=',   
                );
            }
            
            //handle budget filter
            if(isset($budget_list_param) && !empty($budget_list_param))
            {                
                $converted_budget_param=$this->extract_min_max($budget_list_param);

                if($converted_budget_param)
                {
                    if($converted_budget_param['range']==true)
                    {
                        $request_args['meta_query'][]=array(
                            'key'     => 'budget',
                            'value'   => array(
                               (int)$converted_budget_param['min'], //min budget
                               (int)$converted_budget_param['max'],//max budget
                            ),
                            'type'    => 'numeric',
                            'compare' => 'BETWEEN',
                        );
                    }
                    else
                    {
                        $request_args['meta_query'][]=array(
                            'key'     => 'budget',
                            'value'   => $converted_budget_param['budget'],
                            'type'    => 'numeric',
                            'compare' => $converted_budget_param['compare'],
                        );
                    }
                }
             
            }

            
        }

        //handle hidden posts, always display posts for admin.
        if(is_user_logged_in() && !current_user_can('manage_options'))
        {
            $hidden_posts=get_user_meta(get_current_user_id(),'hidden_post_list',true);
            if($hidden_posts)
            {
                $request_args['post__not_in']=$hidden_posts;
            }
        }

        $request_query=new WP_Query($request_args);
        $request_collection=array();
        $request_collection_info=array();

        if($request_query->have_posts())
        {
            while($request_query->have_posts())
            {
                $request_query->the_post();
                $current_request = get_post(); // Get the current post object instead of using global $post
                $converted_request=$this->convert_moving_request($current_request);
                $request_collection[]=$converted_request;
            }
        }

        $request_collection_info['request_list']= $request_collection;
        $request_collection_info['max_num_pages']= $request_query->max_num_pages;
        $request_collection_info['found_posts']= $request_query->found_posts;
        wp_reset_postdata();

        return $request_collection_info;
    }

    function convert_moving_request($request)
    {
        //return info: title, human readable published time, budget, 
        //customer name , description, departure & arrival info,
        //contact method, author info ( name & link get from UM plugin),
        // image collection
        // hidden ids ( user ids)

        $admin_data=AdminData::get_instance();
        $profile_url=$admin_data->getValue('author_url_profile');
              
        $request->last_name=get_post_meta($request->ID,'last_name',true);
        $request->first_name=get_post_meta($request->ID,'first_name',true);

        if($request->post_content)
        {
            $request->convert_description=wp_strip_all_tags($request->post_content);
        }
        else
        {
            $request->convert_description=__('This moving request has no description','moving_platform');
        }
       
        $request->moving_budget=number_format(get_post_meta($request->ID,'budget',true));
        
        //departure
        $depature_date=get_post_meta($request->ID,'departure_date',true);
        $departure_date_instance = DateTime::createFromFormat('Y-m-d', $depature_date);
        $request->departure_date= $departure_date_instance->format('Y-M-d');

        $request->departure_address=get_post_meta($request->ID,'departure_address',true);
        $request->postal_code_departure=get_post_meta($request->ID,'postal_code_depart',true);
        $departure_city_id=get_post_meta($request->ID,'departure_city_id',true);
        $departure_city=get_term_by('ID',(int)$departure_city_id,'city');
        if($departure_city)
        {
            $request->departure_city=$departure_city->name;
        }
        else
        {
            $request->departure_city=__('None','moving_platform');
        }
       

        //arrival
        $arrival_date=get_post_meta($request->ID,'arrival_date',true);
        $arrival_date_instance = DateTime::createFromFormat('Y-m-d', $arrival_date);
        $request->arrival_date= $arrival_date_instance->format('Y-M-d');

        $request->arrival_address=get_post_meta($request->ID,'arrival_address',true);
        $request->postal_code_arrival=get_post_meta($request->ID,'postal_code_arrival',true);
        $arrival_city_id=get_post_meta($request->ID,'arrival_city_id',true);
        $arrival_city=get_term_by('id',(int)$arrival_city_id,'city');        
        if($arrival_city)
        {
            $request->arrival_city=$arrival_city->name;
        }
        else
        {
            $request->arrival_city=__('None','moving_platform');
        }

        $request->contact_method=get_post_meta($request->ID,'contact_method',true);
        $request->human_readable_published=timeAgo($request->post_modified);

        //author info
        //initialize UM info
        //$custom_um_user = um_fetch_user( $request->post_author );
        $request->author_avatar=um_get_user_avatar_url( $request->post_author, '50' );
        $request->author_name=um_user( 'display_name' );
        $request->author_profile_url=$profile_url.get_user_meta($request->post_author,'um_user_profile_url_slug_user_login',true);

        //images collection        
        $converted_image_collection=array();
        
        $image_collection=get_attached_media('image',$request->ID);
        if($image_collection && !empty($image_collection))
        {
            foreach($image_collection as $image_item)
            {
                $thumbnail=wp_get_attachment_image_url($image_item->ID,'thumbnail');
                $full=wp_get_attachment_image_url($image_item->ID,'full');
                $converted_image_collection[]=array('group'=>'gallery-'.$request->ID,'thumbnail'=>$thumbnail,'full'=>$full);
            }
        }

        $request->image_collection=$converted_image_collection;
       
        //hidden user ids

        return $request;
    }
    
    function set_up_title($title_parts)   
    {
        $admin_data=AdminData::get_instance();

        if(is_page_template('moving_post_request_page.php'))
        {
            $title_parts['title'] = $admin_data->getValue('post_request_page_title');
        }       
        
        if(is_page_template('all-requests.php'))
        {
            $title_parts['title'] = $admin_data->getValue('all_request_page_title');
        }   
        
        if(is_page_template('checkout-requests.php'))
        {
            $title_parts['title'] = $admin_data->getValue('checkout_page_title');
        }  

        return $title_parts;
    }

   

    //use this function for budget filter
    function extract_min_max($input) {
        // Check if the string contains comparison operators and extract them
        if (preg_match('/(>=|<=|>|<|=)(\d+)/', $input, $matches)) {
            return ['compare' => $matches[1], 'budget' => (int)$matches[2], 'range'=>false];
        }

        // Handle ranges like "1000-5000"
        if (preg_match('/(\d+)-(\d+)/', $input, $matches)) {
            return ['min' => (int)$matches[1], 'max' => (int)$matches[2], 'range'=>true];
        }

        // Return null if the string doesn't match the expected format
        return false;
    }

    function custom_init_session() {
        if (!session_id()) {
            session_start();
        }
    }

    function add_request_to_cart_action() {
        if(!is_user_logged_in())
        {
            die();
        }

        $product_id = intval($_POST['cart_request_id']);
        
        // Initialize cart if it's empty
        if (!isset($_SESSION['request_cart'])) {
            $_SESSION['request_cart'] = array();
        }
        
        // Add product to cart
        if (!in_array($product_id, $_SESSION['request_cart'])) {
            $_SESSION['request_cart'][] = $product_id;
            $data['message']=__('Added the item to cart.','moving_platform');
            $data['updated_text']=__('Remove from cart','moving_platform');
            $data['success']='true';
            $data['added_id']=$product_id;

            $cart_info=$this->cart_info($_SESSION['request_cart']);
            if($cart_info)
            {
                $data['total_items']=$cart_info['total_items'];
                $data['total_price']=$cart_info['total_price'];
                $data['item_ids']=$cart_info['item_ids'];
            }
            

        } else {
            $data['message']=__('Item has been already added.','moving_platform');            
            $data['success']='false';
        }
        wp_send_json($data);
        die();
    }

    function remove_request_to_cart_action()
    {
        if(!is_user_logged_in())
        {
            die();
        }
        $product_id = intval($_POST['cart_request_id']);

        if (isset($_SESSION['request_cart']) && in_array($product_id, $_SESSION['request_cart'])) {
            $_SESSION['request_cart'] = array_diff($_SESSION['request_cart'], array($product_id));
            
            $data['message']=__('Removed item successfully','moving_platform');
            $data['success']='true';
            $data['removed_id']=$product_id;
            $data['updated_text']=__('Add to cart','moving_platform');

            $cart_info=$this->cart_info($_SESSION['request_cart']);
            if($cart_info)
            {
                $data['total_items']=$cart_info['total_items'];
                $data['total_price']=$cart_info['total_price'];
                $data['item_ids']=$cart_info['item_ids'];
            }
            if($cart_info['total_items']==0 || !$cart_info)
            {
                $data['is_must_redirect']=site_url('all-requests');
                $data['total_items']=0;
            }

        } else {
            $data['message']=__('Item not in cart.','moving_platform');
            $data['success']='false';
        }
        wp_send_json($data);
        die();
    }

    function cart_info($cart_list)
    {
        $admin_data=AdminData::get_instance();

        if(!empty($cart_list) && is_array($cart_list))
        {
            $total_item=count($cart_list);
            $data['total_items']=$total_item;
            $data['total_price']=$total_item * (int)$admin_data->getValue('moving_request_price');
            $data['item_ids']=implode(',',$cart_list);
            return $data;
        }
        return false;
    }

    function search_cities_action()
    {
        $term_collection=[];
        extract($_POST); 
        $search_args = array(
            'taxonomy'   => 'city',       
            'name__like' => $query_search,  
            'hide_empty' => false,         
        );
        $cities = get_terms($search_args);
        if($cities && !is_wp_error($cities))
        {
            foreach ($cities as $city) 
            {
                $postal_code = get_term_meta($city->term_id, 'postal_code', true);
                $city->postal_code = $postal_code ? $postal_code : '';
                $term_collection[] = $city;
            }
            //$term_collection=$cities;
        }
        $data['term_collection']=$term_collection;
        wp_send_json($data);
        die();
    }
   
}

new Moving_Platform_Main();

//handling human readable time

function timeAgo($datetime) {

    $now = new DateTime(current_time('mysql'));
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0) {
        return $diff->y . ' Année' . ($diff->y > 1 ? 's' : '') . '  il y a';
    } elseif ($diff->m > 0) {
        return $diff->m . ' Mois' . ($diff->m > 1 ? 's' : '') . '  il y a';
    } elseif ($diff->d > 0) {
        return $diff->d . ' Jour' . ($diff->d > 1 ? 's' : '') . '  il y a';
    } elseif ($diff->h > 0) {
        return $diff->h . ' Heure' . ($diff->h > 1 ? 's' : '') . ' Il y a';
    } elseif ($diff->i > 0) {
        return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . '  il y a';
    } elseif ($diff->s > 0) {
        return $diff->s . ' seconde' . ($diff->s > 1 ? 's' : '') . '  il y a';
    } else {
        return 'À l instant';
    }
}

function get_role_by_user_id($user_id)
{       
    if(is_user_logged_in())
    {
        $user_info=get_user_by('ID',$user_id);
        if(in_array('customer',$user_info->roles))
        {
           return 'customer';
        }
        if(in_array('um_custom_role_1',$user_info->roles))
        {
           return 'um_custom_role_1';
        }
    }
  
    return false;
}

function get_all_term_by_name($term_name)
{
    $terms = get_terms(array(
        'taxonomy' => $term_name,
        'hide_empty' => false,
        'number' => 50,    
    ));
    return $terms;
}

function generate_drop_down($dropdown_info)
{
    ob_start();
    //dropdown info should include term_name, name, id , class name , selected value
    
    if(!empty($dropdown_info))
    {
        $term_list=get_all_term_by_name($dropdown_info['term_name']);        
    }
    ?>
    <?php if(isset($term_list) && $term_list && !is_wp_error($term_list)): ?>        
        <select placeholder="<?php echo $dropdown_info['placeholder'] ?>" id="<?php echo $dropdown_info['id'] ?>" class="<?php echo $dropdown_info['class'] ?>" name="<?php echo $dropdown_info['name'] ?>">
                <option value=""><?php echo $dropdown_info['placeholder'] ?></option>
                <?php foreach($term_list as $term_item): ?>                    
                    <option <?php if(isset($dropdown_info['selected_value']) && $dropdown_info['selected_value']==$term_item->term_id) echo 'selected'; ?>  data-commune="<?php echo get_term_meta($term_item->term_id,'code_commune',true); ?>" data-postal="<?php echo get_term_meta($term_item->term_id,'postal_code',true); ?>" data-address="<?php echo get_term_meta($term_item->term_id,'detail_address',true); ?>" data-postal="<?php echo get_term_meta($term_item->term_id,'postal_code',true); ?>" value="<?php echo $term_item->term_id ?>">
                        <?php echo $term_item->name ?>
                    </option>
                <?php endforeach; ?>
        </select>
    <?php endif; ?>
    <?php
    return ob_get_clean();
}

function check_request_owner($user_id,$request_id)
{
    $request_owner_id=get_post_field('post_author',$request_id);
    
    if(intval($request_owner_id) == intval($user_id))
    {
        return true;
    }    
    return false;
}

function check_ban_user($user_id,$request_id)
{
    $ban_list=get_post_meta($request_id,'ban_list_ids',true);
    if($ban_list)
    {
        return in_array($user_id,$ban_list);
    }
    return false;
}

    