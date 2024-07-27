(function ($) {
    $(document).ready(function () {

       

        let image_collection_ids=[];     
        
        let video_collection_ids=[];

        //display function
        function displaySelectedFiles(files, remove = false)
        {                        
            var fileListElement = $(".port-uploaded-images-area"); 
            if (remove == true)
            {
                fileListElement.empty(); 
            } 
            files.forEach(function(file) {
                var fileName = "<i class='fa fa-paperclip'></i> "+file.name;
                var fileElement = $("<p class='custom-uploaded-items'>").html(fileName);
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
                            toastr.success('File uploaded successfully');
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
            let videoAttachIds=video_collection_ids.map(item => item.attach_id);
            var formData = $(this).serialize()+"&attachment_ids="+attachIds+"&video_attachment_ids="+videoAttachIds;          
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
      
          //fancybox init
          Fancybox.bind("[data-fancybox]", { 
            closeButton:true,
            backdropClick:false,
            on:
            {
                done:(fancybox,slide)=>{
                    $('.linkedin-sharing-button').click(function(event){
                        event.preventDefault();
                        event.stopPropagation();

                        let linkedin_url_share=$(this).attr('data-url-to-share');                        
                        let linkedin_platform=$(this).attr('data-linkedin-platform-url')+ encodeURIComponent(linkedin_url_share);

                        var popupWidth = 600;
                        var popupHeight = 400;
                        var popupLeft = (screen.width - popupWidth) / 2;
                        var popupTop = (screen.height - popupHeight) / 2;

                        window.open(linkedin_platform, "linkedinShareWindow", "width=" + popupWidth + ",height=" + popupHeight + ",left=" + popupLeft + ",top=" + popupTop + ",scrollbars=no");                        

                    });

                    $('.pinterest-sharing-button').click(function(event){
                        event.preventDefault();
                        event.stopPropagation();

                        let pinterest_url_share=$(this).attr('data-url-to-share');                        
                        let pinterest_platform=$(this).attr('data-pinterest-platform-url')+ encodeURIComponent(pinterest_url_share);

                        var popupWidth = 750;
                        var popupHeight = 550;
                        var popupLeft = (screen.width - popupWidth) / 2;
                        var popupTop = (screen.height - popupHeight) / 2;
                                               
                        window.open(pinterest_platform, "pinterestShareWindow", "width=" + popupWidth + ",height=" + popupHeight + ",left=" + popupLeft + ",top=" + popupTop + ",scrollbars=no");

                    });


                },
            }

          });
        Fancybox.bind("[data-fancybox='audio-group']", {          
              closeButton:false,
              backdropClick:false,
              mainClass:'customAudioGroup',
              on:               
              {
                done: (fancybox, slide) => {      
                    const audioPlayersCollection = document.querySelectorAll('.custom-single-audio-player-port');     
                    audioPlayersCollection.forEach(function (playerElement) {
                        const audioplayer = new Plyr(playerElement,{
                            controls: ['play','progress','current-time','mute'],
                            hideControls: false,
                            autoplay: true,
                        });
                        audioplayer.on('play',function(){                            
                            $('.fancybox__content').find('.plyr__control').css('display','inline-block');
                        })
                        
                        
                    });                      
                },    
                //turn off audio player by using trigger when changing slide
                "Carousel.ready Carousel.beforeChange": (fancybox) => {           
                 
                    const slide = fancybox.getSlide();
                    let status_play_btn=$(slide.contentEl.firstChild).find('[data-plyr="play"]').attr('aria-pressed');              
                    if(status_play_btn=='true')
                    {                        
                        $(slide.contentEl.firstChild).find('[data-plyr="play"]').trigger('click');
                    }
                    
                },
                
              },
             
        });

        Fancybox.bind("[data-fancybox='video-group']", {          
            closeButton:true,
            backdropClick:false,            
            mainClass:'customVideoGroup',
            on:               
            {
              done: (fancybox, slide) => {      
                const portFancyVideoPlayers = document.querySelectorAll('.fancy-video-port');
    
                portFancyVideoPlayers.forEach(function (playerElement) {
                    const custom_fancy_video_players = new Plyr(playerElement,{ controls: ['play','play-large','progress','current-time','mute','volume','fullscreen']});
                });    
                 
              },    
            }
        });

        //remove social link button in profile
        $('.btn-remove-social-link').click(function(){
            let delete_social_option=$(this).attr('data-remove-name');
            $.ajax({
                type: "POST",
                url: ae_globals.ajaxURL,
                dataType: 'json',
                data:
                {
                    action:'delete_social_link_profile',
                    social_type:delete_social_option,
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
    
        //config lightbox
        /*lightbox.option({
            'fadeDuration': 400,
            'imageFadeDuration':400,
            'resizeDuration':400,
          }); */

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

        //turn on / off insert mode
        $("#insert-data-btn").click(function(){
            //turn on
            if($(this).attr('data-insert-mode-status')=='false')
            {
                $(this).html('Cancel insert <i class="fa fa-ban"></i>');
                $(this).attr('data-insert-mode-status','true');
                $(".insert-btn-port-item").css('display','block');
            }
            else
            {
                $(this).html('Insert info <i class="fa fa-info-circle"></i>');
                $(this).attr('data-insert-mode-status','false');
                $(".insert-btn-port-item").css('display','none');
            }
        });
        //insert info open modal button
        $(".insert-btn-port-item-a").click(function(){            
            let insert_port_item_id=$(this).attr('data-insert-item-id');
            $("#port_item_id").val(insert_port_item_id);
            $("#custom-modal-insert-portfolio").css('display','flex');
            $("#insert_port_item_title").val('');
            $("#insert_port_item_description").val('');
            $.ajax({
                type: "GET",
                url: ae_globals.ajaxURL,
                dataType: 'json',
                data: {
                    action:'get_info_of_port_item',
                    portfolio_item_id: insert_port_item_id,                                   
                },
                success: function(response) {                       
                   if(response.success=='true') 
                    {
                        $("#insert_port_item_title").val(response.meta_title);
                        $("#insert_port_item_description").val(response.meta_description);
                        
                    }          
                    else
                    {
                        toastr.error('Something went wrong. Please refresh'); 
                        window.location.reload();
                    }   
                                       
                },   
            });

        });

        //handling form when user submit edit meta port form

        $("#insert-meta-port-form").submit(function(event){
            event.preventDefault();   
            var edit_meta_formData = $(this).serialize();
            $.ajax({
                type: "POST",
                url: ae_globals.ajaxURL,
                dataType: 'json',
                data: edit_meta_formData,
                beforeSend: function() {          
                    $('#insert-meta-port-form').css('opacity',0.5); 
                    $('#insert-meta-port-form').attr('disabled','disabled');         
                    toastr.info('Saving...');   
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
        
        

        //close modal edit meta title & description
        $("#close-modal-insert-port-iconx").click(function(){
            $("#custom-modal-insert-portfolio").css('display','none');
            $("#port_item_id").val('');
        });

        $('#close-modal-insert-port-item-icon').click(function(){
            $("#custom-modal-insert-portfolio").css('display','none');
            $("#port_item_id").val('');
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
                        toastr.info('Deleting files....');   
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

        //init single upload video button to portfolio
        
        if (typeof plupload !== 'undefined') {
            var video_portfolio_uploader = new plupload.Uploader({
                runtimes: 'html5,flash,silverlight,html4',
                browse_button: 'add-videos-portfolio', // ID of the custom button
                container: 'add-video-portfolio-area', // ID of the container for the uploader                           
                url: ae_globals.ajaxURL, // WordPress AJAX handler                       
                multipart: true,     
                chunk_size:'10mb',
                multipart_params: {
                    action: 'video_upload_file_service_handling', // Custom AJAX action for handling the upload
                    _ajax_nonce: $("#video_upload_single").val(), // Nonce for security   
                    datacustomID: "default",  
                    single_video_upload_portfolio: "true",
                    portfolio_id: $("#port_id_single_upload").val(),
                    custom_file_name: "defaultname",
                   custom_file_type: "defaulttype",
                   custom_file_id: "defaultid",                                       
                },
                filters: {
                    prevent_duplicates: true,
                    //max_file_size: '20mb',
                    mime_types: [                        
                        { title: 'allowed files', extensions: 'avi,mp4,webm,wmv,ogg,flac,m4a,wma,aac,mp3' }, 
                    ],
                },
                multi_selection: false, // Allow multiple file selection
                max_file_count: 1,
                init:
                {
                    FilesAdded: function(up, files) 
                    {               
                        
                        video_portfolio_uploader.start();                        
                    },
                    BeforeUpload: function(up,file)
                    {         
                        toastr.warning('Start to uploading file !');
                        $(".topoverlay").css('display','flex');
                        $(".uploadingText").css('display','block');                        
                        up.settings.multipart_params.custom_file_name = file.name;
                        up.settings.multipart_params.custom_file_type = file.type;
                        up.settings.multipart_params.datacustomID=file.id;
                    },     
                    UploadProgress: function(up, file) {
                        
                        $("#uploadingProgressDisplay").text(file.percent + '%');

                    },                  
                    FileUploaded: function(up, file, response) {                          
        
                        var responseObject = JSON.parse(response.response);  
                        if(responseObject.success==true || responseObject.success=='true') 
                        {
                            toastr.success('File uploaded successfully!');
                            window.location.reload();
                        }  
                        else
                        {
                            toastr.error('something went wrong. Please refresh !');
                        }
                        $(".topoverlay").css('display','none');
                        $(".uploadingText").css('display','none');                     
                                                                                                                          
                    },                 
                    UploadComplete: function(up,file)
                    {                        
                       
                    }
                    
                },
                
            })            
        };
  
        video_portfolio_uploader.init();


        //init video uploader for modal portfolio

        if (typeof plupload !== 'undefined') {
            var video_portfolio_modal_uploader = new plupload.Uploader({
                runtimes: 'html5,flash,silverlight,html4',
                browse_button: 'port-upload-videos-btn', // ID of the custom button
                container: 'upload-videos-port-area', // ID of the container for the uploader                           
                url: ae_globals.ajaxURL, // WordPress AJAX handler                       
                multipart: true,     
                chunk_size:'10mb',
                multipart_params: {
                    action: 'video_upload_file_service_handling', // Custom AJAX action for handling the upload
                    _ajax_nonce: $("#port_upload_video_nonce").val(), // Nonce for security   
                    datacustomID: "default",                                      
                    custom_file_name: "defaultname",
                   custom_file_type: "defaulttype",
                   custom_file_id: "defaultid",                                       
                },
                filters: {
                    prevent_duplicates: true,
                    //max_file_size: '20mb',
                    mime_types: [                        
                        { title: 'allowed files', extensions: 'avi,mp4,webm,wmv,ogg,flac,m4a,wma,aac,mp3' },                        
                    ],
                },
                multi_selection: true, // Allow multiple file selection
                max_file_count: 5,
                init:
                {
                    FilesAdded: function(up, files) 
                    {                                      
                        video_portfolio_modal_uploader.start();                        
                    },
                    BeforeUpload: function(up,file)
                    {                                                  
                        up.settings.multipart_params.custom_file_name = file.name;
                        up.settings.multipart_params.custom_file_type = file.type;
                        up.settings.multipart_params.datacustomID=file.id;
                    },     
                    UploadProgress: function(up, file) {
                                                
                        $(".video-upload-progress-port-modal").css('display','block');
                        $(".video-upload-progress-port-modal").css('width',file.percent + '%');
                        $(".video-upload-progress-port-modal").html(file.percent + '%');

                    },                  
                    FileUploaded: function(up, file, response) {                          
        
                        var responseObject = JSON.parse(response.response);  
                        if(responseObject.success==true || responseObject.success=='true') 
                        {
                            video_collection_ids.push({file_id: file.id, attach_id:responseObject.attach_id });                            
                            remove_button=" <a data-video-client-id='"+file.id+"' class='delete-video-service' data-attach-video='"+responseObject.attach_id+"' href='javascript:void(0)'><i class='fa fa-remove'></i></a>";
                       
                            $(".port-uploaded-videos-area").append("<p class='custom-uploaded-items'><i class='fa fa-paperclip'></i> "+file.name+remove_button+"</p>");                            
                            $(".video-upload-progress-port-modal").css('display','none');
                            
                            toastr.success('File uploaded successfully');
                        }  
                        else
                        {
                            toastr.error('something went wrong. Please refresh !');
                        }

                                                                                                                          
                    },                 
                    UploadComplete: function(up,file)
                    {                        
                       
                    }
                    
                },
                
            })            
        };
  
        video_portfolio_modal_uploader.init();


        $("#create-port-form").on("click",".delete-video-service",function(){
            let videoidToRemove=$(this).attr('data-video-client-id');                          
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
                        //remove file from the list to submit portfolio                
                                            
                        let videoindexToRemove = -1;
                        for (var i = 0; i < video_collection_ids.length; i++) {
                            if (video_collection_ids[i].file_id === videoidToRemove) {
                                videoindexToRemove = i;
                              break;
                            }
                        }
                        if (videoindexToRemove !== -1) {
                            video_collection_ids.splice(videoindexToRemove, 1);
                          }  

                          //remove file from client uploader and uploaded area

                          $.each(video_portfolio_modal_uploader.files, function (i, file) {                            
                            if (file && file.id == videoidToRemove) {
                                
                                video_portfolio_modal_uploader.removeFile(file);
                            }

                            $("[data-video-client-id='"+videoidToRemove+"']").parent().remove();                            
                        });
                    }
                    else
                    {
                        toastr.error('failed to delete video !');
                    }
                   
                }
            });
        });

     
        
    });


})(jQuery);