<?php
/*
Template Name: All Requests page
*/
?>
<?php
get_header();
$admin_data=AdminData::get_instance();
$moving_instance=Moving_Platform_Main::get_instance();

//check admin
$admin_view=false;
if ( current_user_can( 'manage_options' ) ) {
    $admin_view=true;
}

//include pending post if admin
$options=array();
if($admin_view==true)
{
    $options['is_admin']=true;
}

//filters and pagination params
$current_page = get_query_var('paged') ? get_query_var('paged') : 1; 

$search_string = isset($_GET['search']) ? $_GET['search'] : '';
$budget_list_param=isset($_GET['budget_filter']) ? $_GET['budget_filter'] : '';

$arrival_date_param=isset($_GET['arrival_date']) ? $_GET['arrival_date'] : '';
$departure_date_param=isset($_GET['departure_date']) ? $_GET['departure_date'] : '';

$arrival_city_param=isset($_GET['city_arrival']) ? $_GET['city_arrival'] : '';
$departure_city_param=isset($_GET['city_depart']) ? $_GET['city_depart'] : '';

$mine=isset($_GET['mine']) ? $_GET['mine'] : '';

$sort_by_param=isset($_GET['sort_by']) ? $_GET['sort_by'] : '';

//set params
$options['search_string']=$search_string;
$options['budget_list_param']=$budget_list_param;

$options['arrival_date_param']=$arrival_date_param;
$options['departure_date_param']=$departure_date_param;

$options['arrival_city_param']=$arrival_city_param;
$options['departure_city_param']=$departure_city_param;

$options['mine']=$mine;
$options['sort_by']=$sort_by_param;
//end set params

//init filter budget list
$budget_filter_list=explode(',',$admin_data->getValue('budget_filter_list'));
$current_user_role=get_role_by_user_id(get_current_user_id());

$moving_request_collection=$moving_instance->get_moving_requests($current_page,$options);

?>
<div class="container all-requests-wrapper">
    <?php if($current_user_role=='service_provider' || $admin_view): ?>
    <!-- shopping bag icon -->
    <div class="shopping-bag-area">
        <a class="shopping-bag-icon" href="<?php echo site_url('checkout-requests') ?>"><i class="fa fa-shopping-bag"></i>
        <span id="number-item-in-cart" class="number-item"><?php if(isset($_SESSION['request_cart'])) echo count($_SESSION['request_cart']); else echo '0'; ?></span>
        </a>
    </div>
    <!-- end shopping bag icon -->

    <!-- searh bar -->
    <div class="search-bar-area" id="search-bar-area">
        <div class="row">
            <!-- use to move out when the users choose value from tom select -->
            <input type="hidden" id="hidden-focus">

            <div class="col-md-6 col-lg-6 col-sm-12">
                <div class="search-bar-fields">
                    <i class="search-bar-icon fa fa-search"></i>
                    <input class="search-bar-input" type="text" id="search" name="search" value="<?php echo $search_string; ?>" placeholder="<?php _e('Keyword','moving_platform'); ?>">
                </div>
            </div>

            <div class="col-md-3 col-lg-3 col-sm-12">
                <div class="search-bar-fields">
                    <i class="search-bar-icon fa-solid fa-coins"></i>
                    <select class="budget-search-filter" id="budget-search-filter" placeholder="<?php _e('Budget Filter (€)','moving_platform'); ?>" autocomplete="off">
                        <option value=""><?php _e('Budget Filter','moving_platform'); ?></option>
                        <?php if($budget_filter_list && is_array($budget_filter_list)): ?>
                                <?php foreach($budget_filter_list as $filter_option): ?>
                                    <option <?php if( $filter_option==$budget_list_param) echo 'selected'; ?> value="<?php echo $filter_option ?>"><?php echo $filter_option ?></option>
                                <?php endforeach; ?>
                        <?php endif; ?>
                                            
                    </select>
                </div>
            </div>

            <div class="col-md-3 col-lg-3 col-sm-12">
                <div class="search-bar-fields">
                    <i class="search-bar-icon fa fa-filter"></i>
                    <select class="budget-search-filter" id="sort-filter" placeholder="<?php _e('Sort','moving_platform'); ?>" autocomplete="off">
                        <option <?php if($sort_by_param=='desc') echo 'selected'; ?> value="desc"><?php _e('Latest','moving_platform'); ?></option>
                        <option <?php if($sort_by_param=='asc') echo 'selected'; ?> value="asc"><?php _e('Oldest','moving_platform'); ?></option>
                    </select>
                </div>
            </div>

            <div class="col-md-3 col-lg-3 col-sm-12">
                <div class="search-bar-fields">
                    <i class="search-bar-icon fa fa-calendar"></i>                   
                    <input type="text" class="search-bar-input custom_date_picker" name="departure_date" id="departure_date" value="<?php echo $departure_date_param; ?>" placeholder="<?php _e('Departure Date Filter', 'moving_platform'); ?>">
                </div>
            </div>

            <div class="col-md-3 col-lg-3 col-sm-12">
                <div class="search-bar-fields">
                    <i class="search-bar-icon fa fa-calendar"></i>                   
                    <input type="text" class="search-bar-input custom_date_picker" name="arrival_date" id="arrival_date" value="<?php echo $arrival_date_param; ?>" placeholder="<?php _e('Arrival Date Filter', 'moving_platform'); ?>">
                </div>
            </div>

            <div class="col-md-3 col-lg-3 col-sm-12">
                <div class="search-bar-fields">                      
                    <i class="search-bar-icon fas fa-plane-departure"></i> 
                  
                     <?php 
                     echo generate_drop_down(array('term_name'=>'city',
                            'name'=>'city_selector_depart',
                            'id'=>'city_selector_depart',
                            'class'=>'filter_city_selector',
                            'placeholder'=> __('Type to search depart cities','moving_platform'),
                            'selected_value'=>$departure_city_param,
                            )
                        ); 
                    ?>
                
                </div>
            </div>

            <div class="col-md-3 col-lg-3 col-sm-12">
                <div class="search-bar-fields">                    
                    <i class="search-bar-icon fas fa-plane-arrival"></i> 
                  
                     <?php 
                     echo generate_drop_down(array('term_name'=>'city',
                            'name'=>'city_selector_arrival',
                            'id'=>'city_selector_arrival',
                            'class'=>'filter_city_selector',
                            'placeholder'=> __('Type to search arrival cities','moving_platform'),
                            'selected_value'=>$arrival_city_param,
                            )
                            
                        ); 
                    ?>
                
                </div>
            </div>

            <div class="col-md-4 col-lg-4 col-sm-12">
                <div class="search-btn-area">        
                    <button type="button" id="btn-search-submit" class="btn-search-submit"><?php _e('Search','moving_platform'); ?></button>
                    <input type="hidden" value="<?php echo site_url('all-requests'); ?>" name="search_link" id="search_link">
                    <button type="button" id="btn-clear-filter" class="btn-clear-filter"><?php _e('Clear filters','moving_platform'); ?></button>
                </div>
            </div>

            <div class="col-md-8 col-lg-8 col-sm-12">
                <!-- only show toggle button to the logged user -->
                <?php if($current_user_role): ?>
                    
                        <div class="on-off-wrapper">
                            <?php if($current_user_role=='customer'): ?>
                                <span class="only-text"><?php _e('Only My Request','moving_platform'); ?></span>                    
                            <?php endif; ?>

                            <?php if($current_user_role=='service_provider'): ?>
                                <span class="only-text"><?php _e('Only My Paid list','moving_platform'); ?></span>                    
                            <?php endif; ?>

                            <label class="custom-switch-toggle">
                                    <input type="checkbox" id="only_mine" name="only_mine" <?php if($mine=='yes') echo 'checked'; ?> >
                                    <span class="custom-slider-toggle"></span>
                            </label>  
                        </div>                                   

                <?php endif; ?>
            </div>

        </div>
    </div>
    <!-- searh bar -->

    <div class="all-request-title">             
        <?php echo $moving_request_collection['found_posts'].' '.__('Requests found','moving_platform'); ?>
    </div>
    <?php if(!empty($moving_request_collection) && $moving_request_collection['found_posts'] > 0): ?>
        <?php $request_list=$moving_request_collection['request_list']; ?>
        
    <div class="row all-requests-container">
        
        <?php foreach($request_list as $request_item): ?>
            <?php 
            //check paid request first & is_owner            
            if(is_user_logged_in())
            {
                $is_paid=check_paid_request(get_current_user_id(),$request_item->ID);
                $is_owner=check_request_owner(get_current_user_id(),$request_item->ID);
                $is_ban=check_ban_user(get_current_user_id(),$request_item->ID);
                
                $is_added_cart=false;
                if(isset($_SESSION['request_cart']) && in_array($request_item->ID, $_SESSION['request_cart']))
                {
                    $is_added_cart=true;
                }
            }
            else
            {
                $is_paid=false;
                $is_owner=false;
                $is_ban=false;
                $is_added_cart=false;
            }            
          
            ?>
        <?php //if(!$is_ban): ?>
        <div class="col-md-12 col-lg-12 col-sm-12 request-item <?php if($is_paid) echo 'paid-request-item'; ?>" data-request-item-wrapper="<?php echo $request_item->ID ?>">
            <!-- paid mark icon ( floating icon position absolute ) -->
            <div class="paid-mark-wrapper <?php if(!$is_paid) echo 'unpaid_mark'; ?>" data-mark-paid-icon="<?php echo $request_item->ID ?>">
                <p class="paid_text"><i class="fa-solid fa-circle-check"></i><?php _e('Paid','moving_platform'); ?></p>
            </div>
          
            <!-- paid mark icon ( floating icon position absolute ) -->

            <p class="request-item-title">                
                <span class="request-title"><?php echo $request_item->post_title ?></span>
                <!-- show pending label to the request owner & admin -->
                <?php if($request_item->post_status=='pending' && ($is_owner || $admin_view)): ?>
                    <span class="moving-info-label pending-label"><?php _e('Pending','moving_platform'); ?></span>
                <?php endif; ?>
                
                <!-- show my request label to request owner -->
                <?php if($is_owner): ?>                 
                    <span class="moving-info-label owner-label"><?php _e('My Request','moving_platform'); ?></span>
                <?php endif; ?>

            </p>
            <div class="basic-info-group">
                <span class="info-item"><i class="fa fa-clock"></i><?php _e('Posted','moving_platform') ?> <strong><?php echo $request_item->human_readable_published ?></strong></span>
                <span class="info-item"><i class="fa-solid fa-coins"></i><?php _e('Budget','moving_platform') ?> <strong><?php echo $request_item->moving_budget ?> €</strong></span>
                <span class="info-item"><i class="fa fa-user"></i><?php _e('Customer','moving_platform') ?> <strong><?php echo $request_item->last_name.' '.$request_item->first_name ?> </strong></span>
            </div>
            <div class="moving-request-description">
                <?php echo $request_item->convert_description; ?>
            </div>
            <div class="moving-request-address-info">
                <p class="address-info-item">
                    <i class="fas fa-plane-departure"></i> 
                    <span class="highlight-title"><?php _e('Departure:','moving_platform') ?></span> 
                    <span class="detailed-address-text"><?php echo $request_item->departure_address.', '.$request_item->departure_city.', '.__('Postal code: ','moving_platform').$request_item->postal_code_departure ?> | </span>
                    <span class="detail-time"><?php echo $request_item->departure_date; ?></span> 
                </p>

                <p class="address-info-item">
                    <i class="fas fa-plane-arrival"></i> 
                    <span class="highlight-title"><?php _e('Arrival:','moving_platform') ?></span> 
                    <span class="detailed-address-text"><?php echo $request_item->arrival_address.', '.$request_item->arrival_city.', '.__('Postal code: ','moving_platform').$request_item->postal_code_arrival ?> | </span>
                    <span class="detail-time"><?php echo $request_item->arrival_date; ?></span> 
                </p>

            </div>
            <div class="contact-information-area">
                <!-- contact method (important thing) -->
                <p class="contact-information-note">
                    <i class="fas fa-comments"></i>
                    <?php _e('Contact:','moving_platform') ?>
              
                    <?php if(is_user_logged_in()): ?>
                    <!-- user logged logic -->
                        <?php if(!$admin_view): ?>
                            <?php if($is_paid || $is_owner): ?>
                                <!-- show contact method if the user has paid or the user is request owner-->
                                <span class="detail-information"><?php echo $request_item->contact_method; ?></span> 
                            <?php else: ?>
                                <?php if($current_user_role=='service_provider'): ?>
                                    <span class="detail-information not-paid" data-contact-id="<?php echo $request_item->ID ?>"></span>                
                                    <!--use data contact id to show contact method if user pay successfully --> 

                                    <a href="#" class="pay-btn-fake"></a>   <!--only display on mobile to break line -->    
                                    <a href="#" class="pay-btn pay-now" data-pay-item-title="<?php echo $request_item->post_title; ?>" data-pay-item="<?php echo $request_item->ID; ?>">Buy</a> 
                                    <a href="#" data-action-cart="<?php echo $request_item->ID; ?>" class="pay-btn <?php if($is_added_cart) echo 'remove-from-cart'; else echo 'add-to-cart'; ?>">                                        
                                        <?php 
                                        if($is_added_cart)
                                        {
                                            _e('Remove from cart','moving_platform'); 
                                        }
                                        if(!$is_added_cart)
                                        {
                                            _e('Add to cart','moving_platform'); 
                                        }
                                        
                                        ?>
                                    </a> 
                                <?php endif; ?>

                                <!-- the customer cannot see or buy the contact method of the other customers. -->
                                <?php if($current_user_role=='customer'): ?>
                                    <span class="detail-information"><?php _e('The contact method is hidden','moving_platform'); ?></span> 
                                <?php endif; ?>
                            <?php endif; ?>
                        
                        <?php else: ?>
                        <!-- admin can see all the contact method -->
                            <span class="detail-information"><?php echo $request_item->contact_method; ?></span> 
                        <?php endif; ?>

                    <?php else: ?>
                     <!-- user NOT logged logic --> 
                            <a href="<?php echo site_url('/identification/') ?>" class="request-list-login-btn"><?php _e('Login','moving_platform'); ?></a> 
                    <?php endif; ?>  
                            

                </p>
                <!-- end contact method (important thing) -->

                <div class="request-posted-author">
                    <a href="<?php echo $request_item->author_profile_url ?>" class="request-owner-name" target="_blank">
                        <img src="<?php echo $request_item->author_avatar ?>"> 
                        <span class="author-name"><?php _e('Owner:','moving_platform') ?> <strong><?php echo $request_item->author_name ?></strong></span>                     
                    </a>                                       
                </div>
            </div>
            <!-- gallery fancybox -->
                <div class="image-gallery-group">                    
                    <?php if($request_item->image_collection): ?>                        
                        <?php foreach($request_item->image_collection as $image_item): ?>
                            <a href="<?php echo $image_item['full']; ?>" data-fancybox="<?php echo $image_item['group']; ?>">
                                <img src="<?php echo $image_item['thumbnail']; ?>">
                            </a>                  
                        <?php endforeach; ?>
                    <?php endif; ?>            
                </div>
            <!-- end gallery fancybox -->
        </div> 
        <?php //endif; ?>   

        <?php endforeach; ?>

        <!--pagination bar -->
        <div class="row request-list-pagination-wrapper">
            <ul class="custom-request-pagination">
                <?php 
                    $big = 999999999;
                    if(isset($moving_request_collection) && is_array($moving_request_collection['request_list']) && !empty($moving_request_collection['request_list']))
                    {                      
                        $pagination_list= paginate_links( array(
                            //'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
                            'base' => site_url('all-requests').'/page/%#%/',                            
                            'total'    => $moving_request_collection['max_num_pages'],
                            'current'  => max( 1, get_query_var( 'paged' ) ),
                            'prev_text' => __('Previous','moving_platform'),
                            'next_text' =>  __('Next','moving_platform'),
                            //'format' => '?paged=%#%',
                            //'format' => 'page=%#%',
                            //'format' => 'page/%#%/',                            
                            'type'=>'array',      
                            'add_args'=>false,                     
                        ) );
                           
                        if($pagination_list && is_array($pagination_list))
                        {
                            foreach($pagination_list as $page_item)
                            {
                                echo '<li>'.$page_item.'</li>';
                            }
                        }
                    }
                ?>
            </ul>
        </div>
        <!-- end of pagination bar -->

    </div>   
    <?php endif; ?>
</div>

<!--stripe modal -->
<div id="stripe-payment-modal" class="stripe-modal">

  <!-- Modal content -->
  <div class="stripe-modal-content">    
    <div class="stripe-modal-main">
        <form class="stripe-modal-form" id="moving-payment-form" method="POST">
            <p class="checkout-title"><?php _e('Checkout','moving_platform'); ?></p>
            <p class="checkout-product-info"><?php _e('Contact of','moving_platform'); ?> <span class="checkout-product-name" id="checkout-product-name"></span> | <?php _e('Price: ','moving_platform') ?><?php echo $admin_data->getValue('moving_request_price') ?>€</p>
            <input type="text" class="stripe-payment-fields" placeholder="<?php _e('Billing name','moving_platform'); ?>" id="billing_name" name="billing_name">
            
            <!-- use to show error -->
            <span></span>

            <input type="hidden" name="action" id="action" value="single_checkout">       
            <input type="hidden" name="single_checkout_nonce" id="single_checkout_nonce" value="<?php echo wp_create_nonce('single_checkout_nonce'); ?>">                                        

            <input type="hidden" name="pay_request_id" id="pay_request_id" value="">  
            <input type="hidden" name="pay_request_price" id="pay_request_price" value="<?php echo $admin_data->getValue('moving_request_price') ?>">          
          
           
            <div class="stripe-card-elements-container">
                <!-- A Stripe Element will be inserted here. -->
                <div id="custom-stripe-cardNum" class="custom-stripe-card-elements cardNum"></div>  
                <div id="custom-stripe-expiry" class="custom-stripe-card-elements cardExpiry"></div>  
                <div id="custom-stripe-cvc" class="custom-stripe-card-elements cardCVC"></div>  
                <!-- A Stripe Element will be inserted here. -->               
            </div> 
            
            <!-- Stripe card error -->               
            <div id="stripe-card-errors"></div>

            <button type="submit" class="btn btn-submit payment_submit" name="payment_submit" id="payment_submit"><?php _e('Confirm Payment','moving_platform'); ?></button>
            <div class="close-modal-area">
                <button type="button" id="stripe-close" class="stripe-close-btn"><?php _e('Close','moving_platform'); ?></button>
            </div>

        </form>

    </div>
  </div>
<?php else: ?>
    <h3 style="margin-top:50px;margin-bottom:100px;"><?php _e('Only the service provider can see the request list','moving_platform'); ?></h3>
<?php endif; ?>
</div>
<!--end stripe modal -->
<?php
get_footer();
