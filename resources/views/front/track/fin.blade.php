{{--@extends('front.layout')--}}

{{--@section('content')--}}
{{--@if (! $item)--}}
{{--    <div class="alert alert-warning">--}}
{{--        {{ __('front.package_not_found') }}--}}
{{--    </div>--}}
{{--@else--}}
{{--   <div class="container">--}}
{{--     <div class="row">--}}
{{--        <div class="col-md-12">--}}
{{--            <div class="wrapper-content bg-white pinside40">--}}
{{--               <div class="contact-form mb60">--}}
{{--        <div class="row">--}}
{{--            <div class="col-lg-12">--}}
{{--		<div class="col-md-offset-2 col-md-8 col-sm-12 col-xs-12">--}}
{{--                     <div class="mb60  section-title text-center  ">--}}
{{--                     <h1 id="info_section">{{ __('front.fin_page_title') }}</h1>--}}
{{--                     @if (session('success'))--}}
{{--                            <div class="alert alert-success"--}}
{{--                                 role="alert">{{ __('front.fin_was_updated') }}</div>--}}
{{--                     @endif--}}
{{--                     </div>--}}
{{--                </div>--}}
{{--                @if (!session('success'))--}}
{{--                {{ Form::open(['class' => 'loan-eligibility-form', 'files' => true]) }}--}}
{{--                <div class="row" id="fin">--}}
{{--                    <div class="col-sm-12 col-lg-8">--}}
{{--                	<h3>{{__('front.fin_title',['track'=>$item->tracking_code])}}</h3>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="row" id="fin">--}}
{{--                    <div class="col-sm-12 col-lg-3">--}}
{{--                        <div data-popup="popover" title="FIN" data-placement="top" data-trigger="hover"--}}
{{--                              data-html="true"--}}
{{--                              data-content="<img style='width: 100%' src='{{ asset('front/images/fin.png') }}'/>"--}}
{{--                              class="form-group has-feedback has-feedback-left {{ ($errors->has('fin') || (isset($nulled) && ! is_null($nulled))) ? 'has-error' : '' }}">--}}
{{--                            <input placeholder="FİN" id="fin" type="text"--}}
{{--                                class="form-control" name="fin"--}}
{{--                                value="{{ old('fin', $item->fin) }}"--}}
{{--                            required>--}}
{{--                            <div class="form-control-feedback">--}}
{{--                                <i class="icon-mobile text-muted"></i>--}}
{{--                            </div>--}}
{{--                            @if ($errors->has('fin'))--}}
{{--                                <label id="phone-error" class="validation-error-label"--}}
{{--                                    for="fin">{{ $errors->first('fin') }}</label>--}}
{{--                            @endif--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="form-group mt30">--}}
{{--                    <div class="col-sm-12 text-center">--}}
{{--                        <button type="submit" class="btn btn-default">{{ __('front.save') }}</button>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                {{ Form::close() }}--}}
{{--		@endif--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div></div></div></div>--}}
{{--    </div>--}}
{{--    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-form-validator/2.3.26/jquery.form-validator.min.js"--}}
{{--            defer></script>--}}
{{--    @if(in_array(app()->getLocale(), ['az', 'ru']))--}}
{{--        <script src="{{ asset('langs/form/' . app()->getLocale() . '.js') }}?v=1.0.0"></script>--}}
{{--    @endif--}}
{{--@endif--}}
{{--@endsection--}}
