<script type="text/template" id="ae-project-loop">

    <div class="project-list-wrap">
        <h2 class="project-list-title">
            <a href="{{= permalink }}" class="secondary-color" title="{{= post_title }}">{{= post_title }}</a>
        </h2>
        <div class="project-list-info">
            <span>
                <i class="fa fa-clock-o fontawesome-icon-custom"></i>
                {{= human_readable_time}}
            </span>
            <span>
                <i class="fa fa-paper-plane fontawesome-icon-custom"></i>
                {{= text_total_bid}}
            </span>
            <# if(text_country != '') {#>
                <span>
                    <i class="fa fa-map-marker fontawesome-icon-custom"></i>
                    {{= text_country}}
                </span>
            <# } #>
           
        </div>
        <div class="project-list-desc">
            <p>{{= post_content_trim}}</p>
        </div>
        {{= list_skills}}
        <div class="custom-project-budget-bid">                        
            <p><i class="fa fa-credit-card"></i> {{= budget}}</p>
            <a href="<?php the_permalink(); ?>">Send Proposal</a>
        </div>
    </div>

    <div class="project-list-custom-info">
        <div class="custom-white-space-top hidden-sm hidden-xs"></div>
        <div class="custom-white-space-bottom hidden-sm hidden-xs"></div>
        <div class="custom-info-employer">
            <a href="{{= author_url}}"> {{= custom_avatar_project}} </a>
            <p><a href="{{= author_url}}"> {{= author_name}} </a></p>
            <span class="project-posted-text">{{= project_posted}} Project posted</span>
        </div>
       
        
    </div>

</script>