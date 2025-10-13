@extends('front.layout')

@section('content')
    {{ Form::open(['method' => 'post', 'route' => 'auth.login']) }}

    <div style="min-height: 600px">
        <!-- content start -->
        <div class="custom-container section-space60">
            <div style="display: flex; justify-content: center" class="row">
                <div  class="contact-form  login-form-esi">
                    <div class="text-center ">
                        <img style="width:150px " src="{{ asset('front/new/logo_login.png') }}"  alt="fly"/>
                        <h1 style="font-weight: 600; font-size: 36px;color: #15549A; margin-top: 24px">{{ __('auth.login_to_your_account') }}</h1>
                    </div>

                    <div>
                        <div class="div-input-login rounded-esi3 form-group has-feedback has-feedback-left {{ ($errors->has('login') || $errors->has('email') || $errors->has('customer_id')) ? 'has-error' : '' }}">
                            <label for="login" >{{ __('auth.email') }}</label>
                            <input  placeholder="{{ __('auth.email') }}" id="login" type="text"
                                    name="login"
                                    value="{{ old('login') ?: (old('email') ?: old('customer_id')) }}"
                                    required
                                    autofocus>
                        </div>
                        <div class="form-control-feedback">
                            <i class="icon-user text-muted"></i>
                        </div>
                        @if ($errors->has('login') || $errors->has('email') || $errors->has('customer_id'))
                            <label style="font-weight: 500; font-size: 16px; color: #6f6b6b; " id="login-error" class="validation-error-label"
                                   for="login">{{ $errors->first('login') ?: ($errors->first('email') ?: $errors->first('customer_id')) }}</label>
                        @endif
                    </div>

                    <div>
                        <div style="position: relative"  class=" div-input-login2 rounded-esi3 form-group has-feedback has-feedback-left {{ $errors->has('password') ? 'has-error' : '' }}">
                            <label for="login">{{ __('auth.password') }}</label>
                            <input placeholder="{{ __('auth.password') }}" id="password" type="password"
                                   name="password" required>
                            <span class="toggle-password" style="position: absolute; right: 24px; bottom: 16px;  cursor: pointer;">
                                <i class="icon-eye-blocked text-muted show-password-icon">
                                    <svg class="show-password-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                        <line x1="1" y1="1" x2="23" y2="23"></line>
                                    </svg>
                                </i>
                                <i class="icon-eye text-muted hide-password-icon" style="display: none;">
                                   <svg class="hide-password-icon"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                   </svg>
                                </i>
                            </span>
                        </div>
                        <div class="form-control-feedback">
                            <i class="icon-lock2 text-muted"></i>
                        </div>
                        @if ($errors->has('password'))
                            <label  style="font-weight: 500; font-size: 16px; color: #6f6b6b; "  id="password-error" class="validation-error-label"
                                    for="email">{{ $errors->first('password') }}</label>
                        @endif
                    </div>


                    <div class="form-group login-options">
                        <div class="row">
                            {{--                            <div class="col-sm-6">--}}
                            {{--                                <label class="checkbox-inline">--}}
                            {{--                                    <input name="remember" type="checkbox" class="styled" checked="checked">--}}
                            {{--                                    {{ __('auth.remember') }}--}}
                            {{--                                </label>--}}
                            {{--                            </div>--}}

                            <div class="col-sm-6 ">
                                <a style="color: #0000FF; font-size: 16px; font-weight: 500" href="{{ route('auth.password.email') }}">{{ __('auth.forgot_password') }}</a>
                            </div>
                        </div>
                    </div>

                    <button style="text-transform: capitalize; display: block; margin-bottom: 24px"  type="submit" class="btn btn-primary btn-block rounded-esi1">{{ __('front.menu.sign_in') }}</button>
                    <a style="border: 1px solid #15549a; color: #15549A; background: none; " href="{{ route('auth.register') }}"
                       class="btn btn-default btn-block content-group rounded-esi1">{{ __('front.menu.sign_up') }}</a>

                </div>
            </div>
        </div>
    </div>
    {{ Form::close() }}



    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.querySelector('.toggle-password');
            const passwordInput = document.getElementById('password');
            const showIcon = document.querySelector('.show-password-icon');
            const hideIcon = document.querySelector('.hide-password-icon');

            togglePassword.addEventListener('click', function() {
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    showIcon.style.display = 'none';
                    hideIcon.style.display = 'inline';
                } else {
                    passwordInput.type = 'password';
                    showIcon.style.display = 'inline';
                    hideIcon.style.display = 'none';
                }
            });
        });
    </script>
@endsection
