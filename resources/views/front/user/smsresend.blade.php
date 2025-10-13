@extends('front.layout')

@section('content')

    <div class="bg-light pinside60">
        <div class="container" style="margin-top: 40px; margin-bottom: 40px;">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="wrapper-content bg-white pinside20 text-center" style="border: 1px solid #e5e5e5">
                            <div class="section-title mb60 text-center">
                                <h1>{{ __('smsverification.title') }}</h1>
                                @if($verified)
                                    <div class="alert alert-success">
                                        {{ __('smsverification.success') }}
                                    </div>
                                @else
                                    <p>{{ __('smsverification.correct_number') }}</p>
                                @endif
                                @if(!$verified)
                                    <div class="row">
                                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                            {{ Form::open(['method' => 'post', 'route' => 'sendVerificationSms']) }}
                                            <fieldset>
                                                <!-- Text input-->
                                                <div class="row">
                                                    <!-- Text input-->
                                                    <div class="col-lg-8">
                                                        <div class="form-group{{ $errors->has('phone') ? ' has-error' : '' }}">
                                                            <label class="control-label">{{ __('smsverification.number') }}</label>

                                                            <input data-inputmask="'mask': '999-999-99-99'"
                                                                   placeholder="050-500-00-00" id="phone" type="text"
                                                                   class="form-control" name="phone"
                                                                   value="{{ old('phone', $phone) }}"
                                                                   required>


                                                            @if ($errors->has('phone'))
                                                                <label id="email-error" class="validation-error-label"
                                                                       for="phone">{{ $errors->first('phone') }}
                                                                </label>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <!-- Button -->
                                                    <div class="col-lg-4">
                                                        <label class="control-label">&nbsp;</label>
                                                        <button type="submit" id="Submit" name="Submit"
                                                                class="btn btn-primary btn-block">{{ __('smsverification.submit') }}</button>
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
                </div>

            </div>
        </div>
    </div>

@endsection