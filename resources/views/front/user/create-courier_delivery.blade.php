@extends('front.layout')

@section('content')
    @include('front.sections.page-header')
    <div class="my-packages">
        <!-- content start -->
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="wrapper-content bg-white">
                        {!! Form::open(['class' => 'contact-us', 'route' => 'cds.store', 'files' => true]) !!}
                        <div class="contact-form mb60">
                            <h1 class="text-center">{{ __('front.create_courtier_delivery_title') }}</h1>
                            <div class="pinside40">
                                <div class="row">
                                    <div class="col-md-5 col-sm-12 col-xs-12">
                                        @if($cd->user_id)
                                            <h4>{{trans('front.courier_name')}}: {{$cd->name}}</h4>
                                        @else
                                            @include('front.form.group', ['key' => 'name', 'label' => trans('front.courier_name'), 'options' => ['class' => 'form-control','value'=>$cd->name]])
                                        @endif
                                    </div>
                                    <div class="col-md-5 col-sm-12 col-xs-12">
                                        @if($cd->user_id)
                                            <h4>{{trans('front.courier_phone')}}: {{$cd->phone}}</h4>
                                        @else
                                            @include('front.form.group', ['key' => 'phone', 'label' => trans('front.courier_phone'), 'options' => ['class' => 'form-control','value'=>$cd->phone]])
                                        @endif
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-10 col-sm-12 col-xs-12">
                                        @if($cd->user_id)
                                            <h4>{{trans('front.courier_address')}}: {{$cd->address}}</h4>
                                        @else
                                            @include('front.form.group', ['type' => 'textarea', 'key' => 'address', 'label' => trans('front.courier_address'), 'options' => ['class' => 'form-control', 'rows' => 2,'value'=>$cd->address]])
                                        @endif
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 col-sm-12 col-xs-12">
                                        <h4>{{ trans('front.courier_delivery_price') }}
                                            : {{ number_format($cd->delivery_price, 2) }} AZN</h4>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-10 col-sm-12 col-xs-12">
                                        @if($cd->user_id)
                                            <h4>{{trans('front.courier_packages')}}: {{$cd->packges_txt}}</h4>
                                        @else
                                            @include('front.form.group', ['type' => 'multi-select', 'key' => 'packages', 'selects'=> $user->getPackagesPaidInbaku(), 'label' => trans('front.courier_packages'), 'options' => ['class' => 'form-control', 'value'=>$user->getPackagesPaidInbaku(),'size'=>7]])
                                        @endif
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-10 col-sm-12 col-xs-12">
                                        @if($cd->user_id)
                                            <h4>{{trans('front.note')}}: {{$cd->user_comment}}</h4>
                                        @else
                                            @include('front.form.group', ['type' => 'textarea', 'key' => 'user_comment', 'label' => trans('front.note'), 'options' => ['class' => 'form-control', 'rows' => 2]])
                                        @endif
                                    </div>
                                </div>
                                <div class="text-center" style="width: 85%">
                                    <h4 style="margin: 0px;color: red;font-weight: bold">Nəzərinizə: Kuryer çatdırılması 1-3 iş günü ərzində olur.</h4>
                                </div>
                                <div class="row">
                                    <div class="form-group" style="margin-top: 15px">
                                        <div class="col-sm-10 text-center">
                                            <button type="submit"
                                                    class="btn btn-default">{{ __('front.courier_delivery_payment') }}</button>
                                        </div>
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
