@extends('front.layout')

@section('content')
    <!-- .site-header -->
    @include('front.main.get-tracking')
    @include('front.main.stores')
    @include('front.main.instagram-slider')
    @include('front.main.tariffs')
    @include('front.main.our-services')
{{--    @include('front.main.calculator')--}}
    @include('front.main.steps')
    @include('front.main.articles')
    @include('front.main.map')
    @include('front.main.partners')

{{--    @include('front.widgets.reason-to-choose')--}}
{{--    @include('front.main.stats')--}}
{{--    --}}{{--    @include('front.main.articles')--}}
    @include('front.main.json')
@endsection
