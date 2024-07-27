<?php
add_action('wp_head','load_custom_meta_sharing',99);

function load_custom_meta_sharing()
{
    if(is_page_template('custom-sharing-social-page.php'))
    {
        if(isset($_GET['id_content']) && $_GET['id_content']!='')
        {
            $custom_meta_title=get_post_meta($_GET['id_content'],'custom_meta_title',true) ? get_post_meta($_GET['id_content'],'custom_meta_title',true) : get_bloginfo('name');
            $custom_meta_description=get_post_meta($_GET['id_content'],'custom_meta_description',true) ? get_post_meta($_GET['id_content'],'custom_meta_description',true) : get_bloginfo('description');
            $media_type=get_post_meta($_GET['id_content'],'custom_attachment_type',true);

            //detect if that is an image
            if($media_type=='portfolio_item')
            {
                $og_image= wp_get_attachment_image_url( $_GET['id_content'], 'large');
            }
            else
            {   //if that is video or audio --> use the image of website
                $og_image='https://artistin.fr/wp-content/uploads/2023/12/cropped-fresque-murale-paint-color-scaled-1.webp';
            }

            echo '<meta property="og:type" content="website" />';
            echo '<meta property="og:title" content="'.$custom_meta_title.'" />';
            echo '<meta property="og:description" content="'.$custom_meta_description.'" />';
            echo '<meta property="og:image" content="'.$og_image.'" />';
        }
        else
        {
            //meta Facebook va Twitter X
            echo '<meta property="og:url" content="'.site_url().'" />';            
            echo '<meta property="og:type" content="website" />';
            echo '<meta property="og:title" content="'.get_bloginfo('name').'" />';
            echo '<meta property="og:description" content="'.get_bloginfo('description').'" />';
            echo '<meta property="og:image" content="https://artistin.fr/wp-content/uploads/2023/12/cropped-fresque-murale-paint-color-scaled-1.webp" />';
           
        }
        
      
    }
}

function display_sharing_social_buttons($id_element)
{
    //sharing: Facebook, Twitter X , Instagram, Pinterest, LinkedIn
    $img_path_link=get_stylesheet_directory_uri().'/assets/img/social_icons/';
    $sharing_collection_imgs=array(
                                'FB'=>$img_path_link.'facebook.png',
                                'X'=>$img_path_link.'twitter.png',
                                'Instagram'=>$img_path_link.'instagram.png',
                                'Pinterest'=>$img_path_link.'pinterest.png',
                                'Linkedin'=>$img_path_link.'linkedin.png',
                            );
       
    $sharing_link=site_url('/social-sharing/').'?id_content='.$id_element;
    
    $FB_btn='<a class="social-sharing-icon-a" href="https://www.facebook.com/sharer/sharer.php?u='.$sharing_link.'" target="_blank">';            
    $FB_btn.=set_up_image_social($sharing_collection_imgs['FB']);
    $FB_btn.='</a>';

    $X_btn='<a class="social-sharing-icon-a" href="https://twitter.com/intent/tweet?url='.$sharing_link.'" target="_blank">';            
    $X_btn.=set_up_image_social($sharing_collection_imgs['X']);
    $X_btn.='</a>';


    $Instagram_btn='<a class="social-sharing-icon-a" href="https://instagram.com" target="_blank">';            
    $Instagram_btn.=set_up_image_social($sharing_collection_imgs['Instagram']);
    $Instagram_btn.='</a>';

    $linkedin_sharing_platform_url='https://www.linkedin.com/shareArticle?mini=true&url=';    
    $Linkedin_btn='<a class="social-sharing-icon-a linkedin-sharing-button" data-linkedin-platform-url="'.$linkedin_sharing_platform_url.'" data-url-to-share="'.$sharing_link.'" href="https://linkedin.com">';            
    $Linkedin_btn.=set_up_image_social($sharing_collection_imgs['Linkedin']);
    $Linkedin_btn.='</a>';

    $pinterest_sharing_platform_url='https://www.pinterest.com/pin/create/button/?url=';   
    $Pinterest_btn='<a class="social-sharing-icon-a pinterest-sharing-button" data-pinterest-platform-url="'.$pinterest_sharing_platform_url.'" data-url-to-share="'.$sharing_link.'" href="https://pinterest.com" target="_blank">';            
    $Pinterest_btn.=set_up_image_social($sharing_collection_imgs['Pinterest']);
    $Pinterest_btn.='</a>';
    

    

    $btn_wrapper='<div class="custom-sharing-social-icon-wrapper">';
        $btn_wrapper.='<p class="sharing-title">Sharing : </p>';
        $btn_wrapper.=$FB_btn;
        $btn_wrapper.=$X_btn;
        $btn_wrapper.=$Instagram_btn;
        $btn_wrapper.=$Linkedin_btn;
        $btn_wrapper.=$Pinterest_btn;
    $btn_wrapper.='</div>';
    return $btn_wrapper;
}


function set_up_image_social($url_image='',$custom_class='')
{
    $image_object='<img src="'.$url_image.'" class="sharing-img-custom-icon ' .$custom_class.'">';
    return $image_object;
}