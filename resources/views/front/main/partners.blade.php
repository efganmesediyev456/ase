<div class="section-space60">
    <div class="custom-container">
        <div style="margin-bottom: 60px" class="row">
            <div class="col-lg-12 col-md-12 col-12 mb-4">
                <h4 class="mb-5" style="font-weight: 400; font-size: 24px; line-height: 1.5">{{ __('front.tariff.partners') }}</h4>
            </div>
        </div>
        <div class="swiper partner-slider">
            <div class="swiper-wrapper">
                @php
                    $partnerLogos = [
                        ['src' => 'lender-logo-1.png', 'alt' => 'Turkish Airlines'],
                        ['src' => 'lender-logo-2.png', 'alt' => 'IATA'],
                        ['src' => 'lender-logo-3.png', 'alt' => 'Asitan'],
                        ['src' => 'lender-logo-4.png', 'alt' => 'FIATA'],
                        ['src' => 'lender-logo-5.png', 'alt' => 'OCS Ana Group'],
                        ['src' => 'lender-logo-6.png', 'alt' => 'Major Logistics'],
                    ];
                @endphp
                @foreach([1, 2] as $iteration)
                    @foreach($partnerLogos as $logo)
                        <div class="swiper-slide rounded-esi">
                            <div>
                                <img src="{{ asset('front/new/' . $logo['src']) }}" alt="{{ $logo['alt'] }}" class="img-fluid" style="object-fit: cover; max-height: 88px">
                            </div>
                        </div>
                    @endforeach
                @endforeach
                {{--                @foreach($partnerLogos as $logo)--}}
                {{--                    <div class="swiper-slide rounded-esi">--}}
                {{--                        <div>--}}
                {{--                            <img src="{{ asset('front/new/' . $logo['src']) }}" alt="{{ $logo['alt'] }}" class="img-fluid" style="object-fit: cover; max-height: 88px">--}}
                {{--                        </div>--}}
                {{--                    </div>--}}
                {{--                @endforeach--}}
            </div>
            <div style="display: none" class="swiper-button-prev"></div>
            <div style="display: none"  class="swiper-button-next"></div>
        </div>
    </div>




</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<style>
    .partner-slider {
        width: 100%;
    }

    .partner-slider .swiper-slide {
        display: flex;
        justify-content: center;
        align-items: center;
        background: #E0EFFF;
        height: 88px;
        padding: 14px;
    }


</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        new Swiper('.partner-slider', {
            slidesPerView: 1,
            spaceBetween: 15,
            loop: true,
            autoplay: {
                delay: 3000,
                disableOnInteraction: false,
            },

            breakpoints: {
                320: {
                    slidesPerView: 2,
                    spaceBetween: 15
                },
                540: {
                    slidesPerView: 3,
                    spaceBetween: 15
                },
                768: {
                    slidesPerView: 4,
                },
                992: {
                    slidesPerView: 6,
                }
            }
        });
    });
</script>