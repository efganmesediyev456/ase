@extends('front.layout')

@section('content')
    <div class="bg-light pinside60">
        <div class="container" style="margin-top: 40px; margin-bottom: 40px;">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                        <div class="wrapper-content bg-white pinside60 text-center" style="border: 1px solid #e5e5e5">
                            <h1>{{ __('emailverification::messages.resend.title') }}</h1>
                            @if($verified)
                                <div class="alert alert-success">
                                    {{ __('emailverification::messages.done') }}
                                </div>

                                @if(env('SMS_VERIFY'))
                                    <div class="alert alert-danger">
                                        {{ __('smsverification.sms_verification_info') }}
                                    </div>

                                    {{ Form::open(['method' => 'post', 'route' => 'verifyAfterEmail']) }}
                                    <input type="hidden" class="form-control" name="phone"
                                           value="{{ Auth::user()->phone }}">
                                    <div style="text-align: right">
                                        <button type="submit"
                                                class="btn btn-primary">{{ __('smsverification.send') }}</button>
                                    </div>

                                    {{ Form::close() }}
                                @else
                                    <a href="{{ route('addresses') }}" class="btn btn-primary">{{ __('smsverification.go_to_panel') }}</a>
                                @endif
                            @else
                                <div class="alert alert-info dark">
                                    {!! __('emailverification::messages.resend.warning', ['email' => $email]) !!}
                                </div>
                                <p>{{ __('emailverification::messages.resend.instructions') }}</p>
                            @endif

                        </div>
                    </div>
                </div>
                @if(!$verified)
                    <div class="row">
                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                            <div class="wrapper-content bg-white pinside50" style="border: 1px solid #e5e5e5">
                                {{ Form::open(['method' => 'post', 'route' => 'resendVerificationEmail']) }}
                                <div class="row">
                                    <!-- Text input-->
                                    <div class="col-lg-8">
                                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                            <label class="control-label">{{ __('emailverification::messages.resend.email') }}</label>

                                            <input type="text" class="form-control" name="email"
                                                   value="{{ old('email', $email) }}">

                                            @if ($errors->has('email'))
                                                <label id="email-error" class="validation-error-label"
                                                       for="email">{{ $errors->first('email') }} }}
                                                </label>
                                            @endif
                                        </div>
                                    </div>
                                    <!-- Button -->
                                    <div class="col-lg-4" style="padding-top: 25px">
                                        <button type="submit" id="Submit" name="Submit"
                                                class="btn btn-primary btn-block">{{ __('emailverification::messages.resend.submit') }}</button>
                                    </div>
                                </div>

                                {{ Form::close() }}
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

@endsection
