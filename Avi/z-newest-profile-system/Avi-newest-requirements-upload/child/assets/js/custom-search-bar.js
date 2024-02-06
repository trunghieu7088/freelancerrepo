(function ($) {

    $(document).ready(function() 
    {
        
        $("#searchType i, #customTypeTextSearh").click(function(){

            let status_search=$("#searchType").attr('data-custom-status');

            if(status_search=='off')
            {
                $(".custom-selection-board").slideDown();
                $("#searchType").attr('data-custom-status','on');
            }
            else
            {
                $(".custom-selection-board").slideUp();
                $("#searchType").attr('data-custom-status','off');
            }
            
        });

        $(".custom-item").click(function(){
            let custom_selected_option=$(this).attr('data-custom-option');

            $("#customTypeTextSearh").text(custom_selected_option);

            $("#customTypeTextSearh").attr('data-custom-searchType',custom_selected_option);

            $("#searchType").attr('data-custom-status','off');

            $(".custom-selection-board").slideUp();          
        });
        
        $(".custom-search-bar-wrapper").submit(function(event){
            event.preventDefault();
            let search_selected_option= $("#customTypeTextSearh").attr('data-custom-searchType');
            let search_string=$("#input-search").val();           
            if(search_selected_option=='Profile')
            {
                window.location.href=search_profile_link+'/?search='+search_string;
            }
            if(search_selected_option=='Session')
            {
                window.location.href=search_session_link+'/?s='+search_string;
            }
        });

    });

})(jQuery);