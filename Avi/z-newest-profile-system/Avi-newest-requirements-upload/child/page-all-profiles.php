<?php
/**
 * Template Name: All Profiles
 */

get_header();
global $profile_item_passed;
//get expertise and languages and countries terms to apply drop down list
$language_terms=get_term_for_filter_profile('language');
$country_terms=get_term_for_filter_profile('country');
$expertise_list=get_expertise_for_filter_profile();


if(isset($_GET['language']) && !empty($_GET['language']))
{
    $language_filter=explode(',',$_GET['language']);
}
else
{
    $language_filter=array();
}

if(isset($_GET['country']) && !empty($_GET['country']))
{
    $country_filter=explode(',',$_GET['country']);
}
else
{
    $country_filter=array();
}

if(isset($_GET['search']) && !empty($_GET['search']))
{
    $search=$_GET['search'];
}
else
{
    $search='';
}

if(isset($_GET['expertise']) && !empty($_GET['expertise']))
{
    $expertise=$_GET['expertise'];
}
else
{
    $expertise='';
}


if(isset($_GET['sortby']) && !empty($_GET['sortby']))
{
    $sortby=$_GET['sortby'];
}
else
{
    $sortby='';
}


$sortbyDropdownList=array('newest'=>'Newest','oldest'=>'Oldest','highrating'=>'High Rating');

$current_page = get_query_var('paged') ? get_query_var('paged') : 1;                            
$profile_list=get_all_profiles($current_page,$language_filter,$country_filter,$expertise,$search,$sortby);


//print("<pre>".print_r($profile_list['profile_list'],true)."</pre>");
/*
$number_found_profiles=$profile_list['found_posts'];
if(!$number_found_profiles || empty($number_found_profiles))
{
    $number_found_profiles=0;
}
*/
?>
 <div id="content" class="search.php">
    <?php // get_template_part('template/content', 'page');?>
    <input type="hidden" name="custom_link_filter" id="custom_link_filter" value="<?php echo get_permalink();  ?>">
    <div class="block-page mjob-container-control search-result">
        <div class="container">
            <h2 class="block-title custom-attribute-profiles">
                   <!-- <p class="block-title-text">
                        <span class="search-result-count">                            
                            <?php // echo $number_found_profiles; ?>                            
                        </span>
                        <span class="search-text-result">All Profiles Page</span>                        
                    </p> -->
            </h2>
            <p class="filter-title">Filter Options</p>
            <div class="row custom-filter-list-area">

                <!-- expertise filter -->
                <div class="col-lg-3 col-md-3 col-sm-12 col-sx-12">
                        <div class="form-group custom-filter-option-profiles">
                            <label>Expertise</label>
                            <select class="custom-profile-filter-select custom-profile-multiple-filter-option" id="select-expertise-profile" name="select-expertise-profile" placeholder="Select category" autocomplete="off">                                        
                                        <option value="">Select category</option>
                                        <?php if(!empty($expertise_list)): ?>
                                            <?php foreach($expertise_list as $expertise_item): ?>

                                                <?php if($expertise== $expertise_item->term_id): ?>
                                                    <option selected value="<?php echo $expertise_item->term_id; ?>">
                                                <?php else: ?>
                                                    <option value="<?php echo $expertise_item->term_id; ?>">
                                                <?php endif; ?>
                                                
                                                    <?php echo $expertise_item->name; ?>                                                   
                                                            
                                                        <?php  $expertise_child=get_child_expertise($expertise_item->term_id); ?>  
                                                        
                                                        <?php if($expertise_child && !is_wp_error($expertise_child)): ?>
                                                                <?php foreach($expertise_child as $child_item): ?>
                                                                    <?php if($child_item->term_id==$expertise): ?>
                                                                        <option selected value="<?php echo $child_item->term_id; ?>">
                                                                    <?php else: ?>
                                                                        <option value="<?php echo $child_item->term_id; ?>">
                                                                    <?php endif; ?>
                                                                    

                                                                        <?php echo '---'.$child_item->name; ?>
                                                                     </option>
                                                                <?php endforeach; ?>

                                                        <?php endif; ?>                                                     

                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                            </select>

                        </div>
                </div>   

                <!-- language filter -->
                <div class="col-lg-3 col-md-3 col-sm-12 col-sx-12">
                     <div class="form-group custom-filter-option-profiles">
                                    <label>Languages</label>
                                    <select class="custom-profile-filter-select custom-profile-multiple-filter-option" multiple id="select-language-profile" name="select-language-profile[]" placeholder="Select language{s}" autocomplete="off">                                        
                                        <?php if(!empty($language_terms)): ?>
                                            <?php foreach($language_terms as $language_term): ?>

                                                <?php if(in_array($language_term->term_id,$language_filter)): ?>
                                                    <option selected value="<?php echo $language_term->term_id; ?>">
                                                <?php else: ?>
                                                    <option value="<?php echo $language_term->term_id; ?>">
                                                <?php endif; ?>

                                                    <?php echo $language_term->name; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                </div>  
                    
                 <!-- country filter -->
                <div class="col-lg-3 col-md-3 col-sm-12 col-sx-12">
                    <div class="form-group custom-filter-option-profiles">
                                    <label>Countries</label>
                                    <select class="custom-profile-filter-select custom-profile-multiple-filter-option" multiple id="select-country-profile" name="select-country-profile[]" placeholder="Select Country{s}" autocomplete="off">                                        
                                        <?php if(!empty($country_terms)): ?>
                                            <?php foreach($country_terms as $country_term): ?>

                                                <?php if(in_array($country_term->term_id,$country_filter)): ?>
                                                    <option selected value="<?php echo $country_term->term_id; ?>">
                                                <?php else: ?>
                                                    <option value="<?php echo $country_term->term_id; ?>">
                                                <?php endif; ?>                                               
                                                    <?php echo $country_term->name; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    
                    </div>
                </div>    

                <!-- sort by -->          
                <div class="col-lg-2 col-md-2 col-sm-12 col-sx-12">
                    <div class="form-group custom-filter-option-profiles">
                            <label>Sort by</label>                            
                            <select class="custom-profile-filter-select custom-profile-multiple-filter-option" id="select-sort-profile" name="select-sort-profile" autocomplete="off">                                        
                                <?php foreach($sortbyDropdownList as $key => $value): ?>
                                     <option <?php if($sortby==$key) echo 'selected'; ?> value="<?php echo $key; ?>">
                                     <?php echo $value; ?>
                                    </option>
                                <?php endforeach; ?>                              
                            </select>
                    </div>
                </div> 
                
                <div class="col-lg-1 col-md-1 col-sm-12 col-sx-12 profile-filter-button-container">
                        <label>Action</label>     
                        <input type="button" class="btn btn-block btn-info" value="Filter" name="profile-filter-button-go" id="profile-filter-button-go">               
                </div>

            </div>

            <div class="row">
                
                <div class="col-lg-12 col-md-12 col-sm-12 col-sx-12 custom-profile-list-block">

                        <div class="block-items no-margin">                          
                            <?php if(isset($profile_list) && is_array($profile_list['profile_list']) && !empty($profile_list['profile_list'])): ?>
                                <?php foreach($profile_list['profile_list'] as $profile_item): ?>
                                    <?php 
                                        $profile_item_passed=$profile_item;
                                        get_template_part('template/custom-profile','card',$profile_item_passed); 
                                    ?>
                                <?php endforeach; ?>
                            <?php else: ?>   
                                <h3>Not found any profiles !</h3>
                            <?php endif; ?>

                        </div>
                        
                       
                        
                </div>

                <div class="row profile-pagination-wrapper">
                        <ul class="pagination custom-profiles-pagination">
                            <?php 
                                $big = 999999999;
                                if(isset($profile_list) && is_array($profile_list['profile_list']) && !empty($profile_list['profile_list']))
                                {
                                    $pagination_list= paginate_links( array(
                                        'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
                                        'total'    => $profile_list['max_num_pages'],
                                        'current'  => max( 1, get_query_var( 'paged' ) ),
                                        'prev_text' => 'Previous',
                                        'next_text' => 'Next',
                                        //'format' => '?paged=%#%',
                                        //'format' => 'page=%#%',
                                        'type'=>'array'
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

            </div>
        </div>
    </div>
 </div>
<?php
get_footer();
?>