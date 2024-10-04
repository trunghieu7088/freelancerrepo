(function ($) {
    $(document).ready(function () {      

       /* post request page */
        //init jquery ui calendar
        if( $(".custom_date_picker").length > 0)
        {            
             $(".custom_date_picker").datepicker({ dateFormat: 'yy-M-dd'});
        }


        //init tom select

        if($("#city_selector_arrival").length > 0)
        {
            var city_selector_arrival =  new TomSelect("#city_selector_arrival",{
                    create: false,
                    maxItems:1,
                    sortField: {
                        field: "text",
                        direction: "asc"
                    },
                    onChange: function(value){
                       $("#city_selector_arrival").focus();
                    },       
                });
         }
        
         if($("#city_selector_depart").length > 0)
        {
                var city_selector_depart= new TomSelect("#city_selector_depart",{
                        create: false,
                        maxItems:1,
                        sortField: {
                            field: "text",
                            direction: "asc"
                        },
                        onChange: function(value){
                           $("#city_selector_depart").focus();
                        },

                    });
        }

        if($("#sort-filter").length > 0)
        {
                    var sort_filter= new TomSelect("#sort-filter",{
                            create: false,
                            maxItems:1,
                            sortField: {
                                field: "text",
                                direction: "asc"
                            },
                            onChange: function(value){
                               $("#sort-filter").focus();
                            },
    
                        });
        }
    

       

        if($("#budget-search-filter").length > 0)
            {
                     var budget_filter = new TomSelect("#budget-search-filter",{
                            create: false,   
                            maxItems:1,                         
                            sortField: {
                                field: "text",
                                direction: "asc"
                            },
                            onChange: function(value){
                               $("#budget-search-filter").focus();
                            },
                        });
            }
       
             $('#city_selector_depart').on('change', function() {
                let selected_depart = $(this).find('option:selected');
                let postal_depart = selected_depart.attr('data-postal');
                $("#postal_code_depart").val(postal_depart);
             });

             $('#city_selector_arrival').on('change', function() {
                let selected_arrival= $(this).find('option:selected');
                let postal_arrival = selected_arrival.attr('data-postal');
                $("#postal_code_arrival").val(postal_arrival);
             });
              
       let post_request_validator= $("#post-request-form").validate({
        ignore: "",
        rules: {
            request_title: "required", 
            last_name: "required",                      
            first_name: "required",
            arrival_date: "required",
            departure_date: "required",
            arrival_address: "required",
            departure_address: "required",
            contact_method: "required",
            postal_code_depart: "required",
            postal_code_arrival: "required",
            budget:
            {
                required: true,
                number: true,
            }, 
            accept_tos: "required",

        },
        messages: {
            request_title: {
                required: required_validation_message
            },
            last_name: {
                required: required_validation_message
            },
            first_name: {
                required: required_validation_message
            },
            arrival_date: {
                required: required_validation_message
            },
            departure_date: {
                required: required_validation_message
            },
            arrival_address: {
                required: required_validation_message
            },
            departure_address: {
                required: required_validation_message
            },
            postal_code_arrival: {
                required: required_validation_message
            },
            postal_code_depart: {
                required: required_validation_message
            },

            budget:
            {
                required: required_validation_message,
                number: number_type_validation,
            }, 
            contact_method: {
                required: required_validation_message,
            },

            accept_tos: {
              required: accept_tos_message,
            }
          },
        errorElement: 'div', // Change the element to <div>
        errorClass: 'error_message', // Change the error class to 'message'
        errorPlacement: function (label, element) {                                   
                $(element).closest('.post-request-fields').append(label);                        
        },
        highlight: function (element, errorClass) {
            $(element).addClass('error_message_field');
        },
        unhighlight: function (element, errorClass) {
            $(element).removeClass('error_message_field');
        },
        submitHandler: function(form) 
        {
            let image_attachment_ids=image_collection_ids.map(item => item.attach_id);
            var request_description_content = tinyMCE.get('request_description').getContent();
            var moving_formData = $(form).serialize()+"&image_attachment_ids="+image_attachment_ids+"&request_description_content="+encodeURIComponent(request_description_content);
            $.ajax({
                type: "post",
                url: moving_ajaxURL,
                dataType: 'json',
                data: moving_formData,        
                beforeSend: function () {
                    $("#post-request-form").css('opacity','0.5');
                    $("#post-request-form").attr('disabled',true);
                },                      
                success: function (response) {
                                       
                    if(response.success==true || response.success =='true')
                    {
                        toastr.success(response.message);
                        window.location.href=response.redirect_url;
                    }
                    else
                    {
                        toastr.error(response.message);
                    }
                   
                }
            });

        }
    });

        /* upload images */       
        let image_collection_ids=[];
        if($("#post-request-form").length > 0)
        {
            if (typeof plupload !== 'undefined') {
                var request_image_uploader = new plupload.Uploader({
                    runtimes: 'html5,flash,silverlight,html4',
                    browse_button: 'request-image-upload-btn', // ID of the custom button
                    container: 'request-image-upload-container', // ID of the container for the uploader                           
                    url: moving_ajaxURL, // WordPress AJAX handler                       
                    multipart: true,      
                    multipart_params: {
                        action: 'request_image_uploader', // Custom AJAX action for handling the upload
                        _ajax_nonce: $("#request_image_uploader_nonce").val(), // Nonce for security                                              
                    },
                    filters: {
                        prevent_duplicates: true,
                        max_file_size: '20mb',
                        mime_types: [                        
                            { title: 'allowed files', extensions: 'png,jpg,jpeg,gif' },                        
                        ],
                    },
                    multi_selection: false, // Allow multiple file selection
                    max_file_count: 5,
                    init:
                    {
                        FilesAdded: function(up, files) 
                        {                                                      
                            if(up.files.length > max_file_upload_request_form)
                            {
                                toastr.warning(message_upload +' '+max_file_upload_request_form+' '+message_upload_file);
                                while (up.files.length > max_file_upload_request_form) {
                                    up.removeFile(up.files[up.files.length - 1]);
                                }
                                return ;
                            }
                            else
                            {                                
                                request_image_uploader.start();
                            }                      
                         
                        },
                        BeforeUpload: function(up,file)
                        {                                
                            toastr.info(message_uploading);
                        },                       
                        FileUploaded: function(up, file, response) {                                                        
                            var responseObject = JSON.parse(response.response);                         
                            if(responseObject.success=='true' || responseObject.success==true)
                            {
                                let html_file=`<div id="${file.id}" class="request-image-item"><img src="${responseObject.url_uploaded}"><a href="#" data-attach-id="${responseObject.attach_id}" data-fe-id="${file.id}" class="delete-image-btn"><i class="fa fa-x"></i></a></div>`;
                                $("#uploaded-image-section").append(html_file);
                                image_collection_ids.push({file_id: file.id, attach_id:responseObject.attach_id });                                                            
                            }
                                                    
                        },
                        UploadComplete: function(up,file)
                        {
                            
                        },
                    },
                    
                })  
            };

            request_image_uploader.init();
        }

            /* handling deleting images */
            $(document).on('click', '.delete-image-btn', function(event) {
                event.stopPropagation();
                event.preventDefault();
                var file_id = $(this).attr('data-fe-id');
                var attach_id_image = $(this).attr('data-attach-id');
                var file = request_image_uploader.getFile(file_id);
                if (file) {
                    //delete file on frontend and remove file from uploader instance
                    request_image_uploader.removeFile(file); // Remove file from the uploader instance
                    $("#" + file_id).remove(); // Remove the file item from the DOM
                    //remove file from ids list submit to server
                    let imageIndexToRemove = -1;
                    for (var i = 0; i < image_collection_ids.length; i++) {
                        if (image_collection_ids[i].file_id === file_id) {
                            imageIndexToRemove = i;
                          break;
                        }
                    }
                    if (imageIndexToRemove !== -1) {
                        image_collection_ids.splice(imageIndexToRemove, 1);
                      }  
                    

                    //delete file from server
                    $.ajax({
                        type: "post",
                        url: moving_ajaxURL,
                        dataType: 'json',
                        data: {
                                action:'delete_image_on_server',
                                attach_file_id_delete: attach_id_image,                                        
                            },                                
                        success: function (response) {
                                               
                            if(response.success==true || response.success =='true')
                            {
                                toastr.success(response.message);
                            }
                            else
                            {
                                toastr.error(response.message);
                            }
                           
                        }
                    });
                }
            });

            /* end */
        /* end upload images */

    /* end post request page */

    /* define role block */
    if($("#select-role").length > 0)
    {
        new TomSelect("#select-role",{
            create: true,
            sortField: {
                field: "text",
                direction: "asc"
            }       
        });
    }

    

    $("#moving-identify-role").submit(function(event){
        event.preventDefault();     
        var save_role_data= $("#moving-identify-role").serialize();

        $.ajax({
            type: "POST",
            url: moving_ajaxURL,
            dataType: 'json',
            data: save_role_data,
            beforeSend: function() {
                $('#moving-identify-role').attr('disabled', true);
                $('#moving-identify-role').css('opacity', '0.5');
            },
            success: function(response) {   
                    
                    if(response.success=='true') 
                    {                        
                        toastr.success(response.message);
                        window.location.href=response.redirect_url;
                    }          
                    else
                    {
                        toastr.error(error_ajax_message);
                    }      
                                       
            },                   
        });
    })
    /* end define role block */

    /* all request page */
    Fancybox.bind('[data-fancybox]', {
        // Your custom options for a specific gallery
      });
      /* search bar */

      //init seacrh bar params
      if($("#search-bar-area").length > 0)
      {
            let search_redirect_url='';
            let search_query='';

            let arrival_date_query='';
            let departure_date_query='';

            let arrival_city_query='';
            let departure_city_query='';

            let budget_filter_query='';
            let only_mine='';    
            let sort_by_query='';
            
      }

      function clearFilters(clear_mine=true)
      {
        //clear all filters

        //date filters
        $("#city_selector_depart").val('');
        $("#city_selector_arrival").val('');

        $("#arrival_date").val('');
        $("#departure_date").val('');

        $("#search").val('');
        budget_filter.clear();

        //cities filters
        city_selector_depart.clear();
        city_selector_arrival.clear();

        //sort
        sort_filter.setValue('desc');

        if(clear_mine==true)
        {
            $("#only_mine").removeAttr('checked');
        }

      }

      $("#btn-clear-filter").click(function(){
        //clear all filters
        clearFilters();
       
      });

      $("#only_mine").click(function(){

        search_query='?search='+$("#search").val();
        if($("#only_mine").is(":checked"))
        {
            only_mine='?mine=yes';            
            clearFilters(false); //clear all other filters except mine option
            search_redirect_url=$("#search_link").val()+only_mine;
            window.location.href=search_redirect_url;
        }
    

      });


      $("#btn-search-submit").click(function(){
           search_query='?search='+$("#search").val();

           arrival_date_query='&arrival_date='+$("#arrival_date").val();
           departure_date_query='&departure_date='+$("#departure_date").val();

           arrival_city_query='&city_arrival='+$("#city_selector_arrival").val();
           departure_city_query='&city_depart='+$("#city_selector_depart").val();

           budget_filter_query='&budget_filter='+budget_filter.getValue();
           sort_by_query='&sort_by='+sort_filter.getValue();

           /* if($("#only_mine").is(":checked"))
            {
                only_mine='&mine=yes';
            }
            else
            {
                only_mine='&mine=no';
            }
            */
            only_mine='&mine=no'; //turn off my request, my paid list when search

           search_redirect_url=$("#search_link").val()+search_query
           +arrival_date_query+departure_date_query
           +arrival_city_query+departure_city_query
           +budget_filter_query+only_mine+sort_by_query;

           window.location.href=search_redirect_url;

      });

      /* end search bar */
    /* end all request page */
    });

})(jQuery);