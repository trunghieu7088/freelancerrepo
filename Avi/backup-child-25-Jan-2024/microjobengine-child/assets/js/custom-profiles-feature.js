(function ($) {

    $(document).ready(function() 
    {

        let language_Tomselect=new TomSelect("#select-language-profile",{
            created:true,
            maxItems: 3,
        });

        let country_Tomselect=new TomSelect("#select-country-profile",{
            created:true,
            maxItems: 3,
        });

        let expertise_Tomselect=new TomSelect("#select-expertise-profile",{
            created:true,            
        });

        let sort_Tomselect=new TomSelect("#select-sort-profile",{
            created:true,            
        });

        

        $(".custom-profile-filter-select").change(function(){

            let converted_query='';
            let language_query='';
            let country_query='';
            let expertise_query='';
            let sortby_query='';

            let languages_filter=$("#select-language-profile").val();              
            if(languages_filter.length > 0 && Array.isArray(languages_filter))          
            {
                language_query='language='+languages_filter.join(',');
            }
            else
            {
                language_query='';
            }
            
            let countries_filter=$("#select-country-profile").val(); 
            if(countries_filter.length > 0 && Array.isArray(countries_filter))          
            {                
                country_query='country='+countries_filter.join(',');
            }
            else
            {
                country_query='';
            }

            let expertise_filter=$("#select-expertise-profile").val();
            if(expertise_filter)
            {
                expertise_query='expertise='+expertise_filter;
            }
            else
            {
                expertise_query='';
            }

            let sortby_filter=$("#select-sort-profile").val();
            if(sortby_filter)
            {
                sortby_query='sortby='+sortby_filter;
            }
            else
            {
                sortby_query='';
            }

            converted_query='?';

            if(country_query !='')
            {
                converted_query+=country_query;
            }

            if(language_query !='')
            {
                if(converted_query=='?')
                {
                    converted_query+=language_query;
                }
                else
                {
                    converted_query+='&'+language_query;
                }
            }

            if(expertise_query !='')
            {
                if(converted_query=='?')
                {
                    converted_query+=expertise_query;
                }
                else
                {
                    converted_query+='&'+expertise_query;
                }
            }

            if(sortby_query !='')
            {
                if(converted_query=='?')
                {
                    converted_query+=sortby_query;
                }
                else
                {
                    converted_query+='&'+sortby_query;
                }
            }


            let redirect_link=$("#custom_link_filter").val()+converted_query;                  
            window.location.href=redirect_link;            
        });        

    });

  })(jQuery);