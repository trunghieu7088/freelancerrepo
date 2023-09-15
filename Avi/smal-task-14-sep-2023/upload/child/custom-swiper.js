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
              slidesPerView: 2,
              spaceBetween: 0,
            },
            640: {
              slidesPerView: 2,
              spaceBetween: 20,
            },
            768: {
              slidesPerView: 2,
              spaceBetween: 5,
            },
            1024: {
              slidesPerView: 5,
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
     
  
    });
  })(jQuery);