@extends('front.layout')

@section('content')
    @include('front.sections.page-header')

    <div class=" custom-container section-space120">

        <div class="swiper news-swiper">
            <div class="swiper-wrapper">
                @foreach($news as $post)
                    @if(isset($post->translateOrDefault($_lang)->title))
                        <div class="swiper-slide">
                            <div class="slide-content rounded-esi">
                                <a  href="{{ route('news.show', $post->slug) }}"
                                    title="{{ $post->translateOrDefault($_lang)->title }}" class="black_hover slide-content-a">
                                    <img src="{{ $post->image }}"
                                         alt="{{ $post->translateOrDefault($_lang)->title }}"
                                         style="object-position: top; "
                                         class="img-fluid"
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
        <!-- content start -->
        {{--        <div class="container">--}}
        {{--            <div class="row">--}}
        {{--                <div class="col-md-12">--}}
        {{--                    <div class="wrapper-content bg-white pinside40">--}}
        {{--                        <div class="row">--}}
        {{--                            @foreach($news->chunk(3) as $items)--}}
        {{--                                <div class="row">--}}
        {{--                                    @foreach($items as $post)--}}
        {{--                                        <div class="col-xl-4 col-lg-4 col-md-4 col-sm-6 col-12">--}}
        {{--                                            <div class="post-caption-block post-block mb30">--}}
        {{--                                                <div class="post-caption-img lift">--}}
        {{--                                                    <a  title="{{ $post->translateOrDefault($_lang)->title }}" href="{{ route('news.show', $post->slug) }}" class="imghover">--}}
        {{--                                                        <img src="{{ $post->image }}"--}}
        {{--                                                             alt="{{ $post->translateOrDefault($_lang)->title }}"--}}
        {{--                                                             class="img-fluid">--}}
        {{--                                                    </a>--}}
        {{--                                                </div>--}}
        {{--                                                <div class="post-caption-content">--}}
        {{--                                                    <h3><a  title="{{ $post->translateOrDefault($_lang)->title }}" href="{{ route('news.show', $post->slug) }}" class=" text-white">{{ $post->translateOrDefault($_lang)->title }}</a></h3>--}}
        {{--                                                    <p class="meta"><span class="meta-date text-white">{{ $post->created_at->format('M d,Y') }}</span></p>--}}
        {{--                                                </div>--}}
        {{--                                            </div>--}}
        {{--                                        </div>--}}
        {{--                                    @endforeach--}}
        {{--                                </div>--}}
        {{--                            @endforeach--}}

        {{--                            <div class="col-md-12 text-center col-sm-12 col-xs-12">--}}
        {{--                                {{ $news->render() }}--}}
        {{--                            </div>--}}
        {{--                        </div>--}}
        {{--                    </div>--}}
        {{--                </div>--}}
        {{--            </div>--}}
        {{--        </div>--}}
    </div>



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
@endsection