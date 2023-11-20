(function ($) {
    jQuery(document).ready(function($){
        var cv_custom_upload;
        if (typeof plupload !== 'undefined') {      
          //  console.log('Plupload is loaded successfully.');
            let url_ajax=window.location.origin+'/wp-admin/admin-ajax.php'; 
            var pluploadSettings = {
                runtimes: 'html5,flash,silverlight,html4',
                browse_button: 'custom-upload-pdf-btn', // ID of the custom button
                container: 'cv-upload-container', // ID of the container for the uploader                                             
                url: url_ajax, // WordPress AJAX handler                    
                multipart: true,      
                multipart_params: {
                    action: 'custom_cv_upload_file', // Custom AJAX action for handling the upload
                    _ajax_nonce: $("#custom-upload-cv-nonce").val(), // Nonce for security   
                    resumeID: $("#resumeID").val(),
                },
                filters: {
                    prevent_duplicates: true,
                    max_file_size: '20mb',
                    mime_types: [                        
                        { title: 'PDF file', extensions: 'pdf' },                        
                    ],
                },
                multi_selection: false, // Allow multiple file selection
                max_file_count: 1,
                init: {
                    FilesAdded: function(up, files) {                                                                
                        cv_custom_upload = up; // Store the uploader object in a variable
                        cv_custom_upload.start();
                    },
                    FileUploaded: function(up, file, response) {
                        //console.log(response);
                        var responseObject = JSON.parse(response.response);
                        if(responseObject.success=='true')
                        {
                            $("#cv-upload-container").append('<p>Uploaded file successfully</p>');
                            window.location.reload();
                        }
                    },
                    Error: function(up, error) {                        
                        //console.log(error);
                        alert(error.message);
                       
                    },
                },
            };
        } 
        cv_uploader = new plupload.Uploader(pluploadSettings);              
        cv_uploader.init();  
     
    });
})(jQuery);