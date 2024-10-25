(function ($) {

  //apply youtube api frame for youtube player in single mjob page
  function applyYTmjob()
  {
    var ytBigPlayerMjob;
    if($('.mjob-youtube-big-content').length > 0)
    {
      window.YT.ready(function() {
        ytBigPlayerMjob= new window.YT.Player("yt-mjob-big-player", {         
          playerVars: {
            'playsinline': 1
        },
        events: {
          onReady: function(event) {
            
            $("#mjob-player-big-modal").on('shown.bs.modal', function(){
              if($("#show-big-player-mjob-btn").attr('data-video-type')=='youtube')
              {
                ytBigPlayerMjob.playVideo();                  
              }
              
            });
            $("#mjob-player-big-modal").on('hidden.bs.modal', function(){
              if($("#show-big-player-mjob-btn").attr('data-video-type')=='youtube')
                {
                  ytBigPlayerMjob.pauseVideo();                 
                }
                               
            });
          }         
        },
        });
      });
    }
   
  }

  function loadVideos() {
    $('.yt-short-video').each(function(index, element) {
        var playerInstance;
        var iframeId = 'youtube-player-' + index; // Tạo id duy nhất cho mỗi iframe
        $(element).attr('id', iframeId); // Gán id cho mỗi iframe

        // Khởi tạo player cho iframe hiện tại
        playerInstance = new window.YT.Player(iframeId, {
            playerVars: {
                'playsinline': 1
            },
            events: {
                onReady: function(event) {
                    // Lắng nghe sự kiện mouseenter và mouseout cho từng iframe
                    $(element).on('mouseenter', function() {
                        //console.log('enter', iframeId);
                        //console.log(playerInstance);
                        playerInstance.playVideo();
                        
                    });

                    $(element).on('mouseleave', function() {
                        //console.log('leave', iframeId);
                        playerInstance.pauseVideo();
                    });
                },
                onStateChange: function(event) {
                    var videoStatuses = Object.entries(window.YT.PlayerState);
                    console.log('State Change:', iframeId, videoStatuses.find(status => status[1] === event.data)[0]);
                }
            }
        });
    });
}

    $(document).ready(function() {

     
        $('.video-rate-it ').raty({
            readOnly: true,
            half: true,
            score: function () {
                return $(this).attr('data-score');
            },
            hints: raty.hint
        });
  
      
      $.getScript("https://www.youtube.com/iframe_api", function() {
        window.YT.ready(function() {
            loadVideos();
            applyYTmjob();
        });
    });
       
        var short_videos_slider = new Swiper(".mje-short-video-slider", {  
          nextButton: '.video-mje-next-area',
          prevButton: '.video-mje-prev-area',       
          slidesPerView: 1,          
          slidesPerGroup:1,  
          spaceBetween: 0,                        
          speed:200,                    
          loop: false,
          autoplay:
          {
            enabled: false,
            delay: 1500,
            stopOnLastSlide: true,
          },
           
          
          breakpoints: {
  
           300: {
              slidesPerView: 1,
              spaceBetween: 0,
            },
            640: {
              slidesPerView: 2,
              spaceBetween: 20,
            },
            768: {
              slidesPerView: 2,
              spaceBetween: 1,
            },
            1024: {
              slidesPerView: 4,
              spaceBetween: 1.5,
            },
      
            1920: {
              slidesPerView: 4,                
              spaceBetween: 25,
            },
          },
  
        });    

        $(".video-mje-next-area").click(function(){
          short_videos_slider.slideNext();          
         });
     
        $(".video-mje-prev-area").click(function(){
          short_videos_slider.slidePrev();          
         });

         function toggleNavigationButtons() {

          if(short_videos_slider.isBeginning)
          {              
            $(".video-mje-prev-area").css('display','none');
          }
        
          if (short_videos_slider.isEnd) 
          {            
            $(".video-mje-next-area").css('display','none');
          } 

          if(!short_videos_slider.isBeginning && !short_videos_slider.isEnd)
          {
            $(".video-mje-prev-area").css('display','flex');
            $(".video-mje-next-area").css('display','flex');
          }
          
                          
        }

        short_videos_slider.on('slideChange', function () { 
        
          toggleNavigationButtons();
          
        });
      
       
        toggleNavigationButtons();
        
        const bigVideoPlayer = new Plyr("#big-local-video",{ 
          controls: ['play-large'], 
          fullscreen: { enabled: false, fallback: false } 
    
    });

         const localVideoPlayers = document.querySelectorAll('.local-item-video-player');
    
         localVideoPlayers.forEach(function (playerElement) {
             const localPlayer = new Plyr(playerElement,{ controls: false});

             localPlayer.on('mouseenter',function(){               
               localPlayer.play();
             });
             
             localPlayer.on('mouseout',function(){              
              localPlayer.pause();
            });

            localPlayer.on('click',function(){               
              $("#video-player-big-modal").modal();              
              //apply data to modal                            
              $("#video-owner-name").text(localPlayer.config.ownerName);
              $("#video-owner-avatar").attr('src',localPlayer.config.ownerAvatarURL);
              $("#video-owner-score").attr('data-score',localPlayer.config.ownerRating);
              $("#video-owner-profile-wrap-link").attr('href',localPlayer.config.ownerProfileURL);
              $("#video-owner-viewpf-btn").attr('href',localPlayer.config.ownerProfileURL);
              $("#video-owner-location").text(localPlayer.config.ownerLocation);
              $("#video-owner-language").text(localPlayer.config.ownerLanguage);
              $("#video-caption-content").html(localPlayer.config.videoCaption);

              //re-load rating score
              $('.video-rate-it ').raty({
                readOnly: true,
                half: true,
                score: function () {
                    return $(this).attr('data-score');
                },
                hints: raty.hint
            });
              //copy data from service list
              $("#serviceListSection").html('');
              let cloneElement= $("#short-video-serviceList-"+localPlayer.config.ID).clone();    
              cloneElement.css('display','block');              
              cloneElement.appendTo($("#serviceListSection"));
              
              bigVideoPlayer.source={
                  type: 'video',
                  sources: [
                    {                        
                        src: localPlayer.config.videoInfo.url,                        
                        type: localPlayer.config.videoInfo.mime_type,
                    }
                ],

              };
              bigVideoPlayer.play();
            });

         });    
         
         /* post service form handling */
         $(".videoType").click(function(){
            if($(this).val()=='none')
            {              
              $("#uploadVideoArea").fadeOut();
              $("#youtubeVideoArea").fadeOut();
              $("#video_caption").removeAttr('required'); 
              $("#youtube_video_caption").removeAttr('required');
              $("#youtube_video_link").removeAttr('required');  
              
            }

            if($(this).val()=='upload')
            {              
              $("#uploadVideoArea").fadeIn();
              $("#youtubeVideoArea").fadeOut();
              $("#video_caption").attr('required',true);
              $("#youtube_video_caption").removeAttr('required');
              $("#youtube_video_link").removeAttr('required');                      
            }

            if($(this).val()=='youtube')
              {
                $("#uploadVideoArea").fadeOut();
                $("#youtubeVideoArea").fadeIn();          
                $("#video_caption").removeAttr('required'); 
                $("#youtube_video_caption").attr('required',true);                      
                $("#youtube_video_link").attr('required',true);                      
              }
              $("#choosenvideoType").val($(this).val());

         })

         /* init short video uploader */
         if($('.short-video-upload-area').length > 0)
         {
            
                if (typeof plupload !== 'undefined') {
                  var short_video_uploader = new plupload.Uploader({
                      runtimes: 'html5,flash,silverlight,html4',
                      browse_button: 'btn-upload-short-video', // ID of the custom button
                      container: 'short-video-upload-container', // ID of the container for the uploader                           
                      url: ae_globals.ajaxURL, // WordPress AJAX handler                       
                      multipart: true,     
                      chunk_size:'5mb',
                      multipart_params: {
                          action: 'short_video_upload_file', // Custom AJAX action for handling the upload
                          _ajax_nonce: $("#short_video_upload_nonce").val(), // Nonce for security   
                          datacustomID: "default",  
                          custom_file_name: "defaultname",
                        custom_file_type: "defaulttype",
                        custom_file_id: "defaultid",                                       
                      },
                      filters: {
                          prevent_duplicates: true,
                          max_file_size: '50mb',
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
                            if(up.files.length > 1)
                            {
                                  toastr.warning('Only upload 1 video for service');
                                  while (up.files.length > 1) {
                                      up.removeFile(up.files[up.files.length - 1]);
                                  }
                                  return ;
                            }
                            else
                            {                                
                              plupload.each(files, function(file) {
                                if (file.type.startsWith('video/')) {
                                  // Create a FileReader to read the video file
                                  var fileReader = new FileReader();
                          
                                  // When the file is fully loaded
                                  fileReader.onload = function(event) {
                                    // Create a temporary video element to load the video
                                    var video = document.createElement('video');
                                    video.preload = 'metadata'; // Only load metadata
                          
                                    // Set the video source to the file data
                                    video.src = event.target.result;
                          
                                    // Wait for the metadata to load so we can access the duration
                                    video.onloadedmetadata = function() {
                                      // Video duration in seconds
                                      var duration = video.duration;
                          
                                      // Get minutes and seconds
                                      var minutes = Math.floor(duration / 60);
                                      var seconds = Math.floor(duration % 60);
                                                  
                          
                                      // Optionally, you can check if the duration meets certain conditions before uploading
                                      if (duration > 40) { // Example: if video is longer than 5 minutes                                        
                                        toastr.warning('Please select a video below 40 seconds.');
                                        short_video_uploader.removeFile(file);
                                        return ; // Remove file from upload queue
                                      } else {
                                        // If everything is okay, start the upload
                                        short_video_uploader.start();
                                      }
                                    };
                                  };
                          
                                  // Read the file as a data URL to allow video preview and metadata extraction
                                  fileReader.readAsDataURL(file.getNative());
                                } else {
                                  toastr.warning('Please select a valid video');
                                  short_video_uploader.removeFile(file); // Remove non-video file from upload queue
                                }
                              });
                                
                            }               
                              
                                                     
                          },
                          BeforeUpload: function(up,file)
                          {   
                            
                              $("#short_uploading_status").val("true");                         
                              up.settings.multipart_params.custom_file_name = file.name;
                              up.settings.multipart_params.custom_file_type = file.type;
                              up.settings.multipart_params.datacustomID=file.id;
                          },     
                          UploadProgress: function(up, file) {
                              $(".shortvideouploadprogressBar").css('display','block');
                              $(".shortvideouploadprogressBar").css('width',file.percent + '%');
                              $(".shortvideouploadprogressBar").html(file.percent + '%');

                          },                  
                          FileUploaded: function(up, file, response) {  
                              
                    
                              var responseObject = JSON.parse(response.response);
                              
                              var uploadResult = responseObject.success;                    
                              if(uploadResult==true || uploadResult =='true')
                              {                                  
                                  $("#short_video_attach_id").val(responseObject.attach_id);
                                  remove_button=" <a class='delete-short-video' data-fe-id='"+file.id+"' data-attach-video='"+responseObject.attach_id+"' href='javascript:void(0)'><i class='fa fa-remove'></i></a>";
                                  $(".choosen-short-video-area").html('');
                                  $(".choosen-short-video-area").append("<p>"+file.name+remove_button+"</p>");
                              }                                                                                                                                    
                          },                 
                          UploadComplete: function(up,file)
                          {                        
                              $("#short_uploading_status").val('');
                          }
                          
                      },
                      
                  })            
              };
        
              short_video_uploader.init();
         }

         // delete short video
         $(document).on('click', '.delete-short-video', function(event) {
            event.stopPropagation();
            event.preventDefault();
            var delete_file_id = $(this).attr('data-fe-id');
            var attach_video_id = $(this).attr('data-attach-video');
            var deleting_video=short_video_uploader.getFile(delete_file_id);
            if (deleting_video) {
                  //delete file on frontend and remove file from uploader instance
                  short_video_uploader.removeFile(deleting_video); // Remove file from the uploader instance                  
                  $("[data-fe-id='"+delete_file_id+"']").parent().remove(); // Remove the file item from the DOM
                  $("#short_video_attach_id").val(''); //remove file from ids list submit to server
                  $(".shortvideouploadprogressBar").css('display','none');
                  //delete file from server
                  $.ajax({
                      type: "post",
                      url: ae_globals.ajaxURL,
                      dataType: 'json',
                      data: {
                              action:'delete_short_video_on_server',
                              attach_file_id_delete: attach_video_id,                                        
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

         /* end init short video uploader */
         
         /* end post service form handling */
         
         /*mjob big player */
         const mjobBigPlayer = new Plyr("#mjob-modal-video-player",{ 
            controls: ['progress','mute','current-time'],
            clickToPlay: false, 
            fullscreen: { enabled: false, fallback: false } 
        });

        mjobBigPlayer.on('click',function(){  
          
          mjobBigPlayer.togglePlay();
        });  
       
          //trigger play video if modal show
          $("#mjob-player-big-modal").on('shown.bs.modal', function(){
            if($("#show-big-player-mjob-btn").attr('data-video-type')=='upload')
            {
              mjobBigPlayer.play();    
            } 
              
          });

          //trigger pause video if modal hide
          $("#mjob-player-big-modal").on('hidden.bs.modal', function(){
            if($("#show-big-player-mjob-btn").attr('data-video-type')=='upload')
            {
              mjobBigPlayer.pause();
            }
            
          });
          
         /* end mjob big player */
          
    })
})(jQuery);