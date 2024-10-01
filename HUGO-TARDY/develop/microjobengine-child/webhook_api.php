<?php
function get_mjob_post_info($mjob_id)
{
    $info_collection=array();
    $mjob_item=get_post($mjob_id);
    if($mjob_item)
    {
        //price & time
        $info_collection['mjob_price']=get_post_meta($mjob_item->ID,'et_budget',true);
        $info_collection['time_delivery']=get_post_meta($mjob_item->ID,'time_delivery',true);
        
        //shipping service
        $info_collection['isShippingService']=get_post_meta($mjob_item->ID,'provide_shipping_service',true) ? : false;   
        $info_collection['shipping_cost'] = get_post_meta($mjob_item->ID, 'shipping_cost', true) ? : 0;

        //youtube link
        $info_collection['youtube_link']= get_post_meta($mjob_item->ID, 'video_meta', true) ? : false;

        //local video
        $info_collection['video_link']='';
        $info_collection['video_mime_type']='';
        $attached_video=get_attached_media('video',$mjob_item->ID);        
        if($attached_video)
        {
            foreach($attached_video as $video_item)
            {
                $info_collection['video_link']=wp_get_attachment_url($video_item->ID);  
                $info_collection['video_mime_type']=$video_item->post_mime_type;     
            }
        }

        //total sale
        $info_collection['total_sale']=get_post_meta($mjob_item->ID, 'et_total_sales', true) ? : 0;

        //opening message
        $info_collection['opening_message']=get_post_meta($mjob_item->ID, 'opening_message', true) ? : false;

        //rating score
        $info_collection['rating_score']=get_post_meta($mjob_item->ID, 'rating_score', true) ? : 0;

        //total views
        $info_collection['view_count']=get_post_meta($mjob_item->ID, 'view_count', true) ? : 0;
        
        //total comment
        $args_cmts = array(
            'type'   => 'mjob_review',
            'status' => 'approve',     
            'post_id' => $mjob_id,          
            'number' =>'',     
            'count'=>true,
        );
        $info_collection['total_comments']=get_comments($args_cmts);

        //featured images return as array
        $info_collection['featured_images']=array();
        $et_images=get_post_meta($mjob_item->ID, 'et_carousels', true) ? : false;
        if($et_images)
        {
            foreach($et_images as $image_item)
            {
                $info_collection['featured_images'][]=wp_get_attachment_image_url($image_item,'full');
            }
        }

    }

    return $info_collection;
}