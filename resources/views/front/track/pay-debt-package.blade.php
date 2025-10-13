@extends('front.layout')

@section('content')
    @if (! $item)
        <div class="alert alert-warning">
            {{ __('front.package_not_found') }}
        </div>
    @else
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="wrapper-content">
                        <div class="contact-form mb60">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="col-md-offset-2 col-md-8 col-sm-12 col-xs-12">
                                        <div class="mb60  section-title text-center  ">
                                            <h1 id="info_section">{{ __('front.pay_page_title') }}</h1>
                                        </div>
                                        @if ( request()->has('success'))
                                            <div class="alert alert-success"
                                                 role="alert">{{ __('front.was_paid') }}</div>
                                        @endif
                                        @if ( request()->has('error'))
                                            <div class="alert alert-danger"
                                                 role="alert">{{ request()->get('error') }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div></div></div></div>
            </div>
            <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-form-validator/2.3.26/jquery.form-validator.min.js"
                    defer></script>
            @if(in_array(app()->getLocale(), ['az', 'ru']))
                <script src="{{ asset('langs/form/' . app()->getLocale() . '.js') }}?v=1.0.0"></script>
    @endif
    @endif
@endsection
