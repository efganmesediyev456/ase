@extends(config('saysay.crud.layout'))

@section('content')
    {{ Form::open(['method' => 'post', 'route' => 'auth.password.email']) }}

    <div class="panel panel-body login-form">
        <div class="text-center">
            <div class="icon-object border-warning text-warning"><i class="icon-spinner11"></i></div>
            <h5 class="content-group">Password recovery
                <small class="display-block">We'll send you instructions in email</small>
            </h5>
        </div>

        <div class="form-group has-feedback">
            <input id="email" type="email" class="form-control" name="email"
                   value="{{ old('email') }}" placeholder="Your email" required>

            <div class="form-control-feedback">
                <i class="icon-mail5 text-muted"></i>
            </div>
            @if ($errors->has('email'))
                <label id="email-error" class="validation-error-label" for="email">{{ $errors->first('email') }}</label>
            @endif
        </div>

        <button type="submit" class="btn bg-pink-400 btn-block legitRipple">Send Password Reset Link <i
                    class="icon-arrow-right14 position-right"></i></button>
    </div>

    {{ Form::close() }}
@endsection
