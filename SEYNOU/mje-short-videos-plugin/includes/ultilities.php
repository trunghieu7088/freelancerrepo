<?php
function get_short_video_mjob($mjob_id)
{
    $short_video=array();
    $short_video_id=get_post_meta($mjob_id,'short_video_id',true);
    
    $mime_type='';
    $url='';
    if($short_video_id)
    {
        $type=get_post_meta($short_video_id,'short_video_type',true);
        if($type=='upload')
        {
            $attach_video=get_attached_media('video',$short_video_id);
            if($attach_video)
            {
                foreach($attach_video as $video_item)
                {
                    $url=wp_get_attachment_url($video_item->ID);   
                    $mime_type=$video_item->post_mime_type;                      
                }
            }
            
            
        }
        if($type=='youtube')
        {
            $url=get_post_meta($short_video_id,'youtube_video_id',true);            
        }
        $short_video=array('type'=>$type,'url'=>$url,'mime_type'=>$mime_type);        
    }
    return $short_video;
}

//get avatar of video owner
function short_video_get_owner_info($user_id)
{
    $user_info=get_user_by('ID',$user_id);
    if(!$user_info)
    return null;

    $avatar = get_user_meta($user_id, 'et_avatar_url', true);
    if (!$avatar)
    {
        $default_avatar = ae_get_option('default_avatar', '');   
        if($default_avatar)          
        {
            $avatar = $default_avatar['thumbnail'][0];  
        }        
    }
    
    if (!$avatar)
    {
        $avatar = 'https://0.gravatar.com/avatar/6cf99d904a8800a571681d5eb9618d99?s=35&d=mm&r=G';
    }  
    $user_profile_id=get_user_meta($user_id,'user_profile_id',true); 
    //necessary data
    $user_info->ownerAvatarURL=$avatar;    
    $user_info->ownerProfileURL=get_author_posts_url($user_id);

    $country_term=get_the_terms($user_profile_id,'country');
    if($country_term && !is_wp_error($country_term))
    {
        $user_info->ownerLocation=$country_term[0]->name;
    }
    else
    {
        $user_info->ownerLocation='None';
    }

    $language=wp_get_post_terms($user_profile_id,'language',array('fields'=>'names'));
    if($language && !is_wp_error($language))
    {
        $user_info->ownerLanguage=implode(' ',$language);
    }
    else
    {
        $user_info->ownerLanguage='None';
    }
    $user_info->ownerRating=mje_get_total_reviews_by_user($user_id);

    return $user_info; 
}

function get_service_list_short_video($video_id)
{
    $service_list=array();
    $service_ids=get_post_meta($video_id,'service_list',true);
    if($service_ids && !empty($service_ids))
    {
        foreach($service_ids as $service)
        {
            $service_list[]=array(  'service_id'=>$service,
                                    'service_link'=>get_post_field('guid',$service),
                                    'service_title'=>get_post_field('post_title',$service),
                                );
        }
    }
    return $service_list;
}
//get short video url, type and mime type
function get_short_video_url($video_id)
{
    $type=get_post_meta($video_id,'short_video_type',true);
    $mime_type='';
    if($type=='upload')
    {
        $attach_video=get_attached_media('video',$video_id);
        if($attach_video)
        {
            foreach($attach_video as $video_item)
            {
                $url=wp_get_attachment_url($video_item->ID);   
                $mime_type=$video_item->post_mime_type;                      
            }
        }
        
    }
    if($type=='youtube')
    {
        $url=get_post_meta($video_id,'youtube_video_id',true);            
    }
   
    return (object)array('type'=>$type,'url'=>$url,'mime_type'=>$mime_type);
}
