@extends('front.layout')

@section('content')

    <div style="min-height: 600px">
        <!-- content start -->
        <div class="container">
            <div class="row">
                <div class="col-md-6 col-md-offset-3">
                    <div class="bg-white pinside40">
                        <h2 class="signin-title-primary mg-b-50">{{ __('passwords.reset_title') }}</h2>

                        @if (session('status'))
                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>
                        @endif
                        {{ Form::open(['method' => 'post', 'route' => 'auth.password.email']) }}
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

                        <button class="btn btn-primary btn-block btn-signin">{{ __('passwords.reset_button') }} </button>

                        {{ Form::close() }}
                    </div><!-- signin-box -->
                </div><!-- signin-box -->
            </div><!-- signin-box -->

        </div><!-- signin-wrapper -->

@endsection
