@extends('front.layout')

@section('content')
    @include('front.sections.page-header')
    <div class="my-packages">
        <!-- content start -->
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="wrapper-content bg-white">
                                    @if ( request()->has('success'))
                                        <div class="alert alert-success"
                                             role="alert">{{ __('front.was_paid') }}</div>
                                    @endif
                                    @if ( request()->has('error'))
                                        <div class="alert alert-danger"
                                             role="alert">{{ request()->get('error') }}</div>
                                    @endif
                        <div class="contact-form mb60">

                                        <div class="col-lg-8 col-sm-12">
                                            <h1 class="mb20">{{ __('front.courier_deliveries_page_title') }}</h1>
                                        </div>
                            <div class="pinside40">
                                    <div class="row">
                                        <div class="col-md-5 col-sm-12 col-xs-12">
					    <h4>{{trans('front.courier_name')}}: <b>{{$cd->name}}</b></h4>
                                        </div>
                                        <div class="col-md-5 col-sm-12 col-xs-12">
					    <h4>{{trans('front.courier_phone')}}: <b>{{$cd->phone}}</b></h4>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-10 col-sm-12 col-xs-12">
					    <h4>{{trans('front.courier_address')}}: <b>{{$cd->address}}</b></h4>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-5 col-sm-12 col-xs-12">
                                            <h4>{{trans('front.courier_status')}}: <b>{{trans('front.courier_delivery_status_'.$cd->status)}}</b></h4>
                                        </div>
                                        @if($cd->paid)
                                            <div class="col-md-2 col-sm-6 col-xs-12">
                                                 <div class="text-center compare-action">
                                                     <button class="btn btn-success btn-sm"
                                                         disabled>{{ trans('front.paid') }}</button>
                                                  </div>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 col-sm-12 col-xs-12">
					    <h4>{{trans('front.courier_delivery_price')}}: <b>{{$cd->delivery_price}}</b></h4>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-10 col-sm-12 col-xs-12">
					    <h4>{{trans('front.packages')}}: <b>{{$cd->packages_str}}</b></h4>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-10 col-sm-12 col-xs-12">
					    <h4>{{trans('front.note')}}: <b>{{$cd->user_comment}}</b></h4>
                                        </div>
                                    </div>
                                    @if(!$cd->paid)
                                    <div class="row">
                                        <div class="col-md-10 col-sm-12 col-xs-12" style="width:100%">

{{--                                         @if(Auth::user()->id == '1901' || Auth::user()->id == '30869')--}}
                                                    <form action="{{ route('kapital.new.payment','courier') }}" method="POST">
                                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                        <input type="hidden" name="item_id" value="{{ $cd->id }}">
                                                        <input type="hidden" name="amount" value="{{ $cd->delivery_price }}">
                                                        <button class="btn btn-primary btn-sm" type='submit'>{{ trans('front.pay') }}</button>
                                                    </form>
{{--                                            @endif--}}

{{--				     {!! (new App\Models\Payments\PortManat())->generateForm2($cd) !!}--}}
                                        </div>
                                    </div>
                                    @endif
                                <div class="text-center" style="width: 85%;margin-top: 5px">
                                    <h4 style="margin: 0px;color: red;font-weight: bold">Nəzərinizə: Kuryer çatdırılması 1-3 iş günü ərzində olur.</h4>
                                </div>
			    </div>
			</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
