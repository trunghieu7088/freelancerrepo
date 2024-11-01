(function ($) {

  //apply youtube api frame for youtube player in video list page
  function applyYTVideoList()
  {
    var ytBigPlayerList;
    if($("#yt-video-player-big-modal").length > 0)
    {
          window.YT.ready(function() {
            ytBigPlayerList= new window.YT.Player("yt-big-player-video-list", {         
              playerVars: {
                'playsinline': 1,
                'autoplay': 0,
                'mute': 0,
                'loop': 1,
                'color': 'white',
                'controls': 0,
                'playsinline': 1,
                'rel': 0,
                'enablejsapi': 1,                
            },
            events: {
              onReady: function(event) {
                
                $("#yt-video-player-big-modal").on('shown.bs.modal', function(){

                 // var videoUrl= $("#yt-big-player-video-list").attr('data-video-url');
                 //$("#yt-big-player-video-list").attr('src',videoUrl);
                 //ytBigPlayerList.loadVideoByUrl(videoUrl); 
                  var youtubeID=$("#yt-big-player-video-list").attr('data-youtube-id');
                                    
                  ytBigPlayerList.loadVideoById(youtubeID);
                  ytBigPlayerList.loadPlaylist({playlist: [youtubeID]});                 
                  ytBigPlayerList.playVideo();  
                                                                      
                });

                $("#yt-video-player-big-modal").on('hidden.bs.modal', function(){                  
                  ytBigPlayerList.pauseVideo();                                                                       
                });

              }         
            },
            });
          });
    }
  }

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

  function loadVideos(sliderInstance) {
    const youtubePlayers = []; // Mảng lưu các playerInstance
    $('.yt-video-player-container').each(function(index, element) {
        var playerInstance;
        var iframe = $(element).find('.yt-short-video');
        var iframeId = 'youtube-player-' + index; // Tạo id duy nhất cho mỗi iframe
        $(iframe).attr('id', iframeId); // Gán id cho mỗi iframe

        // Khởi tạo player cho iframe hiện tại
        playerInstance = new window.YT.Player(iframeId, {
            playerVars: {
                'playsinline': 1
            },
            events: {
                onReady: function(event) {
                  youtubePlayers[index] = playerInstance; // Lưu playerInstance vào mảng
                  var viewportYT=$(window).width();
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
                    
                    $(element).find('.open-yt-btn').on('click', function() {
                      if(viewportYT > 1024)
                      {
                            //console.log('Player Instance for iframe ID:', iframeId);
                          //console.log(playerInstance);                                        
                          var youtube_big_play_info=JSON.parse($(this).attr('data-video-info'));
                          $("#yt-video-player-big-modal").modal();              
                          //apply data to modal                            
                          $("#yt-video-owner-name").text(youtube_big_play_info.ownerName);
                          $("#yt-video-owner-avatar").attr('src',youtube_big_play_info.ownerAvatarURL);
                          $("#yt-video-owner-score").attr('data-score',youtube_big_play_info.ownerRating);
                          $("#yt-video-owner-profile-wrap-link").attr('href',youtube_big_play_info.ownerProfileURL);
                          $("#yt-video-owner-viewpf-btn").attr('href',youtube_big_play_info.ownerProfileURL);
                          $("#yt-video-owner-location").text(youtube_big_play_info.ownerLocation);
                          $("#yt-video-owner-language").text(youtube_big_play_info.ownerLanguage);
                          $("#yt-video-caption-content").html(youtube_big_play_info.videoCaption);
            
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
                          $("#yt-serviceListSection").html('');
                          let cloneElement= $("#yt-short-video-serviceList-"+youtube_big_play_info.ID).clone();    
                          cloneElement.css('display','block');              
                          cloneElement.appendTo($("#yt-serviceListSection"));

                          //apply youtube id link
                        //  var youtube_big_play_url='https://www.youtube.com/embed/'+youtube_big_play_info.videoInfo.url+'?autoplay=0&mute=0&loop=1&color=white&controls=0&playsinline=1&rel=0&enablejsapi=1&playlist='+youtube_big_play_info.videoInfo.url;
                          //$("#yt-big-player-video-list").attr('data-video-url',youtube_big_play_url);
                          $("#yt-big-player-video-list").attr('data-youtube-id',youtube_big_play_info.videoInfo.url);                 
                          
                      }
                      
                   }); //end onclick button
                   
                   sliderInstance.on('slideChange', function () {  
                      if(viewportYT < 1024)
                      {
                          // Tạm dừng tất cả các video khác
                          youtubePlayers.forEach((player, index) => {
                               /* if (index !== activeSlideIndex && player && player.pauseVideo) {
                                    player.pauseVideo();
                                } */
                                player.pauseVideo();
                            });
                                                  
                          var activeSlideIndex = sliderInstance.activeIndex; 
                          var current_slide_dataYT=sliderInstance.slides[activeSlideIndex].attributes['data-swiperb-item'].value;  
                             
                            youtubePlayers.forEach((player, index) => {
                              /* if (index !== activeSlideIndex && player && player.pauseVideo) {
                                   player.pauseVideo();
                               } */                          
                              var currentYTClip=player.getIframe();  
                              var currentYTClipID=currentYTClip.attributes['data-swiperb-item'].value                
                              //console.log(currentYTClipID);
                              if(current_slide_dataYT == currentYTClipID)
                              {
                                player.playVideo();
                              }
                              
                           });
                      }
                     
                    
                    }); // end slider on change event

                },
                onStateChange: function(event) {
                    // var videoStatuses = Object.entries(window.YT.PlayerState);
                    //console.log('State Change:', iframeId, videoStatuses.find(status => status[1] === event.data)[0]);
                }
            }
        });
    });
}

    $(document).ready(function() {
      //init viewport variable
      var viewportWidth = $(window).width();

        //init slider
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
  
           360: {
              slidesPerView: 1,
              spaceBetween: 0,                  
            },
            640: {
              slidesPerView: 2,
              spaceBetween: 0,             
            },
            768: {
              slidesPerView: 2,
              spaceBetween: 0,
            },
            810:
            {
              slidesPerView: 2,
              spaceBetween: 0,
            },
            1024: {
              slidesPerView: 3,
              spaceBetween: 0,
            },
      
            1920: {
              slidesPerView: 4,                
              spaceBetween: 0,
            },
          },
  
        });    

        $(".video-mje-next-area").click(function(){
          short_videos_slider.slideNext();          
         });
     
        $(".video-mje-prev-area").click(function(){
          short_videos_slider.slidePrev();          
         });

         //hide or show next / prev buttons function
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

        //hide or show next / prev buttons when slide change
        short_videos_slider.on('slideChange', function () {         
          toggleNavigationButtons();    
          
        });

        //close the info box when change slide ( only mobile)
        short_videos_slider.on('beforeTransitionStart', function () {  

          if($(window).width() < 1024)         
            {              
                let slide_box=short_videos_slider.slides[short_videos_slider.activeIndex].attributes['data-swiperb-item'].value;
                $("[data-info-box='"+ slide_box+"']").css('display','none'); 
            }
        });
        
             
        toggleNavigationButtons();
      
      //auto trigger pop-up after 2 seconds when page finished loading
        if($(".video-area-description").length > 0)
        {
          const autoTriggerPlayer=setTimeout(function(){           
              $("#mjob-player-big-modal").modal('show');                                      
           }, 1000);
        }

        //turn off loading effect after 2 seconds
        if($(".loading-frame").length > 0)
          {
            setTimeout(function(){ 
                $(".loading-frame").css('display','none');
             }, 1000);
          }

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
            loadVideos(short_videos_slider);
            applyYTmjob();
            applyYTVideoList();
        });
    });
       
    
               
        //init big player for local video ( video list ) on desktop view
        const bigVideoPlayer = new Plyr("#big-local-video",{ 
            controls: ['play-large','progress','current-time','mute'], 
            fullscreen: { enabled: false, fallback: false } 
          });
        
      
          
        const localVideoPlayers = document.querySelectorAll('.local-item-video-player');
        if(viewportWidth > 1024)
        {
          //init video player ( local video ) for video list on desktop / laptop view
              localVideoPlayers.forEach(function (playerElement) {
                  const localPlayer = new Plyr(playerElement,{ controls: false, muted: false, clickToPlay: false});
                  
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
        }
        else
        {            
           //init video player ( local video ) for video list on desktop / laptop view
          const customPlayers=Plyr.setup('.local-item-video-player',{ controls: ["progress","current-time","mute"], 
            muted: false, 
            fullscreen: {
              enabled:false,
            },
            clickToPlay: false,
          });

          //adding play event when onclick ( mobile view)
          if($(window).width < 1024 && customPlayers)
          {
            customPlayers.forEach(function(player) {
              player.on('click', function() {
                  if (player.playing) {
                      player.pause();  // Dừng video nếu đang phát
                  } else {
                      player.play();   // Phát video nếu đang dừng
                  }
              });
            });
          }
        

          //handling slide change for mobile view 
          short_videos_slider.on('slideChange', function () {      
            if(viewportWidth < 1024)              
            {
                  let current_slide=short_videos_slider.activeIndex;
                  var current_slide_data=short_videos_slider.slides[current_slide].attributes['data-swiperb-item'].value;                                   
      
                  if(current_slide_data)
                  {
                      //pause all player first
                      if(customPlayers)
                        { 
                          customPlayers.forEach(pauseplayer => {
                            pauseplayer.pause();                  
                          }); 
                        }
      
                    //find the player of the current slide and try to play
                      var targetPlayer = customPlayers.find(player => {                   
                          if(player.config.ID == current_slide_data)
                          {
                            return player;              
                          }                       
                      });
                      if(targetPlayer)
                      {                
                        targetPlayer.play();
                      }              
                  }                              
            }
          
          });
           
        }
         

         //trigger when modal big player (video list ) show and hide
         $("#video-player-big-modal").on('shown.bs.modal', function(){          
            bigVideoPlayer.play();                        
        });

        $("#video-player-big-modal").on('hidden.bs.modal', function(){          
            bigVideoPlayer.pause();                    
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
        if($("#mjob-modal-video-player").length > 0)
        {
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
        }
         /* end mjob big player */
            $('.close-box-js').click(function(){              
              let close_box=$(this).attr('data-close-box');
              $("[data-info-box='"+close_box+"']").slideUp();                              
          });

          if(viewportWidth < 1024)
          {
              //open box info for mobile view ( Youtube video)
              $('.open-yt-btn').click(function(){              
                let open_box=$(this).attr('data-box-item');
                $("[data-info-box='"+open_box+"']").slideDown(); 
              });

               //open box info for mobile view ( upload video)
               $('.open-box-info').click(function(){              
                let open_box=$(this).attr('data-box-item');
                $("[data-info-box='"+open_box+"']").slideDown(); 
              });
               
          }

          if(viewportWidth > 1024)
          {
            $('.open-box-info').click(function(){              
              $("[data-video-item='"+ $(this).attr('data-box-item')+"']").trigger('click'); 
                                      
            });
          }
          
          /* edit mjob form */
          $(document).on('click','#remove-video-player',function(){            
              $("#mjob-video-edit-wrapper").fadeOut();
              $("#undo-video-player").fadeIn();
              $(this).fadeOut();
              $("#remove-current-video").val('remove');
              $("#current_video_caption").fadeOut();
          });

          $(document).on('click','#undo-video-player',function(){            
            $("#mjob-video-edit-wrapper").fadeIn();
            $("#remove-video-player").fadeIn();
            $(this).fadeOut();
            $("#remove-current-video").val('');
            $("#current_video_caption").fadeIn();
          });

          /* end edit mjob form */

    })
})(jQuery);
