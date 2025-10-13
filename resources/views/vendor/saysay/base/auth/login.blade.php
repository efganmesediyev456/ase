@extends(config('saysay.crud.layout'))

@section('content')
    {{ Form::open(['method' => 'post', 'route' => 'auth.login']) }}

    <div class="panel panel-body login-form">

        <div class="text-center">
            <div class="icon-object border-warning-400 text-warning-400"><i class="icon-people"></i></div>
            <h5 class="content-group-lg">Login to your account
                <small class="display-block">Enter your credentials</small>
            </h5>
        </div>

        <div class="form-group has-feedback has-feedback-left {{ $errors->has('email') ? 'has-error' : '' }}">
            <input placeholder="Email" id="email" type="email" class="form-control" name="email"
                   value="{{ old('email') }}"
                   required
                   autofocus>
            <div class="form-control-feedback">
                <i class="icon-user text-muted"></i>
            </div>
            @if ($errors->has('email'))
                <label id="email-error" class="validation-error-label" for="email">{{ $errors->first('email') }}</label>
            @endif

        </div>

        <div class="form-group has-feedback has-feedback-left {{ $errors->has('password') ? 'has-error' : '' }}">
            <input placeholder="Password" id="password" type="password" class="form-control" name="password" required>
            <div class="form-control-feedback">
                <i class="icon-lock2 text-muted"></i>
            </div>
            @if ($errors->has('password'))
                <label id="password-error" class="validation-error-label" for="email">{{ $errors->first('password') }}</label>
            @endif
        </div>


        <div class="form-group login-options">
            <div class="row">
                <div class="col-sm-6">
                    <label class="checkbox-inline">
                        <input name="remember" type="checkbox" class="styled" checked="checked">
                        Remember
                    </label>
                </div>

                <div class="col-sm-6 text-right">
                    <a href="{{ route('auth.password.email') }}">Forgot password?</a>
                </div>
            </div>
        </div>

        <div class="form-group">
            <button type="submit" class="btn bg-pink-400 btn-block">Login <i
                        class="icon-circle-right2 position-right"></i></button>
        </div>

        <span class="help-block text-center no-margin">By continuing, you're confirming that you've read our <a
                    href="#">Terms &amp; Conditions</a> and <a href="#">Cookie Policy</a></span>

    </div>
    {{ Form::close() }}
@endsection
