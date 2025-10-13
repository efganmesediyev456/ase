@extends(config('saysay.crud.layout'))

@section('content')
    {{ Form::open(['method' => 'post', 'route' => 'auth.password.reset']) }}

    <div class="panel panel-body login-form">

        <div class="text-center">
            <div class="icon-object border-warning-400 text-warning-400"><i class="icon-unlocked"></i></div>
            <h5 class="content-group-lg">Password reset
                <small class="display-block">Enter your new password</small>
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

        <div class="form-group has-feedback has-feedback-left {{ $errors->has('password_confirmation') ? 'has-error' : '' }}">
            <input placeholder="Confirm Password" id="password_confirmation" type="password" class="form-control" name="password_confirmation" required>
            <div class="form-control-feedback">
                <i class="icon-lock2 text-muted"></i>
            </div>
            @if ($errors->has('password_confirmation'))
                <label id="password_confirmation-error" class="validation-error-label" for="email">{{ $errors->first('password_confirmation') }}</label>
            @endif
        </div>

        <div class="form-group">
            <button type="submit" class="btn bg-pink-400 btn-block"> Reset Password <i
                        class="icon-circle-right2 position-right"></i></button>
        </div>

    </div>
    {{ Form::close() }}
@endsection
