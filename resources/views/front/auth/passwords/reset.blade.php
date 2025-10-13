@extends('front.layout')

@section('content')
    <div style="min-height: 600px">
        <!-- content start -->
        <div class="container">
            <div class="row">
                <div class="col-md-6 col-md-offset-3">
                    <div class="bg-white pinside40">
                        <h2 class="signin-title-primary mg-b-50"> {{ trans('passwords.reset_title') }}</h2>
                        {{ Form::open(['method' => 'post', 'route' => 'auth.password.reset']) }}

                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="form-group {{ ($errors->has('login') || $errors->has('email') || $errors->has('customer_id')) ? 'has-error' : '' }}">
                            <input placeholder="{{ __('passwords.email') }}" id="login" type="text" class="form-control"
                                   name="email"
                                   value="{{ old('login') ?: (old('email') ?: old('customer_id')) }}"
                                   required
                                   autofocus>
                            @if ($errors->has('login') || $errors->has('email') || $errors->has('customer_id'))
                                <label id="login-error" class="validation-error-label"
                                       for="login">{{ $errors->first('login') ?: ($errors->first('email') ?: $errors->first('customer_id')) }}</label>
                            @endif
                        </div><!-- form-group -->
                        <div class="form-group {{ ($errors->has('password')) ? 'has-error' : '' }}">
                            <input placeholder="{{ __('auth.password') }}" id="password" type="password"
                                   class="form-control"
                                   name="password" required>
                            @if ($errors->has('password'))
                                <label id="login-error" class="validation-error-label"
                                       for="login">{{ $errors->first('password') }}</label>
                            @endif

                        </div><!-- form-group -->

                        <div class="form-group mg-b-50 {{ ($errors->has('password_confirmation')) ? 'has-error' : '' }}">
                            <input placeholder="{{ __('passwords.confirm_password') }}" id="password_confirmation"
                                   type="password" class="form-control"
                                   name="password_confirmation" required>
                            @if ($errors->has('password_confirmation'))
                                <label id="password_confirmation-error" class="validation-error-label"
                                       for="password_confirmation">{{ $errors->first('password_confirmation') }}</label>
                            @endif

                        </div><!-- form-group -->

                        <button type="submit"
                                class="btn btn-primary btn-block btn-signin">{{ __('passwords.submit') }} </button>

                        {{ Form::close() }}
                    </div><!-- signin-box -->
                </div><!-- signin-box -->
            </div><!-- signin-box -->

        </div><!-- signin-wrapper -->
@endsection