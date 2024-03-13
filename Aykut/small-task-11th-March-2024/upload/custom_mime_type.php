<?php
  function child_mjob_add_mime_types($mimes)
  {
      /**
       * admin can add more file extension
       */
      if (current_user_can('manage_options')) {
          return array_merge($mimes, array(
              'ac3' => 'audio/ac3',
              'mpa' => 'audio/MPA',
              'flv' => 'video/x-flv',
              'svg' => 'image/svg+xml',              
              'doc' =>   'application/msword',
              'docx' =>   'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
              'pdf' => 'application/pdf',
              'psd' => 'application/psd',
              'zip' => 'multipart/x-zip',     
              //custom code
              'mp4' => 'video/MP4',
              'mp3' => 'audio/mpeg',
              'aac' => 'audio/aac',
              'gif' => 'image/gif',
              'pptx' =>'application/vnd.openxmlformats-officedocument.presentationml.presentation',
              'pptm'  =>  'application/vnd.ms-powerpoint.presentation.macroEnabled.12',              
              'ppt'   =>   'application/vnd.ms-powerpoint',
              'mht' =>  'multipart/related',   
          ));
      }
      // if user is normal user
      $mimes = array_merge($mimes, array(
          'doc' =>   'application/msword',
          'docx' =>   'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
          'pdf' => 'application/pdf',
          'psd' => 'application/psd',
          'zip' => 'multipart/x-zip',   
          
          //custom code
          'mp4' => 'video/MP4',
          'mp3' => 'audio/mpeg',
          'aac' => 'audio/aac',
          'gif' => 'image/gif',
          'pptx' =>'application/vnd.openxmlformats-officedocument.presentationml.presentation',
          'pptm'  =>  'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
          'ppt'   =>   'application/vnd.ms-powerpoint',
          'mht' =>  'multipart/related',


      ));
      return $mimes;
  }
  add_filter('et_upload_file_upload_mimes', 'child_mjob_add_mime_types');
  add_filter('upload_mimes', 'child_mjob_add_mime_types');