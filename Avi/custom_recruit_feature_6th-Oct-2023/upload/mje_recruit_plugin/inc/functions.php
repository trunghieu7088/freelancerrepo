<?php
function request_item_js_template(){ ?>
    <script type="text/template" id="mjob-item-loop">
        <!-- <div class="test">
           <div class="full  ">
                <div class="full col-request-title">
                    <p><a class="request-loop-title" href="{{= permalink }}">{{=post_title}}</a></p>
                </div>

                <div class="full row-request-info">
                    <span>{{=date_txt}}</span> |
                    <span>{{=number_offers_txt}} </span>|
                    <span>{{=budget_txt}}</span>
                </div>
                <div class="full request-loop-expert">
                    {{=post_excerpt}}
                    <div class="full request-loop-expert">
                        {{=tag_txt}}
                    </div>
                </div>
        </div>

        </div>-->

         <!-- custom code recruit -->
         <div class="custom-flex-profile">                            
                <div class="recruitList-avatar-area">
                    <img src="{{=recruit_avatar_author}}" class="mje-recruit-avatar">
                </div>  
                <p>{{=recruit_display_name}}</p>              
                <div class="vote">
                    <div class="rate-it star" data-score="{{=rating_score}}"></div>
                    {{=author_rate_text}}                    
                </div>
        </div>
        <div class="full custom-flex-recruit">
            <div class="full col-request-title">
                <p>
                    <a class="request-loop-title" href="{{= permalink }}">{{=post_title}}</a>
                    <span class="custom-text-expire">
                        {{=date_txt}}                                                                      
                    </span>
                </p>
            </div>

           <div class="full request-loop-expert">
                {{=post_excerpt}}              
               <div class="recruit-view-detail-btn">
                    <a href="{{= permalink }}">
                    View Details
                    <i class="fa fa-angle-double-right"></i>                                                           
                    </a>
                </div>

                <div class="full custom-info-recruit">
                    <span><i class="fa fa-credit-card"></i> Budget: {{=budget_txt}}</span>
                    <span><i class="fa fa-briefcase"></i> {{=number_offers_txt}}</span>
                    <span><i class="fa fa-clock-o"></i> {{=time_of_delivery_txt}}</span>
                    <span><i class="fa fa-list"></i> Category: {{=category_name}}  </span>
                </div>
               
               <div class="full request-loop-tag-wrap">{{=tag_txt}}</div>
           </div>           
        </div>
    <!-- end custom code recruit -->
    </script>
<?php }

function count_offers_of_request($request_id){
    global $wpdb;
    $request_id = (int) $request_id;
    $sql =  "SELECT COUNT(*) FROM $wpdb->posts where post_type = 'mje_offer' AND post_parent = $request_id ";
    $number_offers = $wpdb->get_var($sql);

    return (int) $number_offers;
}
if (!function_exists('mje_list_tax_of_request')) {
    /**
     * display html of list skill or category of project
     * @param  int $id project id
     * @param  string $title - title apperance in h3
     * @param  string $slug taxonomy slug
     * @return display list taxonomy of project.
     */
    function mje_list_tax_of_request($id, $title = '', $taxonomy = 'skill', $class = '') {
        $class = 'list-categories';
        if ($class = 'skill') {
            $class = 'list-skill';
        }
        $terms = get_the_terms($id, $taxonomy);
    if ($terms && !is_wp_error($terms)): ?>
        <span class="tag-lable"><?php _e('Tags:','mje_recruit');?></span>
        <div class="list-require-skill-project list-taxonomires list-<?php echo $taxonomy; ?>">
            <?php the_taxonomy_list($taxonomy, '<span class="skill-name-profile">', '</span>');?>
        </div>
        <?php endif;
    }
}
function mre_get_list_tax_of_request($id, $title = '', $taxonomy = 'skill', $class = '') {
    $class = 'list-categories';
    if ($class = 'skill') {
        $class = 'list-skill';
    }
    $terms = get_the_terms($id, $taxonomy);
    ob_start();
    if ($terms && !is_wp_error($terms)): ?>
        <span class="tag-lable"><?php _e('Tags:','mje_recruit');?></span>
        <div class="list-require-skill-project list-taxonomires list-<?php echo $taxonomy; ?>">
        <?php the_taxonomy_list($taxonomy, '<span class="skill-name-profile">', '</span>');?>
        </div>
    <?php endif;
    $b = ob_get_clean();
    return $b;
}