@extends('front.layout')

@section('content')
    @include('front.sections.page-header')

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="wrapper-content bg-white pinside20">
                    <div class="loan-eligibility-block">
                        <div class="row">
                            <div class="col-lg-6">
                                <h2 class="mb20">{{ __('front.calculator.title') }}</h2>
                                {{ Form::open(['class' => 'form-horizontal loan-eligibility-form']) }}
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label for="input"
                                               class="control-label">{{ __('front.calculator.choose_country') }}
                                            :</label>
                                        <select class="form-control" name="country">
                                            @foreach($countries as $country)
                                                <option @if (Request::get('country') == $country->id) selected
                                                        @endif value="{{ $country->id }}">{{ $country->translateOrDefault($_lang)->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-12 col-lg-6">
                                        <label for="input"
                                               class="control-label">{{ __('front.calculator.length_unit') }}:</label>
                                        <select class="form-control" name="length_unit">
                                            <option value="" disabled selected>{{ __('front.calculator.length_unit') }}</option>
                                            @foreach(config('ase.attributes.length') as $key => $unit)
                                                <option @if (Request::get('length_unit') == $key) selected @endif value="{{ $key }}">
                                                    {{ $unit }}
                                                </option>
                                            @endforeach
                                        </select>

                                    </div>
                                    <div class="col-sm-12 col-lg-6">
                                        <label for="input"
                                               class="control-label">{{ __('front.calculator.weight_unit') }}:</label>
                                        <select class="form-control" name="weight_unit">
                                            <option value="" disabled selected>{{ __('front.calculator.weight_unit') }}</option>
                                            @foreach(config('ase.attributes.weight') as $key => $unit)
                                                <option @if (Request::get('weight_unit') == $key) selected @endif value="{{ $key }}">
                                                    {{ $unit }}
                                                </option>
                                            @endforeach
                                        </select>

                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-12 col-lg-3">
                                        <label for="input" class="control-label">{{ __('front.calculator.weight') }}
                                            :</label>
                                        <input value="{{ Request::get('weight') }}" type="text"
                                               class="form-control input-sm" id="input"
                                               name="weight">
                                    </div>
                                    <div class="col-sm-12 col-lg-3">
                                        <label for="input" class="control-label">{{ __('front.calculator.width') }}
                                            :</label>
                                        <input value="{{ Request::get('width') }}" type="text"
                                               class="form-control input-sm" id="input"
                                               name="width">
                                    </div>
                                    <div class="col-sm-12 col-lg-3">
                                        <label for="input" class="control-label">{{ __('front.calculator.height') }}
                                            :</label>
                                        <input value="{{ Request::get('height') }}" type="text"
                                               class="form-control input-sm" id="input"
                                               name="height">
                                    </div>
                                    <div class="col-sm-12 col-lg-3">
                                        <label for="input" class="control-label">{{ __('front.calculator.length') }}
                                            :</label>
                                        <input value="{{ Request::get('length') }}" type="text"
                                               class="form-control input-sm" id="input"
                                               name="length">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-12 text-center">
                                        <button type="submit"
                                                class="btn btn-default">{{ __('front.calculator.button') }}</button>
                                        <button type="button"
                                                onclick="window.location.href='{{ route('calculator') }}'"
                                                class="btn btn-primary">
                                            {{ __('front.calculator.reset_all') }}
                                        </button>

                                    </div>
                                </div>
                                {{ Form::close() }}
                            </div>
                            <div class="col-lg-6 text-center">
                                <h2 class="mb40">{{ __('front.calculator.approximate_price') }}</h2>
                                <div class="loan-eligibility-info" style="font-size: 50px;">
                                    {{ $result or null }}
                                </div>
                                {{--@if ($price)
                                    <div class="row mt60">
                                        <div class="col-lg-11 col-lg-offset-1">
                                            <div class="alert alert-info">{{ __('front.calculator.note') }}</div>
                                        </div>
                                    </div>
                                @endif--}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
