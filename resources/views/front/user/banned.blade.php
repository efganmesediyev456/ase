@extends('front.layout')

@section('content')
    @include('front.sections.page-header')

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="wrapper-content bg-white pinside20">
                    <div class="alert alert-warning"
                         role="alert">{{ __('front.banned') }}  <a href="tel:{{ $setting->phone }}"> {{ $setting->phone }}</a></div>
                </div>
            </div>
        </div>
    </div>

@endsection