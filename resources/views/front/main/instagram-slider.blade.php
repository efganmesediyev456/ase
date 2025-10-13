<div style="margin-top: 100px" class="">
    <div class="custom-container">
        <div style="margin-bottom: 50px" class="row">
            <div class="col-lg-5">
                <div>

                        <a  href="https://www.instagram.com/aseshop.az?igsh=dTJ6cWppMm04eGYz"  target="_blank">
                            <h1 class="hover-h1" >{{ __('front.instagram.title') }}</h1>
                        </a>
                    <p style="font-weight: 400; font-size: 16px; line-height: 24px; color: #1a1a1e">{{ __('front.instagram.sub_title') }}</p>
                </div>
            </div>
        </div>
        <div>
            <div class="swiper promo-swiper">
                <div class="swiper-wrapper">
                    @foreach($instagrams as $instagram)
                        <div class="swiper-slide">
                            <div class="slide-content">
                                <a href="{{$instagram->url}}"><img src="{{asset($instagram->image)}}" alt=""></a>
                            </div>
                        </div>
                    @endforeach

                </div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            </div>
        </div>
    </div>
</div>


<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<style>
    .promo-swiper {
        width: 100%;
        padding: 20px;
    }

    .slide-content {
        display: flex;
        justify-content: space-around;
        align-items: center;
        height: 100%;
    }



</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        new Swiper('.promo-swiper', {
            loop: true,
            autoplay: {
                delay: 3000,
                disableOnInteraction: false,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            breakpoints: {
                0: {
                    slidesPerView: 1,
                    spaceBetween: 10
                },
                480: {
                    slidesPerView: 2,
                    spaceBetween: 15
                },
                768: {
                    slidesPerView: 3,
                    spaceBetween: 20
                }
            }
        });
    });
</script>