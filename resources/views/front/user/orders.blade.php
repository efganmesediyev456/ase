@extends('front.layout')

@section('content')
    @include('front.sections.page-header')
    <div class="my-packages">
        <!-- content start -->
        <div class="custom-container section-space80">
            <div class="row">
                <div class="col-md-12">
                    <div style="padding: 24px 30px;" class="wrapper-content bg-white box-shadow-esi rounded-esi">

                        <div class="row mb40">
                            <div class="col-lg-9 col-sm-12">
                                <h1 style="font-weight: 500; font-size: 32px;color: #000;line-height: 1.5" class="mb20">{{ __('front.order_page_title') }}</h1>
                                <p style="font-weight: 500; font-size: 18px;color: #aaa;line-height: 2">{!! __('front.order_page_description') !!}</p>
                            </div>
                            <div class="col-lg-3 col-sm-12 text-right">
                                <a style="display: inline-block; padding: 20px " href="{{ route('my-orders.create') }}"
                                   class="btn btn-default button-blue-esi rounded-esi1">{{ __('front.order') }}</a>
                            </div>
                        </div>

                        @if (session('deleted'))
                            {{--                            <div class="alert alert-danger"--}}
                            {{--                                 role="alert">{{ __('front.order_was_deleted') }}1</div>--}}
                            <div style="border-radius: 12px; color: #fff;padding: 16px; background: #FF0000; text-align: center; width: 100%; font-size: 16px; font-weight: 600"
                                 role="alert">{{ __('front.order_was_deleted') }}</div>
                        @endif

                        @if (session('success'))
                            {{--                            <div class="alert alert-success"--}}
                            {{--                                 role="alert">{{ __('front.order_was_created') }}2</div>--}}
                            <div style="border-radius: 12px; border: 2px solid #11B42F;color: #11B42F;padding: 16px; background: #fff; text-align: center; width: 100%; font-size: 16px; font-weight: 600"
                                 role="alert">{{ __('front.order_was_created') }}</div>

                        @endif
                        @forelse($orders as $order)
                            @if($order->country)
                                <div style="margin-top: 30px" class="cards-flex-boxs-box rounded-esi">

                                    <div class="cards-flex-box">
                                        <span class="card-esi"> {{__('front.user.country')}}</span>
                                        <img
                                                src="{{ $order->country ? $order->country->flag : null }}"
                                                alt="{{ $order->country ? $order->country->name : null }}">
                                    </div>
                                    <div class="cards-flex-box">
                                        <span class="card-esi">{{__('front.user.date')}}</span>
                                        <span class="date card-esi2">{{ $order->created_at->format('d.m.y') }}</span>
                                    </div>
                                    {{--                                        <div class="shipping_price">{{ $order->country ? $order->country->translateOrDefault($_lang)->name : "-" }}</div>--}}
                                    <div class="cards-flex-box">
                                        <span class="card-esi">{{ __('front.number_links') }}</span>
                                        <h3 class="rate card-esi2">{{ $order->links_count }}</h3>
                                    </div>
                                    <div class="cards-flex-box">
                                        <span class="card-esi">{{ __('front.service_fee') }}</span>
                                        <h3 class="repayment card-esi2">{{ $order->service_fee or '-' }}</h3>
                                    </div>
                                    <div class="cards-flex-box">
                                        <span class="card-esi">{{ __('front.total_price') }}</span>
                                        <h3 class="compare-rate card-esi2">{{ $order->total_price or '-' }}</h3>
                                    </div>
                                    <div class="cards-flex-box">
                                        <span class="card-esi">Status</span>
                                        <span style="color: #11B42F; border-bottom: 1px solid #11B42F; padding: 1px; " class=" card-esi">{{ $order->status_info['text'] }}</span>
                                    </div>
                                    <div  style="display: flex; flex-direction: row; justify-content: center; align-items: center; gap: 20px">
                                        @if(! $order->status)
                                            {!! Form::open(['id' => 'order_' . $order->id, 'method' => 'delete', 'route' => ['my-orders.delete', $order->id]]) !!}
                                            {!! Form::close() !!}
                                            <a style="background: #FF0000; color: #fff; border: none;padding:16px 24px;font-size:14px;border-radius: 12px; width: 110px; display: flex; justify-content: center; align-items: center; gap: 20px" onclick="document.getElementById('order_<?= $order->id; ?>').submit();"
                                               class="">{{ __('front.delete') }}
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M6 19C6 20.1 6.9 21 8 21H16C17.1 21 18 20.1 18 19V7H6V19ZM19 4H15.5L14.5 3H9.5L8.5 4H5V6H19V4Z" fill="#fff">
                                                        <animate attributeName="opacity" values="1;0.7;1" dur="2s" repeatCount="indefinite"/>
                                                    </path>
                                                </svg></a>
                                        @endif
                                        <a href="{{ route('my-orders.show', $order->id) }}"
                                           style="background: #15549A; color: #fff; border: none;padding:16px 24px;font-size:18px;border-radius: 12px; width: 200px; text-align: center">{{ __('front.detailed') }}</a>
                                    </div>

                                </div>
                            @endif
                        @empty
                            <div style="margin-block: 24px" class="alert-esi"
                                 role="alert">{{ __('front.no_any_package') }}</div>
                        @endforelse
                        <div class="mt-20 text-center">
                            {!! $orders->render() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection