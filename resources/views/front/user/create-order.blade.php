@extends('front.layout')

@section('content')
    @include('front.sections.page-header')
    <div class="my-packages">
        <!-- content start -->
        <div  class="section-space40">
            <div class="">
                <div >
                    <div class="wrapper-content ">
                        {!! Form::open() !!}
                        <div style="background: #4274ac" class=" pinside20">
                            <div style="margin-block: 30px" class="custom-container">
                                <h1 style="font-weight: 500; font-size: 40px" class="text-white text-center">{{ __('front.create_order_title') }}</h1>
                                <div >
                                    <div class="row">
                                        <div class="col-lg-4 col-sm-12 esmer">
                                            @include('front.form.group', ['type' => 'select', 'key' => 'country', 'label' => ucfirst(strtolower(trans('front.calculator.choose_country'))), 'selects' => $countries, 'options' => ['class' => 'form-control']])
                                        </div>
                                        <div  class="col-lg-8 col-sm-12 esmer">
                                            @include('front.form.group', ['key' => 'note', 'label' => trans('front.special_note'), 'options' => ['class' => 'form-control']])
                                        </div>
                                        <div class="col-lg-12">
                                            <div style="color: #fff;font-size: 16px; font-weight: 500 " class="mt10">
                                                {{ __('front.create_order_alert') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="custom-container section-space60 ">
                            <div class="pinside60 bg-white box-shadow-esi rounded-esi ">
                                <div class="row">
                                    <div class="text-center">
                                        <h1 style="font-size: 40px ; font-weight: 500; color: black; margin-bottom: 24px">{{ __('front.create_order_enter_urls') }}</h1>
                                        <p style="font-weight: 400; font-size: 14px; color: #7f7b7b" class="mt10">{{ __('front.create_order_sub_urls_text') }}</p>
                                    </div>

                                    <div class="row mt60">
                                        @if(session('error'))
                                            <div class="col-lg-6 col-lg-offset-3">
                                                <div class="alert alert-danger">{{ __('front.enter_at_least_one_url') }}</div>
                                            </div>
                                        @endif
                                        <div class="col-lg-6">
                                            @include('front.form.group', ['key' => 'url[0][link]', 'label' => trans('front.url') . ' #1', 'options' => ['class' => 'form-control', 'placeholder' => trans('front.url_example')]])
                                        </div>

                                        <div class="col-lg-6">
                                            @include('front.form.group', ['key' => 'url[0][note]', 'label' => trans('front.note'), 'options' => ['class' => 'form-control', 'placeholder' => trans('front.url_note_example')]])
                                        </div>
                                    </div>
                                    @for($i = 2; $i <= 15; $i++)
                                        <div class="row mt10">
                                            <div class="col-lg-6">
                                                @include('front.form.group', ['key' => 'url[' . ($i - 1). '][link]', 'label' => trans('front.url') . ' #' . $i, 'options' => ['class' => 'form-control', 'placeholder' => trans('front.url_example')]])
                                            </div>
                                            <div class="col-lg-6">
                                                @include('front.form.group', ['key' => 'url[' . ($i - 1) . '][note]', 'label' => trans('front.note'), 'options' => ['class' => 'form-control', 'placeholder' => trans('front.url_note_example')]])
                                            </div>
                                        </div>
                                    @endfor

                                    <div class="row mt40 text-center">
                                        <button style="border: none" type="submit"
                                                class="button-blue-esi rounded-esi1" >{{ __('front.create_order_button') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection