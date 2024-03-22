(function ($) {
    $(document).ready(function () {

       

        let image_collection_ids=[];        

        //display function
        function displaySelectedFiles(files, remove = false)
        {                        
            var fileListElement = $(".port-uploaded-images-area"); 
            if (remove == true)
            {
                fileListElement.empty(); 
            } 
            files.forEach(function(file) {
                var fileName = file.name;
                var fileElement = $("<p>").text(fileName);
                var removeButton = $("<a>", {
                    class: "port-remove-icon",
                    href:"javascript:void(0)",
                    html: "<i class='fa fa-remove'></i>",
                    datacustomID: file.id,
                    click: function()
                    {
                        var idToRemove=file.id;
                        var indexToRemove = -1;
                        for (var i = 0; i < image_collection_ids.length; i++) {
                            if (image_collection_ids[i].file_id === idToRemove) {
                              indexToRemove = i;
                              break;
                            }
                        }
                        if (indexToRemove !== -1) {
                            image_collection_ids.splice(indexToRemove, 1);
                          }  
                          
                          portfolio_images_uploader.removeFile(file);                                                                                
                                             
                          displaySelectedFiles(portfolio_images_uploader.files,true);    

                          $.ajax({
                            type: "post",
                            url: ae_globals.ajaxURL,
                            dataType: 'json',
                            data: {
                                    action:'delete_attach_file_on_server',
                                    attach_file_id_delete:$(this).attr('attach_file_id'), 
                                    datacustomID:$(this).attr('datacustomID'),                                                                      
                                },                                
                            success: function (response) {
                                console.log(response);
                            }
                        });
                    }
                });
                fileElement.append(removeButton);
                fileListElement.append(fileElement);
                
            });
            
        }   

        //init file uploader
           let is_show_progressbar=false;
         
            if (typeof plupload !== 'undefined') {
                var portfolio_images_uploader = new plupload.Uploader({
                    runtimes: 'html5,flash,silverlight,html4',
                    browse_button: 'port-upload-images-btn', // ID of the custom button
                    container: 'upload-images-port-area', // ID of the container for the uploader                           
                    url: ae_globals.ajaxURL, // WordPress AJAX handler                       
                    multipart: true,      
                    multipart_params: {
                        action: 'port_upload_images_action', // Custom AJAX action for handling the upload
                        _ajax_nonce: $("#port_upload_images_none").val(), // Nonce for security   
                        datacustomID: "default",                      
                    },
                    filters: {
                        prevent_duplicates: true,
                        max_file_size: '20mb',
                        mime_types: [                        
                            { title: 'allowed files', extensions: 'png,jpg,jpeg,gif' },                        
                        ],
                    },
                    multi_selection: true, // Allow multiple file selection
                    max_file_count: 10,
                    init:
                    {
                        FilesAdded: function(up, files) 
                        {
                            let selectedFiles=[];
                            for (var i = 0; i < Math.min(portfolio_images_uploader.settings.max_file_count, files.length); i++) {
                                selectedFiles.push(files[i]);                              
                            }                         
                            displaySelectedFiles(selectedFiles);
                            portfolio_images_uploader.start();
                        },
                        BeforeUpload: function(up,file)
                        {                                
                            $(".create-portfolio-btn").attr('disabled','disabled');
                            $(".close-modal-port-icon").attr('disabled','disabled');
                            $("#port-upload-images-btn").attr('disabled','disabled');
                            if(is_show_progressbar==false)
                            {
                                $(".uploadprogressBar").css('display','block');
                                is_show_progressbar=true;
                            }

                               //hide remove file button while uploading                            

                            up.settings.multipart_params.datacustomID=file.id;
                        },                       
                        FileUploaded: function(up, file, response) {                            
                            //convert the result to array
                            var responseObject = JSON.parse(response.response);
                           
                            // get returned ids attachment and set for the input hidden                            
                            image_collection_ids.push({file_id: file.id, attach_id:responseObject.attach_id });                            
                                                              
                            //set return attach id for element ( this is used for delete button)
                            $("[datacustomID='"+file.id+"']").attr('attach_file_id',responseObject.attach_id);
                            
                        },
                        UploadComplete: function(up,file)
                        {
                            $(".create-portfolio-btn").removeAttr('disabled');
                            $(".close-modal-port-icon").removeAttr('disabled');
                            $("#port-upload-images-btn").removeAttr('disabled');                            
                            if(is_show_progressbar==true)
                            {
                                $(".uploadprogressBar").css('display','none');
                                is_show_progressbar=false;
                            }
                        },
                    },
                    
                })            
            };
      
            portfolio_images_uploader.init();

            //edit banner button

            if (typeof plupload !== 'undefined') {
                var banner_image_uploader = new plupload.Uploader({
                    runtimes: 'html5,flash,silverlight,html4',
                    browse_button: 'port-update-banner-btn', // ID of the custom button
                    container: 'update-banner-btn-area', // ID of the container for the uploader                           
                    url: ae_globals.ajaxURL, // WordPress AJAX handler                       
                    multipart: true,      
                    multipart_params: {
                        action: 'update_banner_image_portfolio', // Custom AJAX action for handling the upload                                                                  
                    },
                    filters: {
                        prevent_duplicates: true,
                        max_file_size: '20mb',
                        mime_types: [                        
                            { title: 'allowed files', extensions: 'png,jpg,jpeg,gif' },                        
                        ],
                    },
                    multi_selection: false, // Allow multiple file selection
                    max_file_count: 1,
                    init:
                    {
                        FilesAdded: function(up, files) 
                        {
                            banner_image_uploader.start();
                        },
                        BeforeUpload: function(up,file)
                        {    
                            $('.port-banner-background').css('opacity','0.5');
                            $("#port-update-banner-btn").attr('disabled','disabled');
                        },                        
                        FileUploaded: function(up, file, response) {
                            console.log(response);
                            var responseObject = JSON.parse(response.response);
                            $('.port-banner-background').css('opacity','1');
                            $("#port-update-banner-btn").removeAttr('disabled');                                                       
                            $("#port-banner-image").attr('src',responseObject.url_banner);
                           
                        },
                    },
                    
                })            
            };

            banner_image_uploader.init();
        
        $(".open-add-port-modal").click(function(){
            $("#custom-modal-add-portfolio").css('display','block');
        });

        $('.close-modal-port-icon').click(function(){
            $("#custom-modal-add-portfolio").css('display','none');
        });

        $('#create-port-form').submit(function(event){
            event.preventDefault();          
            let attachIds = image_collection_ids.map(item => item.attach_id);
            var formData = $(this).serialize()+"&attachment_ids="+attachIds;          
            $.ajax({

                type: "POST",
                url: ae_globals.ajaxURL,
                dataType: 'json',
                data: formData,
                beforeSend: function() {
                    $('#create-port-form').attr('disabled','disabled');
                    $('#create-port-form').css('opacity',0.5);
                    toastr.info('Creating portofolio...'); 
                },
                success: function(response) {   
                    if(response.success=='true') 
                    {
                        toastr.success(response.message); 
                        window.location.href=response.redirect_url;
                    }          
                    else
                    {
                        toastr.success('Something went wrong. Please refresh'); 
                        window.location.reload();
                    }      
                                       
                },   
                error: function(error) {                    
                    toastr.error('Something went wrong. Please refresh');
                }             
            });
           
        });

        //config lightbox
        lightbox.option({
            'fadeDuration': 400,
            'imageFadeDuration':400,
            'resizeDuration':400,
          });

          //handle show or hide sidebar on mobile
          $('.show-sidebar-btn').click(function(){            
            $(".port-sidebar").css('display','flex');
                $(".port-sidebar").animate({                    
                    right: '10px',                                   
                },'slow');
          });

          $('.hide-port-sidebar-btn').click(function(){            
           
                $(".port-sidebar").animate({                    
                    right: '-400px',                                   
                },'slow');
                setTimeout(function(){ $(".port-sidebar").css('display','none'); }, 500);
               
          });

          //open edit modal

          $("#edit-portfolio").click(function(){
            $("#custom-modal-edit-portfolio").css('display','block');
        });

        $('#close-modal-edit-port-icon').click(function(){
            $("#custom-modal-edit-portfolio").css('display','none');
        });

        $('#close-modal-edit-port-iconx').click(function(){
            $("#custom-modal-edit-portfolio").css('display','none');
        });

     
        $('#edit-port-form').submit(function(event){
            event.preventDefault();          
            
            var formData = $(this).serialize();          
            $.ajax({
                type: "POST",
                url: ae_globals.ajaxURL,
                dataType: 'json',
                data: formData,
                beforeSend: function() {
                    $('#edit-port-form').attr('disabled','disabled');
                    $('#edit-port-form').css('opacity',0.5);
                },
                success: function(response) {   
                    if(response.success=='true') 
                    {
                        toastr.success(response.message); 
                        window.location.reload();
                    }          
                    else
                    {
                        toastr.error('Something went wrong. Please refresh'); 
                        window.location.reload();
                    }      
                                       
                },   
                error: function(error) {                    
                    toastr.error('Something went wrong. Please refresh');
                }             
            });
           
        });

        //init single upload image to portfolio button

        let is_shown_single_upload_alert=false;
       

        if (typeof plupload !== 'undefined') {
            var single_image_uploader = new plupload.Uploader({
                runtimes: 'html5,flash,silverlight,html4',
                browse_button: 'add-images-portfolio', // ID of the custom button
                container: 'add-images-portfolio-area', // ID of the container for the uploader                           
                url: ae_globals.ajaxURL, // WordPress AJAX handler                       
                multipart: true,      
                multipart_params: {
                    action: 'single_upload_images', // Custom AJAX action for handling the upload
                    _ajax_nonce: $("#single_upload_images_nonce").val(), // Nonce for security   
                    portID_singleUpload: $("#port_id_single_upload").val(),                      
                },
                filters: {
                    prevent_duplicates: true,
                    max_file_size: '20mb',
                    mime_types: [                        
                        { title: 'allowed files', extensions: 'png,jpg,jpeg,gif' },                        
                    ],
                },
                multi_selection: true, // Allow multiple file selection
                max_file_count: 10,
                init:
                {
                    FilesAdded: function(up, files) 
                    {                        
                        single_image_uploader.start();
                    },
                    BeforeUpload: function(up,file)
                    {                   
                        if(is_shown_single_upload_alert ==false)                                
                        {
                            toastr.info('Uploading images...','',{timeOut: 6000});    
                            $('.topoverlay').css('display','block');
                            is_shown_single_upload_alert=true;
                        }
                       
                    },
                    FileUploaded: function(up, file, response) {                        
                       
                    },
                    UploadComplete: function(up,file){
                        toastr.success('Uploaded images successfully'); 
                        window.location.reload();
                    },
                },
                
            })            
        };
  
        single_image_uploader.init();

        //bulk select button handling

        $("#bulk-select-images").click(function(){
            if($(this).attr('data-bulk-select-status')=='false')
            {
                $(this).html('Cancel Select <i class="fa fa-ban"></i>');
                $(".chosen-image-select").css('display','block');
                $(this).attr('data-bulk-select-status','true');
                $('#btn-delete-images-port').css('display','inline');
            }
            else
            {
                $(this).html('Bulk Select <i class="fa fa-check-square"></i>');
                $(".chosen-image-select").css('display','none');
                $(this).attr('data-bulk-select-status','false');
                $('#btn-delete-images-port').css('display','none');

                //reset all selectors to off and reset style
                $("[data-port-item-id]").css('opacity','1');
                $(".image-selector-port").attr('data_select_status','false');
                $(".image-selector-port").html('Select');
            }
            
        });

        //single button handling

        let select_content='Selected '+ '<i class="fa fa-check"></i>';
        $(".image-selector-port").click(function(){
            if($(this).attr('data_select_status')=='false')
            {                   
                $("[data-port-item-id='"+$(this).attr('data_attachment_id_select')+"']").css('opacity','0.5');
                $(this).html(select_content);
                $(this).attr('data_select_status','true');
            }
            else
            {
                $("[data-port-item-id='"+$(this).attr('data_attachment_id_select')+"']").css('opacity','1');
                $(this).html('Select');
                $(this).attr('data_select_status','false');
            }
           
        });

        //delete images button

        $('#btn-delete-images-port').on('click', function() {
            var selectedIds = [];
            
            $('.chosen-image-select').each(function() {
                var $element = $(this).find('.image-selector-port');
                var selectStatus = $element.attr('data_select_status');
                
                if (selectStatus === 'true') {
                    var attachmentId = $element.attr('data_attachment_id_select');
                    selectedIds.push(attachmentId);
                }
            });
            
            if(selectedIds.length > 0)
            {
                $.ajax({
                    type: "POST",
                    url: ae_globals.ajaxURL,
                    dataType: 'json',
                    data: {
                        action:'delete_images_of_portfolio',
                        portfolio_id:$("#btn-delete-images-port").attr('data_portfolio_id'), 
                        list_images_ids: selectedIds,
                        data_delete_images_nonce: $("#btn-delete-images-port").attr('data_delete_images_nonce'),
                    },
                    beforeSend: function() {
                        $('.topoverlay').css('display','block');
                        toastr.info('Deleting images....');   
                    },
                    success: function(response) {                       
                       if(response.success=='true') 
                        {
                            toastr.success(response.message); 
                            window.location.reload();
                        }          
                        else
                        {
                            toastr.error('Something went wrong. Please refresh'); 
                            window.location.reload();
                        }   
                                           
                    },   
                    error: function(error) {                    
                        toastr.error('Something went wrong. Please refresh');
                    }             
                });
            }
            else
            {
                toastr.warning('You must choose images to delete'); 
            }

        });

        //delete portfolio handling
        $("#btn-delete-portfolio").click(function(){
            $(".modal-confirm-delete-portfolio").css('display','block');
        });

        //cancel delete port
        $("#cancel-delete-portfolio-btn").click(function(){
            $(".modal-confirm-delete-portfolio").css('display','none');
        });

        //submit delete port
        

        $('#delete-portfolio-modal').submit(function(event){   
            event.preventDefault(); 
            var formData = $("#delete-portfolio-modal").serialize();         
            $.ajax({

                type: "POST",
                url: ae_globals.ajaxURL,
                dataType: 'json',
                data: formData,
                beforeSend: function() {
                    $('#delete-portfolio-modal').attr('disabled','disabled');
                    toastr.info('Deleting portfolio...'); 
                },
                success: function(response) {   
                    if(response.success=='true') 
                    {
                        toastr.success(response.message); 
                        window.location.href=response.redirect_url;
                    }          
                    else
                    {
                        toastr.success('Something went wrong. Please refresh'); 
                        window.location.reload();
                    }      
                                       
                },   
                error: function(error) {                    
                    toastr.error('Something went wrong. Please refresh');
                }             
            });
           
        });

        
    });


})(jQuery);