(function ($) {
    $(document).ready(function () {
  
   
            var swiper = new Swiper(".mySwiper", {
          slidesPerView: 4,
          //centeredSlides: true,
          spaceBetween: 0,    
          //resistance: false,  
          speed:200,
          slidesPerGroup:2,                         
          //resistanceRatio: 0.2,
          autoplay:
          {
            enabled: false,
            delay:1000,
            stopOnLastSlide:true
          },
          freemode:
          {
            enabled: true,
            sticky: true,
          },
          watchOverflow:true,
           navigation: {
            nextEl: ".swiper-button-next-unique",          
          },
          breakpoints: {
  
           300: {
              slidesPerView: 2.1,
              spaceBetween: 0,
            },
            640: {
              slidesPerView: 2.1,
              spaceBetween: 20,
            },
            768: {
              slidesPerView: 2.1,
              spaceBetween: 5,
            },
            1024: {
              slidesPerView: 5.2,
              spaceBetween: 5,
            },
          },
  
        });
  
     $("#nextslidep").click(function(){
       swiper.slideNext();
  
      });
  
     $("#prevslidep").click(function(){
       swiper.slidePrev();
  
      });

      var carousel_slider = new Swiper(".carouselswiper", {
        slidesPerView: 3,
        //centeredSlides: true,
        spaceBetween: 0,    
        //resistance: false,  
        speed:200,
        slidesPerGroup:1,                         
        //resistanceRatio: 0.2,
        autoplay:
        {
          enabled: false,
          delay:1000,
          stopOnLastSlide:true
        },
        freemode:
        {
          enabled: true,
          sticky: true,
        },
         pagination: {                       //pagination(dots)
          el: '.swiper-pagination',
          clickable: true,
          dynamicBullets: true,
          dynamicMainBullets: 4,

      },
        
        breakpoints: {

         300: {
            slidesPerView: 1.1,
            spaceBetween: 0,
          },
          640: {
            slidesPerView: 2.1,
            spaceBetween: 20,
          },
          768: {
            slidesPerView: 2.1,
            spaceBetween: 1,
          },
          1024: {
            slidesPerView: 3.1,
            spaceBetween: 1,
          },
        },

      });    
      
      
     $("#profile_nextslidep").click(function(){
      carousel_slider.slideNext();
 
     });
 
    $("#profile_prevslidep").click(function(){
      carousel_slider.slidePrev();
 
     });
     
  
    });
  })(jQuery);