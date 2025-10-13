@extends('front.layout')

@section('content')

    <div class="bg-light pinside60">
        <div class="container" style="margin-top: 40px; margin-bottom: 40px;">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12 col-sm-12 col-lg-12">
                        <div class="wrapper-content bg-white pinside20 text-center" style="border: 1px solid #e5e5e5">
                            <h1>{{ __('smsverification.title') }}</h1>
                            @if($verified)
                                <div class="alert alert-success">
                                    {{ __('smsverification.success') }}
                                </div>

                                <a href="{{ route('addresses') }}" class="btn btn-primary">{{ __('smsverification.go_to_panel') }}</a>
                            @else
                                <p>{{ __('smsverification.correct_code') }}</p>

                                @if(Session::has('error'))
                                    <div class="alert alert-danger"> {{ Session::get('error') }}</div>
                                @endif
                            @endif

                            @if(!$verified)
                                <div class="row">
                                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                        {{ Form::open(['method' => 'post', 'route' => 'checkCode']) }}
                                        <fieldset>
                                            <!-- Text input-->
                                            <div class="row">
                                                <!-- Text input-->
                                                <div class="col-lg-3">
                                                    <div class="form-group{{ $errors->has('sms_verification_code') ? ' has-error' : '' }}">
                                                        <label class="control-label">{{ __('smsverification.code') }}</label>

                                                        <input type="text" class="form-control" name="sms_verification_code">

                                                        @if ($errors->has('sms_verification_code'))
                                                            <label id="email-error" class="validation-error-label"
                                                                   for="sms_verification_code">{{ $errors->first('sms_verification_code') }}
                                                            </label>
                                                        @endif

                                                    </div>
                                                </div>

                                                <div class="col-xl-4 col-lg-3">
                                                    <label class="control-label">&nbsp;</label>
                                                    <button type="submit" id="Submit" name="Submit" class="btn btn-success btn-block">{{ __('smsverification.submit') }}</button>
                                                </div>


                                                <div class="col-xl-4 col-lg-3">
                                                    <label class="control-label">&nbsp;</label>
                                                    <a href="{{ route('verifyAfterEmail') }}"
                                                       onclick="event.preventDefault(); document.getElementById('resend-form').submit();"
                                                       class="btn btn-primary btn-block btn-signin col-md-3">{{ __('smsverification.resend') }}</a>
                                                </div>
                                                <div class="col-xl-4 col-lg-3">
                                                    <label class="control-label">&nbsp;</label>
                                                    <a href="{{ route('showResendVerificationSmsForm') }}"
                                                       class="btn btn-warning btn-block btn-signin col-md-5">{{ __('smsverification.change_number') }}</a>
                                                </div>
                                            </div>
                                        </fieldset>

                                        {{ Form::close() }}
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>


                <form id="resend-form" action="{{ route('verifyAfterEmail') }}" method="POST" style="display: none;">
                    {{ csrf_field() }}
                    <input type="hidden" class="form-control" name="phone" value="{{ Auth::user()->phone }}">
                </form>
            </div>
        </div>
    </div>

@endsection
