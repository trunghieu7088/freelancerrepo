<script type="text/template" id="ae-profile-loop">

    <div class="profile-list-wrap">
        <a class="profile-list-avatar" href="{{= author_link }}">
            {{= et_avatar}}
        </a>
        <h2 class="profile-list-title">
            <a href="{{= author_link }}">
                {{= author_name }}

                  <# if( validation_item != false ){  #>
                    {{= validation_item }}
                <# } #>

            </a>
        </h2>
        <p class="profile-list-subtitle">{{= et_professional_title }}</p>

         <div style="padding-left:80px;margin-top:5px;">            
             <p style="display:inline-block;font-size:16px;font-family:arial !important;color:#fff;" class="label label-info">Rank : {{= custom_rank_order}}</p>
         </div>
         
        <div class="profile-list-info">
            <div class="profile-list-detail">
                <span class="rate-it" data-score="{{= rating_score }}"></span>
                <span>{{= experience }}</span>
                <span>{{= project_worked }}</span>
                <# if( hourly_rate_price ){  #>
                <span>{{= hourly_rate_price }}</span>
                <# } #>
                <span style="font-weight: normal;">{{= earned }}</span>
            </div>
            <div class="profile-list-desc">
                {{= excerpt }}
            </div>
        </div>
    </div>

</script>