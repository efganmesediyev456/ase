@extends('front.layout')

@section('content')
    @include('front.sections.page-header')

    <div class="">
        <!-- content start -->
        <div class="custom-container section-space80">
            <div class="wrapper-content bg-white pinside40 box-shadow-esi rounded-esi">
                <div class="row">
                    <div class="col-md-12">
                        <div class="post-holder">
                            <div class="post-block mb40">
                                <div class="bg-white">
                                    <div  style="gap: 30px; align-items: start; margin-bottom: 40px" class="login-flex">
                                        <h1 class="title-blue">{{ $single->translateOrDefault($_lang)->title }}</h1>
                                        <img  style="object-fit: cover; height:320px  " src="{{ $single->image }}"
                                              alt="{{ $single->translateOrDefault($_lang)->title }}"
                                              class=" rounded-esi image-news">
                                    </div>
                                    {{--                                    <p class="meta">--}}
                                    {{--                                        <span class="meta-date">{{ $single->created_at->format('M d,Y') }}</span>--}}
                                    {{--                                    </p>--}}
                                    <div class="mt20 content-news">
                                        {!! $single->translateOrDefault($_lang)->content !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "BreadcrumbList",
            "itemListElement": [
                {
                    "@type": "ListItem",
                    "position": 1,
                    "name": "{{ __('front.menu.home') }}",
            "item": "{{ route('home') }}"
        },
        {
            "@type": "ListItem",
            "position": 2,
            "name": "{!! __('front.menu.news') !!}",
            "item": "{{ route('news') }}"
        },
        {
            "@type": "ListItem",
            "position": 3,
            "name": "{{ $single->translateOrDefault($_lang)->title }}",
            "item": "{{ URL::current() }}"
        }
	]
}
    </script>
    <script type="application/ld+json">
        {
          "@context": "http://schema.org",
          "@type": "NewsArticle",
          "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "{{ URL::current() }}"
              },
              "headline": "{{ $single->translateOrDefault($_lang)->title }}",
              "articleBody": "{!! strip_tags($single->translateOrDefault($_lang)->content) !!}",
              "keywords": "",
              "url": "{{ URL::current() }}",
              "articleSection": null,
              "description": "{!! strip_tags($single->meta_description) !!}",
              "author": [
                {
                  "@type": "Organization",
                  "name": "Aseshop"
                }
              ],
              "publisher": {
                "@type": "Organization",
                "name": "Aseshop"
              },
              "image": [
                {
                  "@type": "ImageObject",
                  "url": "{{ asset($single->image) }}"
                }
              ]
            }

    </script>

@endsection