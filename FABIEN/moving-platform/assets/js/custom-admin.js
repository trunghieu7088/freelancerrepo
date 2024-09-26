(function ($) {
    $(document).ready(function () {   
        $(".import-remove-file").click(function()
        {
            let file_id=$(this).attr('data-attach-id');
            let file_url=$(this).attr('data-file-url');            
            $.ajax({
                type: "POST",
                url: admin_ajax_url,
                dataType: 'json',
                data: {
                    action:'delete_import_excel_file',
                    delete_file_id: file_id,
                    delete_file_url: file_url,

                },
                beforeSend: function() {
                   
                },
                success: function(response) {   
                        
                        alert(response.message);                     
                        window.location.reload();  
                },                   
            });

        });

        if($("#ban_user_list").length > 0)
        {
                var ban_user_list =  new TomSelect("#ban_user_list",{
                        create: false,                        
                        maxItems:10,
                        plugins: ['remove_button'],
                        sortField: {
                            field: "text",
                            direction: "asc"
                        },
                        /* onChange: function(value){
                           $("#ban_user_list").focus();
                        },       */
                    });

                $("html, body").animate({ scrollTop: $(document).height()-$(window).height() });
        }

    });
})(jQuery);