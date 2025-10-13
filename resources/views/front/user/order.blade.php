@extends('front.layout')

@section('content')
    @include('front.sections.page-header')
    <div class="my-packages">
        <!-- content start -->
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="wrapper-content bg-white">
                        <div class="pinside50">
                            <div class="row mb40">
                                <div class="col-lg-12 col-sm-12 text-center">
                                    <h1 class="mb10">{{ __('front.single_order_page_title', ['country' => ucfirst(strtolower($order->country->translateOrDefault($_lang)->name))]) }}</h1>
                                    <h3 class="mb20">#{{ $order->custom_id }} | {{ __('front.status') . ': ' . __($order->status_info['text']) }}</h3>
                                    <p>{!! $order->note or __('front.single_order_page_description') !!}</p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="fee-charges-table">
                                        @if (session('deleted'))
                                            <div class="alert alert-danger"
                                                 role="alert">{{ __('front.link_was_deleted') }}</div>
                                        @endif
                                        <ul class="list-group mt20">
                                            <li class="list-group-item active">
                                                <div class="row">
                                                    <div class="col-lg-1"><b>#</b></div>
                                                    <div class="col-lg-5"><b>{{ __('front.urls') }}</b></div>
                                                    <div class="col-lg-6">{{ __('front.notes') }}</div>
                                                </div>
                                            </li>
                                            @forelse($order->links as $key => $link)
                                                <li class="list-group-item">
                                                    <div class="row">
                                                        <div class="col-lg-1">{{ $key + 1 }}</div>
                                                        <div class="col-lg-5">
                                                            <a href="{{ $link->url }}"
                                                               target="_blank">{{ str_limit($link->url, 45) }}</a>
                                                        </div>
                                                        <div class="col-lg-5">{{ $link->note or trans('front.not') }}</div>

                                                        <div class="col-lg-1">

                                                            @if(! $order->status)
                                                                {!! Form::open(['id' => 'order_' . $order->id, 'method' => 'delete', 'route' => ['my-orders.link.delete', $link->id]]) !!}
                                                                {!! Form::close() !!}
                                                                <a onclick="document.getElementById('order_<?= $order->id; ?>').submit();"
                                                                   class="btn btn-danger btn-sm">{{ __('front.delete') }}</a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </li>
                                            @empty
                                                <li>
                                                    <div class="alert alert-warning">
                                                        {{ __('front.no_any_link') }}
                                                    </div>
                                                </li>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt30">
                                <div class="col-lg-offset-4 col-lg-8 col-sm-12">
                                    <table class="table table-bordered">
                                        <thead>
                                        <tr>
                                            <th>{{ __('front.order_price.description') }}</th>
                                            <th>{{ __('front.price') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>{{ __('front.order_price.price') }}</td>
                                                <td>{{ $order->price or '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td>{{ __('front.coupon_sale') }}</td>
                                                <td>{{ '-' . $order->coupon_sale }}</td>
                                            </tr>
                                            <tr>
                                                <td>{{ __('front.service_fee') }}</td>
                                                <td>{{ $order->service_fee or '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td>{{ __('front.total_price') }}</td>
                                                <td>{{ $order->total_price or '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="2">
                                                    <b>{{ __('front.order_price.explanation') }}</b>
                                                </td>
                                            </tr>

                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection