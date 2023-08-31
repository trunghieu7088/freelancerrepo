(function ($) {
    $(document).ready(function () {
        const swiper = new Swiper('.swiper', {
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
        });
        window.addEventListener('scroll', ()=> {
            if (window.scrollY > 0) {
                document.querySelector('#jsTopBar').classList.add('active')
            } else {
                document.querySelector('#jsTopBar').classList.remove('active')
            }
            $('.js-counter').scrollTop  = 1000
            if (window.scrollY >= 900 && window.scrollY <= 1100 || window.scrollY >= 0 && window.scrollY <= 100) {
                $('.js-number').each((index,item) => {
                    let $item = $(item)
                    let e = $item.data('count');
                    let p = $item.data('per');
                    let s = 0;
                    let counter = setInterval(()=>{
                        s = s + p;
                        $item.html(new Intl.NumberFormat().format(s))
                        if (s >= e) {
                            $item.html(new Intl.NumberFormat().format(e))
                            clearInterval(counter)
                        }
                    }, 10)
                })
            }
        })
        let status = true;
        $('.js-number').hover(function (event) {
            if (status === true) {
                let $item = $(this)
                status = false;
                let e = $item.data('count');
                let p = $item.data('per');
                let s = 0;
                let counter = setInterval(()=>{
                    s = s + p;
                    $item.html(new Intl.NumberFormat().format(s))
                    if (s >= e) {
                        $item.html(new Intl.NumberFormat().format(e))
                        status = true;
                        clearInterval(counter)
                    }
                }, 10)
            }
        })
        $('#jsToggleMobileMenuButton').on('click', function (e){
            $('#jsMobileMenu').toggleClass('show')
        })
    });
})(jQuery);