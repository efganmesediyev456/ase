<div>
    <div class="custom-container">
        <div style="margin-bottom: 40px" class="row">
            <div class="col-lg-4">
                <div>
                    <h1 style="font-weight: 500; font-size: 40px; color:#0E1B2F; margin-bottom: 14px">{{ __('front.news.title') }}</h1>
                    <p style="font-weight: 400; font-size: 16px; line-height: 24px; color: #1a1a1e">{{ __('front.news.description') }}</p>
                </div>
            </div>
        </div>
        <div class="swiper news-swiper">
            <div class="swiper-wrapper">
                @foreach($news as $post)
                    @if(isset($post->translateOrDefault($_lang)->title))
                        <div class="swiper-slide">
                            <div class="slide-content rounded-esi ">
                                <a  href="{{ route('news.show', $post->slug) }}"
                                    title="{{ $post->translateOrDefault($_lang)->title }}" class=" slide-content-a black_hover">
                                    <img src="{{ $post->image }}"
                                         alt="{{ $post->translateOrDefault($_lang)->title }}"
                                         style="object-position: top; "
                                    >
                                </a>
                                <div class="slide-content-content">
                                    <a href="{{ route('news.show', $post->slug) }}"
                                       title="{{ $post->translateOrDefault($_lang)->title }}"
                                       class=" text-white">{{ $post->translateOrDefault($_lang)->title }}
                                    </a>
                                </div>

                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>
    </div>
</div>



{{--<div >--}}

{{--    <style>--}}
{{--        .map-container {--}}
{{--            height: 100vh;--}}
{{--            margin: 0;--}}
{{--            display: flex;--}}
{{--            flex-direction: column;--}}
{{--            background-color: #808080; /* Gray background for the map */--}}
{{--        }--}}
{{--        .input-container {--}}
{{--            padding: 10px;--}}
{{--            background-color: #fff;--}}
{{--            border-bottom: 1px solid #e0e0e0;--}}
{{--            display: flex;--}}
{{--            gap: 10px;--}}
{{--            flex-wrap: wrap;--}}
{{--        }--}}
{{--        .input-container input {--}}
{{--            padding: 8px;--}}
{{--            font-size: 0.875rem;--}}
{{--            border: 1px solid #e0e0e0;--}}
{{--            border-radius: 4px;--}}
{{--            width: 200px;--}}
{{--            font-family: "Roboto", sans-serif;--}}
{{--        }--}}
{{--        gmpx-store-locator {--}}
{{--            width: 100%;--}}
{{--            height: 100%;--}}
{{--            --gmpx-color-surface: #fff;--}}
{{--            --gmpx-color-on-surface: #212121;--}}
{{--            --gmpx-color-on-surface-variant: #757575;--}}
{{--            --gmpx-color-primary: #1967d2;--}}
{{--            --gmpx-color-outline: #e0e0e0;--}}
{{--            --gmpx-fixed-panel-width-row-layout: 0; /* Hide the left panel */--}}
{{--            --gmpx-fixed-panel-height-column-layout: 0; /* Hide the panel in column layout */--}}
{{--            --gmpx-font-family-base: "Roboto", sans-serif;--}}
{{--            --gmpx-font-family-headings: "Roboto", sans-serif;--}}
{{--            --gmpx-font-size-base: 0.875rem;--}}
{{--            --gmpx-hours-color-open: #188038;--}}
{{--            --gmpx-hours-color-closed: #d50000;--}}
{{--            --gmpx-rating-color: #ffb300;--}}
{{--            --gmpx-rating-color-empty: #e0e0e0;--}}
{{--        }--}}
{{--        gmpx-store-locator::part(fixed-panel) {--}}
{{--            display: none !important;--}}
{{--        }--}}
{{--    </style>--}}

{{--<div class="map-container">--}}
{{--    <div class="input-container">--}}
{{--        <input type="text" id="city-input" placeholder="Enter city name">--}}
{{--        <input type="text" id="address-input" placeholder="Enter address">--}}
{{--    </div>--}}
{{--    <gmpx-api-loader key="AIzaSyDASLAyoHYDXN_rqvGpCV1PZjBSn2tfky0" solution-channel="GMP_QB_locatorplus_v11_cABD"></gmpx-api-loader>--}}
{{--    <gmpx-store-locator map-id="DEMO_MAP_ID"></gmpx-store-locator>--}}
{{--</div>--}}

{{--<script type="module" src="https://ajax.googleapis.com/ajax/libs/@googlemaps/extended-component-library/0.6.11/index.min.js"></script>--}}
{{--<script type="module">--}}
{{--    const CONFIGURATION = {--}}
{{--        "locations": [--}}
{{--            {"title":"ASE express","address1":"Uzeyir Hacibeyli 61B Bakı","address2":"Bakı 1000, Azerbaijan","coords":{"lat":40.37433736508527,"lng":49.84989902209016},"placeId":"ChIJufc3TKl9MEAR6qs1i21A9Bo"},--}}
{{--            {"title":"Aseshop","address1":"FQJM+G2R","address2":"Khachmaz, Azerbaijan","coords":{"lat":41.481500641351566,"lng":48.782461920237736},"placeId":"ChIJwfGFNAC3N0ARuZyB1BulfAg"}--}}
{{--        ],--}}
{{--        "mapOptions": {"center":{"lat":40.5,"lng":49.5},"fullscreenControl":true,"mapTypeControl":false,"streetViewControl":false,"zoom":8,"zoomControl":true,"maxZoom":17,"mapId":""},--}}
{{--        "mapsApiKey": "AIzaSyDASLAyoHYDXN_rqvGpCV1PZjBSn2tfky0",--}}
{{--        "capabilities": {"input":false,"autocomplete":false,"directions":false,"distanceMatrix":true,"details":false,"actions":false}--}}
{{--    };--}}

{{--    document.addEventListener('DOMContentLoaded', async () => {--}}
{{--        await customElements.whenDefined('gmpx-store-locator');--}}
{{--        const locator = document.querySelector('gmpx-store-locator');--}}
{{--        locator.configureFromQuickBuilder(CONFIGURATION);--}}

{{--        const cityInput = document.getElementById('city-input');--}}
{{--        const addressInput = document.getElementById('address-input');--}}
{{--        let originalLocations = [...CONFIGURATION.locations];--}}

{{--        cityInput.addEventListener('input', () => {--}}
{{--            const city = cityInput.value.trim().toLowerCase();--}}
{{--            if (!city) {--}}
{{--                locator.configureFromQuickBuilder({...CONFIGURATION, locations: originalLocations});--}}
{{--                return;--}}
{{--            }--}}
{{--            const filteredLocations = originalLocations.filter(location =>--}}
{{--                location.address2.toLowerCase().includes(city)--}}
{{--            );--}}
{{--            locator.configureFromQuickBuilder({...CONFIGURATION, locations: filteredLocations});--}}
{{--        });--}}

{{--        addressInput.addEventListener('input', async () => {--}}
{{--            const address = addressInput.value.trim();--}}
{{--            if (!address) {--}}
{{--                locator.configureFromQuickBuilder({...CONFIGURATION, locations: originalLocations});--}}
{{--                return;--}}
{{--            }--}}

{{--            try {--}}
{{--                const response = await fetch(--}}
{{--                    `https://maps.googleapis.com/maps/api/geocode/json?address=${encodeURIComponent(address)}&key=${CONFIGURATION.mapsApiKey}`--}}
{{--                );--}}
{{--                const data = await response.json();--}}
{{--                if (data.status === 'OK' && data.results.length > 0) {--}}
{{--                    const { lat, lng } = data.results[0].geometry.location;--}}
{{--                    const sortedLocations = originalLocations--}}
{{--                        .map(location => {--}}
{{--                            const distance = Math.sqrt(--}}
{{--                                Math.pow(location.coords.lat - lat, 2) + Math.pow(location.coords.lng - lng, 2)--}}
{{--                            );--}}
{{--                            return { ...location, distance };--}}
{{--                        })--}}
{{--                        .sort((a, b) => a.distance - b.distance);--}}
{{--                    locator.configureFromQuickBuilder({--}}
{{--                        ...CONFIGURATION,--}}
{{--                        locations: sortedLocations,--}}
{{--                        mapOptions: { ...CONFIGURATION.mapOptions, center: { lat, lng }, zoom: 12 }--}}
{{--                    });--}}
{{--                }--}}
{{--            } catch (error) {--}}
{{--                console.error('Geocoding error:', error);--}}
{{--            }--}}
{{--        });--}}
{{--    });--}}
{{--</script>--}}

{{--</div>--}}


<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<style>
    .news-swiper {
        width: 100%;
    }

    .news-swiper .slide-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        height: 100%;
        overflow: hidden;
    }
    .news-swiper .slide-content-a{
        display:inline-block;
        width: 100%;
    }

    .news-swiper .slide-content img{
        width: 100% !important;
        height: 228px !important;
        object-fit: cover;
    }

    .slide-content-content{
        background:#0e1b2f ;
        padding: 18px;
        color: #fff;
        width: 100%;
        min-height: 130px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .slide-content-content a{
        font-weight: 500;
        font-size: 18px;
        line-height: 24px;

    }
    .slide-content-content a:hover{
        color: #fff !important;
    }


    .swiper-button-prev,
    .swiper-button-next {
        color: #0E1B2F;
        background: white !important;
        width: 40px !important;
        height: 40px !important;
        border-radius: 50% !important;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2) !important;
    }

    .swiper-button-prev::after,
    .swiper-button-next::after {
        font-size: 18px !important;
        font-weight: bold !important;
        color: black !important;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        new Swiper('.news-swiper', {
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