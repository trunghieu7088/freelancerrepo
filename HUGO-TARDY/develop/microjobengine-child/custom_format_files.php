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
                'mp4' => 'video/MP4',
                'doc' =>   'application/msword',
                'docx' =>   'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'pdf' => 'application/pdf',
                'psd' => 'application/psd',
                'zip' => 'multipart/x-zip',
                'mp3' => 'audio/mpeg',
                'wav' => 'audio/wave',
                'aiff' => 'audio/aiff', 
                'avi' =>'video/x-msvideo',
                'webm' => 'video/webm',
                'wmv'=>'video/x-ms-wmv',
            ));
        }
        // if user is normal user
        $mimes = array_merge($mimes, array(
            'doc' =>   'application/msword',
            'docx' =>   'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'pdf' => 'application/pdf',
            'psd' => 'application/psd',
            'zip' => 'multipart/x-zip',  
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wave',
            'aiff' => 'audio/aiff',   
            'mp4' => 'video/MP4',   
            'flv' => 'video/x-flv',    
            'avi' =>'video/x-msvideo',
            'webm' => 'video/webm',
            'wmv'=>'video/x-ms-wmv',


        ));
        return $mimes;
    }
    add_filter('et_upload_file_upload_mimes', 'child_mjob_add_mime_types');
    add_filter('upload_mimes', 'child_mjob_add_mime_types');