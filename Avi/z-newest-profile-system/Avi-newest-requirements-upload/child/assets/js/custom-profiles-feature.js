(function ($) {

    $(document).ready(function() 
    {
        let redirect_link='';

        if($("#select-language-profile").length)
        {
            let language_Tomselect=new TomSelect("#select-language-profile",{
                created:true,
                maxItems: 2,
                maxOptions: null,
            });
    
            let country_Tomselect=new TomSelect("#select-country-profile",{
                created:true,
                maxItems: 2,
                maxOptions: null,
            });
    
            let expertise_Tomselect=new TomSelect("#select-expertise-profile",{
                created:true, 
                maxOptions: null,           
            });
    
            let sort_Tomselect=new TomSelect("#select-sort-profile",{
                created:true,   
                maxOptions: null,         
            });
        }
        

        

        $(".custom-profile-filter-select").change(function(){

            redirect_link='';
            
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


             redirect_link=$("#custom_link_filter").val()+converted_query;                  
                       
        });      

        $("#profile-filter-button-go").click(function(){
            if(redirect_link == '')
            {
                window.location.reload();                
            }
            else
            {
                window.location.href=redirect_link;
            }
             
        });

        $(".custom-contact-myself").click(function(){
            alert('You cannot contact by yourself !');
        });
       
    });

  })(jQuery);