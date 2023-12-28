<?php

//create menu shortcode
function custom_menu_subscription_admin() {
    ob_start();
    ?>
        <div class="manage-subscription-admin-menu">
                <a href="<?php echo site_url('manage-subscription/?task=paypalinfo'); ?>">Paypal API information</a>                
                <a href="<?php echo site_url('manage-subscription/?task=subscriptionmanage'); ?>">Manage Plans</a>                
                <a href="<?php echo site_url('manage-subscription/?task=customcreateplan'); ?>">Create plans</a>                                
                <a href="<?php echo site_url('manage-subscription/?task=subscriptionlist'); ?>">Manage subscriptions</a>                                
                <a href="<?php echo site_url('manage-subscription/?task=adminaccess'); ?>">Admin Access</a>                                
               <!-- <a href="<?php echo site_url('manage-subscription/?task=manageproducts'); ?>">Manage Products</a> -->
       </div>
    <?php
    return ob_get_clean();
}
add_shortcode('subscription_menu_admin', 'custom_menu_subscription_admin');


//handle title each pages

add_filter('wp_title','filter_title_subscription',999,1);
function filter_title_subscription($title)
{
    if(is_page('manage-subscription'))
    {
        if(isset($_GET['task']))
        {
            $task=$_GET['task'];
            switch ($task)
            {
                case 'paypalinfo':
                    $title='Paypal Info';
                    break;
                    
                case 'manageproducts':
                    $title='Manage products';
                    break;

                case 'subscriptionmanage':
                    $title='Manage subscriptions Plan';
                    break;
                
                case 'customcreateplan':
                    $title='Create subscription plans';
                    break;
                case 'subscriptionlist':
                    $title='Manage subscriptions';
                    break;
            }          
        }
        else
        {
            $title='Manage Subscription';
        }
        $title.=' - '.get_bloginfo( 'name' );
    }
    if(is_page('mysubscription'))
    {
        $title='My Subscription'.' - '.get_bloginfo( 'name' );
    }
    if(is_page('subscription'))
    {
        $title='Pricing Plans'.' - '.get_bloginfo( 'name' );
    }
   
    return $title;
};

//layout to manage admin who can access

function adminaccess_shortcode()
{
    ob_start();
    $admin_subscription_list=get_option('admin_subscription_list',false);
    $check_current_user=wp_get_current_user();    
    ?>
     <p class="admin-subscription-title">Manage Admin Access</p>
        <div class="row">
            <div class="col-md-12">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Action</th>
                        </tr>                    
                    </thead>  
                    <tbody>
                        <?php if($admin_subscription_list): ?>
                            <?php foreach($admin_subscription_list as $admin): ?>
                                <?php $admin_item=get_user_by('email',$admin); ?>
                                 <tr>
                                    <td><?php echo $admin_item->user_email; ?></td>
                                    <td><?php echo $admin_item->display_name; ?></td>
                                    <td><?php echo $admin_item->user_login; ?></td>
                                    <td><button data-email="<?php echo $admin_item->user_email; ?>" class="btn btn-danger custom-btn-delete-admin-subscription">Remove</button></td>
                                 </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>                       
                    </tbody>              
                </table>
            </div>

            <div class="col-md-12">
                <h4 class="text-center">Set admin who can access</h4>
                <form name="setAdminSubscription" id="setAdminSubscription" action="">                    
                    <input type="hidden" id="action" name="action" value="set_admin_access_subscription">    
                    <div class="form-group">
                        <label for="admin_email">Admin Email</label>
                        <input type="text" class="form-control" id="admin_email" name="admin_email" value="" required>     
                    </div>  
                    <button type="submit" class="btn btn-primary">Add</button> 
                </form>
            </div>
        </div>
    <?php 
    return ob_get_clean();
}
add_shortcode('adminaccess', 'adminaccess_shortcode');


//layout insert paypal info
function custom_paypal_info_shortcode() {
    ob_start();
    $client_id=get_option('custom_paypal_client_id','');
    $secret_key=get_option('custom_paypal_secret','');
    ?>
        <p class="admin-subscription-title">Paypal API Information</p>
        <div class="row">
            <form name="savePaypalInfo" id="savePaypalInfo" action="">    
            <input type="hidden" id="paypal_info_nonce" name="paypal_info_nonce" value="<?php echo wp_create_nonce('paypal_info_nonce'); ?>">                             
            <input type="hidden" id="action" name="action" value="update_paypal_info">                             
                <div class="form-group">
                    <label for="client_id">Client ID</label>
                    <input type="text" class="form-control" id="client_id" name="client_id" value="<?php echo $client_id; ?>" required>     
                </div>   
                
                <div class="form-group">
                    <label for="secret_key">Secret key</label>
                    <input type="text" class="form-control" id="secret_key" name="secret_key" value="<?php echo $secret_key; ?>" required>     
                </div>   
                <button type="submit" class="btn btn-primary">UPDATE</button>
            </form>
       </div>
    <?php
    return ob_get_clean();
}
add_shortcode('paypalinfo', 'custom_paypal_info_shortcode');


//layout manage plans
function custom_paypal_subscription_manage()
{
    
        ob_start();
        $list_plans=custom_get_list_plans();   
        $list_plans_info=$list_plans['plans'];  
        $list_wp_plan= $list_plans['wp_plan'];
        $free_plan_list=get_all_free_plans();
        echo '<script type="text/template" id="subscriptionplansjson">' . json_encode($list_wp_plan) . '</script>';     
        ?>
        <p class="admin-subscription-title">Manage Subscription Plans</p>        
        <div class="row">           
            <!-- <pre>
                <?php
                // print_r($list_plans_info);
                ?>
            </pre> -->
            <div class="table-responsive">          
                <table class="table table-striped">
                    <tr>
                        <!-- <th>ID</th> -->
                        <th>Name</th>                                         
                        <th>Status</th>                                    
                        <th>Currency</th>   
                        <th>Price / month</th> 
                        <th>Action</th>  
                    </tr>
                  
                    <?php foreach($list_plans_info as $plan) : ?>
                        <?php 
                        if($plan['wp_plan_status']=='publish')
                         { 
                            $wp_status_plan='Active' ; 
                            $label_text='text-success bg-success';
                         } 
                         else                         
                         {
                            $wp_status_plan='Disabled';
                            $label_text='text-warning  bg-warning';
                         }
                          
                          ?>
                        <tr>
                            <!-- <td><?php //echo $plan['id']; ?></td> -->
                            <td><a href="javascript:void(0)" class="modalPlan" data-paypal-id="<?php echo $plan['id']; ?>"><?php echo $plan['name']; ?></a></td>                            
                            <td class="<?php echo $label_text; ?>"><?php echo $wp_status_plan; ?></td>
                            <td><?php echo $plan['billing_cycles'][0]['pricing_scheme']['fixed_price']['currency_code']; ?></td>
                            <td><?php echo $plan['billing_cycles'][0]['pricing_scheme']['fixed_price']['value']; ?></td>
                            <td>
                                <?php //if($plan['status']=='ACTIVE') : ?>                                    
                                <?php if($plan['wp_plan_status']=='publish') : ?>                                                                        
                                    <button class="btn btn-warning deactivate-subscription-btn setStatus-subscription-btn" data-plan-id="<?php echo $plan['id']; ?>" data-plan-status="archive">Deactivate</button>
                                <?php else: ?>
                                    <button class="btn btn-success deactivate-subscription-btn setStatus-subscription-btn" data-plan-id="<?php echo $plan['id']; ?>" data-plan-status="publish">Activate</button>
                                <?php endif; ?>
                                    <button class="btn btn-danger deactivate-subscription-btn real-delete-plan-btn"  data-plan-id="<?php echo $plan['id']; ?>">Delete</button>
                                    <a class="btn btn-default" href="<?php echo site_url('manage-subscription/?task=customcreateplan&action=edit&plan='.$plan['id']); ?>">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            <h4 class="text-center">Manage Free Plans</h4>
            <div class="table-responsive">          
                <table class="table table-striped">
                    <tr>
                        <!-- <th>ID</th> -->
                        <th>Name</th>                                         
                        <th>Status</th>                                    
                        <th>Currency</th>   
                        <th>Price / month</th> 
                        <th>Action</th>  
                    </tr>
                     <?php if(!empty($free_plan_list)): ?>
                     <?php foreach($free_plan_list as $free_plan): ?>                     
                      <?php  if($free_plan->plan_status=='Active')
                         {                            
                            $label_text_free='text-success bg-success';
                         } 
                         else                         
                         {
                            
                            $label_text_free='text-warning  bg-warning';
                         }
                         ?>
                        <tr>                        
                            <td><a href="javascript:void(0)" class="modalPlan" data-paypal-id="<?php echo $free_plan->paypal_plan_id; ?>"><?php echo $free_plan->title; ?></a></td>
                            <td class="<?php echo $label_text_free ?>"><?php echo $free_plan->plan_status; ?></td>
                            <td>USD</td>
                            <td>0</td>
                            <td>
                                <?php //if($plan['status']=='ACTIVE') : ?>                                    
                                <?php if($free_plan->plan_status=='Active') : ?>                                                                        
                                    <button class="btn btn-warning deactivate-subscription-btn setStatus-subscription-btn" data-plan-id="<?php echo $free_plan->paypal_plan_id; ?>" data-plan-status="archive">Deactivate</button>
                                <?php else: ?>
                                    <button class="btn btn-success deactivate-subscription-btn setStatus-subscription-btn" data-plan-id="<?php echo $free_plan->paypal_plan_id; ?>" data-plan-status="publish">Activate</button>
                                <?php endif; ?>
                                    <button class="btn btn-danger deactivate-subscription-btn real-delete-plan-btn"  data-plan-id="<?php echo $free_plan->paypal_plan_id; ?>">Delete</button>
                                    <a class="btn btn-default" href="<?php echo site_url('manage-subscription/?task=customcreateplan&action=edit&plan='.$free_plan->paypal_plan_id); ?>">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </table>
                
                
        </div>
        

<!-- Modal detail plan -->
<div id="subscriptionPlanModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Modal Header</h4>
      </div>
      <div class="modal-body">
          <div class="admin-subscription-modal">

          </div>
      </div>
      <div class="modal-footer">        
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>
<!-- end Modal detail plan -->

<!-- Modal confirm de or reactivate plan -->
<div id="confirmActiveplan" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Confirm ?</h4>
      </div>
      <div class="modal-body">
          <div class="active-modal-content">

          </div>
      </div>
      <div class="modal-footer">
        <button type="button" id="btn-actionPlan" data-paypal-id="" class="btn btn-warning">Deactivate</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>
<!-- end  -->

<!-- Modal delete plan -->
<div id="deletePlanModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Confirm ?</h4>
      </div>
      <div class="modal-body">
          <div class="active-modal-content">

          </div>
      </div>
      <div class="modal-footer">
        <button type="button" id="btn-deletePlan" data-paypal-id="" class="btn btn-danger">Delete</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>
<!-- end  -->

        <?php
        return ob_get_clean();
}
add_shortcode('subscriptionmanage','custom_paypal_subscription_manage');


//layout create plans
function custom_create_plan_shortcode() {
    ob_start();
    if(isset($_GET['action']) && $_GET['action']=='edit' && isset($_GET['plan']))
    {
        $wp_edit_plan=find_plan_by_paypal_plan_id($_GET['plan']);
        if($wp_edit_plan)
        {
            $is_editable=true;
        }
        else
        {
            $is_editable=false;
        }
    }
    else
    {
        $is_editable=false;
    }
    //get info to show
    if($is_editable==true)
    {
        $edit_plan_name=$wp_edit_plan->post_title;
        $edit_short_description=get_post_meta($wp_edit_plan->ID,'plan_short_description',true);
        $edit_plan_subtitle=get_post_meta($wp_edit_plan->ID,'plan_subtitle',true);
        $edit_transaction_fee=get_post_meta($wp_edit_plan->ID,'plan_transaction_fee_percent',true);
        $edit_plan_number_posts=get_post_meta($wp_edit_plan->ID,'plan_number_posts',true);        
        $edit_plan_price=get_post_meta($wp_edit_plan->ID,'price_per_month',true);        
        for($advertise_text = 1; $advertise_text <= 6; $advertise_text++)
            {
                $advertise_text_display[]=get_post_meta($wp_edit_plan->ID,'title_advertisement'.$advertise_text,true) ? get_post_meta($wp_edit_plan->ID,'title_advertisement'.$advertise_text,true) : '';                
            }
    }
    ?>
        <p class="admin-subscription-title">Create Subscription Plans</p>
        <div class="row">
                              
            <form name="createSubscriptionPlanForm" id="createSubscriptionPlanForm" action="">   
                
            <input type="hidden" id="create_plan_paypal_nonce" name="create_plan_paypal_nonce" value="<?php echo wp_create_nonce('create_plan_paypal_nonce'); ?>">                             
            
                <?php if($is_editable==true): ?>
                    <input type="hidden" id="action" name="action" value="edit_subscription_plans_paypal">
                    <input type="hidden" id="wp_plan_id_edit" name="wp_plan_id_edit" value="<?php echo $wp_edit_plan->ID; ?>">
                <?php else: ?>
                    <input type="hidden" id="action" name="action" value="create_subscription_plans_paypal">
                <?php endif; ?>
                
            
            <!-- default information for product and plan -->                        
            
            <!-- product -->
            <input type="hidden" id="product_type" name="product_type" value="SERVICE">     
            <input type="hidden" id="product_category" name="product_category" value="SOFTWARE">     
           
            <!-- plan -->
            <input type="hidden" id="plan_status" name="plan_status" value="ACTIVE">
            <input type="hidden" id="plan_currency" name="plan_currency" value="USD">
            <input type="hidden" id="plan_tenure_type" name="plan_tenure_type" value="REGULAR">
            <input type="hidden" id="plan_sequence" name="plan_sequence" value="1">
            <!-- end default information for product  and plan  -->
                <div class="form-group">
                    <label for="plan_name">Plan Name</label>
                    <input type="text" class="form-control" placeholder="Pro Plan" id="plan_name" name="plan_name" value="<?php if($is_editable) echo $edit_plan_name; ?>" required>     
                </div>   
                
                <div class="form-group">
                    <label for="plan_description">Short Description</label>
                    <input type="text" class="form-control" placeholder="Experience excellent with our pro plan" id="plan_description" name="plan_description" value="<?php if($is_editable) echo $edit_short_description; ?>" required>     
                </div> 

                <div class="form-group">
                    <label for="plan_subtitle">Subtitle</label>
                    <input type="text" class="form-control" placeholder="No credit card required or Renew monthly" id="plan_subtitle" name="plan_subtitle" value="<?php if($is_editable) echo $edit_plan_subtitle; ?>" required>     
                </div> 
                
                <div class="form-group">
                    <label for="plan_price">Price ( USD )</label>
                    <input type="number" class="form-control" placeholder="1" id="plan_price" name="plan_price" value="<?php if($is_editable) echo $edit_plan_price; ?>" required>     
                </div>

                
                <div class="form-group">
                    <label for="number_posts">Number of posts</label>
                    <input type="number" min="1" class="form-control" placeholder="1" id="number_posts" name="number_posts" value="<?php if($is_editable) echo $edit_plan_number_posts; ?>" required>     
                </div>

                <div class="form-group">
                    <label for="transaction_fee">Transaction fee (%)</label>
                    <input type="number" class="form-control" placeholder="0" id="transaction_fee" name="transaction_fee" value="<?php if($is_editable) echo $edit_transaction_fee; ?>" required>     
                </div>             

                <div class="form-group title-advertisement">
                    <label>Title Advertisement ( optional )</label>
                    <input type="text" class="form-control" placeholder="Up to % expertise listings" id="title_advertisement1" name="title_advertisement1" value="<?php if($is_editable) echo $advertise_text_display[0]; ?>">     
                    <input type="text" class="form-control" placeholder="% transaction fee" id="title_advertisement2" name="title_advertisement2" value="<?php if($is_editable) echo $advertise_text_display[1]; ?>">     
                    <input type="text" class="form-control" placeholder="Live chat with client" id="title_advertisement3" name="title_advertisement3" value="<?php if($is_editable) echo $advertise_text_display[2]; ?>">     
                    <input type="text" class="form-control" placeholder="Custom offer" id="title_advertisement4" name="title_advertisement4" value="<?php if($is_editable) echo $advertise_text_display[3]; ?>">     
                    <input type="text" class="form-control" placeholder="Gigs extra such as online course and E-books" id="title_advertisement5" name="title_advertisement5" value="<?php if($is_editable) echo $advertise_text_display[4]; ?>">     
                    <input type="text" class="form-control" placeholder="Best benefit" id="title_advertisement6" name="title_advertisement6" value="<?php if($is_editable) echo $advertise_text_display[5]; ?>">     

                </div>

                <?php if($is_editable==true): ?>
                    <button type="submit" class="btn btn-primary">Update Plan</button>
                <?php else: ?>
                    <button type="submit" class="btn btn-primary">Create Plan</button>
                <?php endif; ?>
            </form>      
       </div>
    <?php
    return ob_get_clean();
}
add_shortcode('customcreateplan', 'custom_create_plan_shortcode');


//layout manage subscriptions
add_shortcode('subscriptionlist','custom_manage_subscription_list_shortcode');

function custom_manage_subscription_list_shortcode()
{
    ob_start();
    ?>
    <p class="admin-subscription-title">Manage Subscriptions</p>
    <div class="row">           
            <table id="subscriptionTable" class="table table-bordered table-hover table-striped">
                <thead>
                    <tr>    
                        <th></th> 
                        <th>User</th>           
                        <th>Plan</th>                       
                        <th>Status</th>
                        <th>Price($)</th>                        
                        <th>Subscription Date (Y-M-D)</th>                        
                    </tr>
                </thead>
            </table>        
    </div>

<!-- Modal detail plan -->
<div id="subscriptionDetailModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Payment Info From Paypal</h4>
      </div>
      <div class="modal-body">
          <div class="admin-subscription-modal">

          </div>
      </div>
      <div class="modal-footer">        
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>
<!-- end Modal detail plan -->

    <?php
    return ob_get_clean();
}

//layout manage products
//will delete in the future or comment out
function custom_manage_products_shortcode()
{
    ob_start();
    $product_lists=custom_get_list_products();
    ?>
    <p class="admin-subscription-title">Manage Products</p>
    <div class="row">
      <pre>
            <?php var_dump($product_lists); ?>
        </pre> 
    </div>
   
    <?php
    return ob_get_clean();
}
add_shortcode('manageproducts', 'custom_manage_products_shortcode');

