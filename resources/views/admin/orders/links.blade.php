@extends(config('saysay.crud.layout'))

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="page-header">
                <div class="page-header-content">
                    <div class="page-title">
                        <h4><i class="icon-arrow-left52 position-left"></i> <span class="text-semibold">Links for request</span>
                            #{{ $order->id }}</h4>
                        <a class="heading-elements-toggle"><i class="icon-more"></i></a></div>
                </div>
                <div class="breadcrumb-line breadcrumb-line-component"><a class="breadcrumb-elements-toggle"><i
                                class="icon-menu-open"></i></a>
                    <ul class="breadcrumb">
                        <li><a href="{{ route('dashboard') }}"><i class="icon-home2 position-left"></i> Dashboard</a>
                        </li>
                        <li><a href="{{ route('orders.index') }}">Requests</a></li>
                        <li class="active">Request #{{ $order->id }}</li>
                    </ul>
                </div>
            </div>
            <div class="content">
                <div class="panel panel-flat">
                    <div class="panel-heading">
                        <h5 class="panel-title">Links</h5>
                    </div>

                    <div class="panel-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <ul>
                                    <li><b>Name</b> : <i style="color: #2162ff">{{ $order->user->full_name }}</i> /
                                        <b>{{ $order->country->warehouse->address->contact_name }}</b></li>
                                    <li><b>Member ID</b> : <i
                                                style="color: #2162ff">{{ $order->user->customer_id }} </i></li>
                                    <li><b>Country</b> : <i style="color: #2162ff">{{ $order->country->name }} </i></li>
                                    <li><b>Address</b> : <i
                                                style="color: #2162ff">{{ $order->user->customer_id }} {{ $order->country->warehouse->address->address_line_1 }} </i>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        @if ($order->note) User note : {{ $order->note }} @endif
                    </div>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>URL</th>
                                <th>Status</th>
                                <th>Note</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($links as $row => $item)
                                <tr>
                                    <td>{{ $row + 1 }}</td>
                                    <td><a target="_blank"
                                           href="{{ $item->url }}">{{ str_limit($item->url, (strlen($item->url) > 60 ? 60 : (strlen($item->url) - 3))) }}</a>
                                    </td>
                                    <td>@include('crud::components.columns.select-editable', ['entry' => $item->status])</td>
                                    <td>{{ $item->note }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
