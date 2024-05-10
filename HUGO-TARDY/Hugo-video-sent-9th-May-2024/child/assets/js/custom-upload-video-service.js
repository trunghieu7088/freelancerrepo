(function ($) {
    $(document).ready(function () {     
        let max_file_video_upload=0;  
        let remove_button=null; 
       

        if (typeof plupload !== 'undefined') {
            var video_service_uploader = new plupload.Uploader({
                runtimes: 'html5,flash,silverlight,html4',
                browse_button: 'video-btn-upload', // ID of the custom button
                container: 'video-upload-area', // ID of the container for the uploader                           
                url: ae_globals.ajaxURL, // WordPress AJAX handler                       
                multipart: true,     
                chunk_size:'10mb',
                multipart_params: {
                    action: 'video_upload_file_service_handling', // Custom AJAX action for handling the upload
                    _ajax_nonce: $("#video_upload_nonce").val(), // Nonce for security   
                    datacustomID: "default",  
                    custom_file_name: "defaultname",
                   custom_file_type: "defaulttype",
                   custom_file_id: "defaultid",                                       
                },
                filters: {
                    prevent_duplicates: true,
                    //max_file_size: '20mb',
                    mime_types: [                        
                        { title: 'allowed files', extensions: 'avi,mp4,webm,wmv' },                        
                    ],
                },
                multi_selection: false, // Allow multiple file selection
                max_file_count: 1,
                init:
                {
                    FilesAdded: function(up, files) 
                    {         
                        if(max_file_video_upload==1)                        
                        {                           
                            toastr.error('you can only upload 1 video for the service !');
                            return false;                            
                        }                      
                                              
                        video_service_uploader.start();                        
                    },
                    BeforeUpload: function(up,file)
                    {         
                        $("#uploading_status").val("true");                         
                        up.settings.multipart_params.custom_file_name = file.name;
                        up.settings.multipart_params.custom_file_type = file.type;
                        up.settings.multipart_params.datacustomID=file.id;
                    },     
                    UploadProgress: function(up, file) {
                        $(".videouploadprogressBar").css('display','block');
                        $(".videouploadprogressBar").css('width',file.percent + '%');
                        $(".videouploadprogressBar").html(file.percent + '%');

                    },                  
                    FileUploaded: function(up, file, response) {  
                        
                        //console.log(response);                        
                        //convert the result to array
                        var responseObject = JSON.parse(response.response);
                        
                        var uploadResult = responseObject.success;                    
                        if(uploadResult==true || uploadResult =='true')
                        {
                            max_file_video_upload=1;  
                            $("#video_attach_id").val(responseObject.attach_id);
                            remove_button=" <a  class='delete-video-service' data-attach-video='"+responseObject.attach_id+"' href='javascript:void(0)'><i class='fa fa-remove'></i></a>";
                            $(".choosen-video-area").html('');
                            $(".choosen-video-area").append("<p>"+file.name+remove_button+"</p>");
                        }                                                                                                                                    
                    },                 
                    UploadComplete: function(up,file)
                    {                        
                        $("#uploading_status").val('');
                    }
                    
                },
                
            })            
        };
  
        video_service_uploader.init();

        $(".et-form").on("click",".delete-video-service",function(){           
            $.ajax({
                type: "post",
                url: ae_globals.ajaxURL,
                dataType: 'json',
                data: {
                        action:'delete_attach_video_on_server',
                        attach_file_id_delete:$(this).attr('data-attach-video'),                                        
                    },                                
                success: function (response) {
                                       
                    if(response.success==true || response.success =='true')
                    {
                        $(".videouploadprogressBar").css('display','none');
                        $(".choosen-video-area").html('');
                        max_file_video_upload=0; 
                    }
                    else
                    {
                        toastr.error('failed to delete video !');
                    }
                   
                }
            });
        });
        
    })
})(jQuery);