<?php

class Admin_Action
{
    public static $instance;

    function __construct(){        
		       
		$this->init_hook();
	}

    function init_hook()
    {
        add_action( 'add_meta_boxes', array($this,'add_action_dashboard'),999);
        add_action('admin_action_update_ban_list_request',array($this,'update_ban_list_request_action'));        
    }

    public static function get_instance()    
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    function add_action_dashboard()
    {
        add_meta_box( 'action-box', 'Admin Actions',array($this,'display_action_dashboard'), 'moving_request' );
    }

    function display_action_dashboard($post)
    {      
        $user_collection=$this->get_all_users();  
        $ban_list=get_post_meta($post->ID,'ban_list_ids',true);        
        ?>
        <p><?php _e('Hide this request with the below users','moving_platform') ?></p>
        <div style="margin-bottom:100px;">
            <form method="POST" name="ban_user_form" id="ban_user_form">
                <input type="hidden" name="action" value="update_ban_list_request">
                <input type="hidden" name="moving_request_id" value="<?php echo $post->ID; ?>">
                <select multiple placeholder="<?php _e('Type to search and ban user','moving_platform'); ?>" style="width:100%;" id="ban_user_list" name="ban_user_list[]">
                    <option value=""><?php _e('Type to search and ban user','moving_platform'); ?></option>
                    <?php if($user_collection): ?>
                        <?php foreach($user_collection as $user_item): ?>

                            <?php if($user_item->ID != $post->post_author): // do not show the owner of request ?>
                                <option <?php if(!empty($ban_list) && in_array($user_item->ID,$ban_list)) echo 'selected'; ?> value="<?php echo $user_item->ID; ?>">
                                    <?php echo $user_item->user_login.' | '.$user_item->user_email; ?>
                                </option>       
                            <?php endif; ?>  

                        <?php endforeach; ?>                      
                    <?php endif; ?>               
                </select>
                <input style="margin-top:15px;" type="submit" value="<?php _e('Hide','moving_platform'); ?>" class="button button-primary">
            </form>
            
            <?php if(isset($_GET['ban_message']) && $_GET['ban_message']=='true'): ?>
                    
                <div style="border-radius:5px;margin-top:20px;padding:10px;font-size:14px;color:#ffffff;background-color:#355E3B;border:1px solid #000000;">
                    <?php _e('Updated successfully !','moving_platform'); ?>
                </div>
                  
            <?php endif; ?>
        </div>

        <?php        
    }

    function get_all_users()
    {
        $user_query = new WP_User_Query( array( 'number' => -1 , 'role__not_in' => array( 'administrator' )) );
        $user_collection=$user_query->get_results();
        return $user_collection;
    }

    function update_ban_list_request_action()
    {
        $request_id=$_POST['moving_request_id']; 
        if(isset($_POST['ban_user_list']) && !empty($_POST['ban_user_list']))
        {           
            $ban_list=$_POST['ban_user_list'];    
            foreach($ban_list as $ban_user)
            {
                $hidden_post_list=get_user_meta($ban_user,'hidden_post_list',true);
                if(!$hidden_post_list)
                {
                    $hidden_post_list=array();
                }                
                array_push($hidden_post_list,$request_id); // add item
                $handled_hidden_list=array_unique($hidden_post_list);//remove duplicate item
                
                //update hidden posts list to user meta.
                update_user_meta($ban_user,'hidden_post_list',$handled_hidden_list);
            }
        }      
        else
        {
            $ban_list=array();
        }

        //update hidden user list to the posts.
        update_post_meta($request_id,'ban_list_ids',$ban_list); 
        wp_redirect(admin_url("post.php?post=$request_id&action=edit&ban_message=true"));
        exit();
    }
}
new Admin_Action();