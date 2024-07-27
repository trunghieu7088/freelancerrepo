(function ($) {
    $(document).ready(function () { 
        
        const portfolioVideoPlayers = document.querySelectorAll('.portfolio-item-video-player');
    
        portfolioVideoPlayers.forEach(function (playerElement) {
            const custom_video_players = new Plyr(playerElement,{ controls: ['current-time']});
        });    
        
 
    })
})(jQuery);