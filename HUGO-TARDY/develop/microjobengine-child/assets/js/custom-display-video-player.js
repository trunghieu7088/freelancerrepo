(function ($) {
    $(document).ready(function () { 
        
        const portfolioVideoPlayers = document.querySelectorAll('.portfolio-item-video-player');
    
        portfolioVideoPlayers.forEach(function (playerElement) {
            const custom_video_players = new Plyr(playerElement,{ controls: ['current-time']});
        });    
        
        
        $(".show_central_video_player").click(function(){
            $(".video_player_central_topoverlay").css('display','flex');
            $("#central_video_player_src").attr('src',$(this).attr('data-video-url'));
            $("#central_video_player_src").attr('type',$(this).attr('data-mime-type'));
            $("#central_video_player")[0].load();            
            const central_video_player=new Plyr("#central_video_player",{ controls: ['play-large','progress','current-time','mute','volume','fullscreen']});
            central_video_player.play();
        });

        $("#close_modal_video_player_btn").click(function(){
            central_video_player.pause();
            $(".video_player_central_topoverlay").css('display','none');
        });
    })
})(jQuery);