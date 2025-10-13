@extends('front.layout')

@section('content')
    @include('front.sections.page-header')
    <div class="my-packages">
        <!-- content start -->
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="wrapper-content bg-white">
                                    {!! Form::open(['method' => 'post', 'class' => 'contact-us', 'route' => ['cds.update',$cd->id], 'files' => true]) !!}
				    <?php $item=$cd; ?>
                        <div class="contact-form mb60">
                            <h1 class="text-center">{{ __('front.update_courier_delivery_title') }}</h1>
                            <div class="pinside40">
                                    <div class="row">
                                        <div class="col-md-5 col-sm-12 col-xs-12">
                                            @include('front.form.group', ['key' => 'name', 'label' => trans('front.courier_name'), 'options' => ['class' => 'form-control','value'=>$cd->name]])
                                        </div>
                                        <div class="col-md-5 col-sm-12 col-xs-12">
                                            @include('front.form.group', ['key' => 'phone', 'label' => trans('front.courier_phone'), 'options' => ['class' => 'form-control','value'=>$cd->phone]])
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-10 col-sm-12 col-xs-12">
                                            @include('front.form.group', ['type' => 'textarea', 'key' => 'address', 'label' => trans('front.courier_address'), 'options' => ['class' => 'form-control', 'rows' => 2,'value'=>$cd->address]])
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 col-sm-12 col-xs-12">
                                            @include('front.form.group', ['key' => 'delivery_price', 'label' => trans('front.courier_delivery_price'), 'options' => ['class' => 'form-control','readonly' => 'true','value'=>$cd->delivery_price]])
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-10 col-sm-12 col-xs-12">
                                            @include('front.form.group', ['type' => 'multi-select', 'key' => 'packages', 'selects'=> $user->getPackagesPaidInbaku($cd->id), 'label' => trans('front.courier_packages'), 'options' => ['class' => 'form-control','size'=>7 ]])
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-10 col-sm-12 col-xs-12">
                                            @include('front.form.group', ['type' => 'textarea', 'key' => 'user_comment', 'label' => trans('front.note'), 'options' => ['class' => 'form-control', 'rows' => 2,'value'=>$cd->user_comment]])
                                        </div>
                                    </div>
                                    <div class="form-group mt30">
                                        <div class="col-sm-10 text-center">
                                            <button type="submit" class="btn btn-default">{{ __('front.save') }}</button>
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
