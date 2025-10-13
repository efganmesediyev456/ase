<!DOCTYPE html>

<html>

<head>
    <meta charset="UTF-8">
    <meta name="description" content="Courier Delivery # {{ $item->id }}">
    <meta name="author" content="ASE">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courier Delivery # {{ $item->id }}</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css">
    <style>

        .container {
            max-width: 850px !important;
            min-width: 500px !important;
        }
        html, body {
            max-width: 98%;
            max-height: 98%;
        }
        main {
            width: 850px;
            height: 550px;
            font-size: 1em;
            line-height: 1.6em;
            margin: 15px 0 0 15px;

        }

        #table-right-footer {
            padding: 10px 5px;
        }

        @page {
            size: auto;
            margin: 0mm;
        }

        @media print {

            .print {
                display: none !important;
            }


            main {
                /*transform: rotate(-90deg);
                left: -285px;
                bottom: 220px;*/
            }
        }

        .print {
            display: block;
            float: right;
            border: 1px solid #000;
            margin-right: 21px;
            padding: 15px;
            border-radius: 6px;
            font-weight: 600;
            background: #f1f1f1;
            cursor: pointer;
            position: absolute;
            right: 0px;
            top: 24px;
        }

        .rotated {
            margin: 1px auto;
            /*transform: rotate(90deg);*/
        }

        .table-left {
            border: 1px solid black;
            width: 41%;
        }

        .line {
            height: 2px;
            background: #000000;
            width: 100%;
            display: block;
            margin-left: 0;
            border: 1px solid #000000;
        }


        #table-left-header img {
            margin-top: 2px;
            margin-bottom: 2px;
            width: 100%;
            height: auto;
        }

        #table-left-recipient {
            padding-top: 10px;
        }

        #table-left-recipient img {
            width: 100%;
            height: auto;
        }

        .table-right {
            border: 1px solid black;
            width: 58%;
        }

        #table-right-header {
            padding-top: 10px;
        }

        #table-right-barcode img {
            width: 100%;
            height: 130px;
            padding-bottom: 10px;
        }

        #table-right-barcode2 img {
            width: 360px;
            height: 100px;
            padding-bottom: 10px;
        }

        .col-md-6 {
            width: 49.5%;
        }
        .col-md-5 {
            width: 41.5%;
        }
        .col-md-7 {
            width: 58%;
        }

    </style>

</head>

<body>

<main>
    <div class="container rotated" style="width: 100%;">
        <div class="row">
            <div class="col-md-6 table-left">
                <div id="table-left-recipient">
                    <div class="row">
                        <div class="col-md-12">
                            <p><b>RECIPIENT:</b> <i>{{$item->name ? $item->name : ($item->user ? $item->user->full_name : '')}}</i> <br></p>
                            <p><b>Packages:</b> <i>{{$item->packages_with_cells_str }} </i> <br></p>
                            <p class="ml-6">
                                <b>Address</b> : <i>{{ $item->address ? $item->address: ($item->user ? $item->user->address : '') }}</i><br>
                                <b>Phone</b> : <i>{{ $item->phone ? $item->phone : ($item->user ? $item->user->phone : '') }}</i><br>
                            @if($item->company_name || $item->direction || $item->invoice_type)
				<br>
                                <b>Company Name:</b> <i>{{$item->company_name }} </i> <br>
                                <b>Direction:</b> <i>{{$item->direction_with_label }} </i> <br>
                                <b>Invoice:</b> <i>{{$item->invoice_type_with_label }} </i> <br>
                                <br>
                            @endif

                                <b>Time</b> : <i>{{ $item->courier_assigned_at }}</i><br>
				<b>Status</b> : 
				    @if($item->status==7)
				       <i style="color:red">{{ $item->statusWithLabel }} ({{ config('ase.attributes.cd.notDeliveredStatusCd')[$item->not_delivered_status] }})</i>
				    @else
				       <i @if($item->status==4) style="color:green" @endif>{{ $item->statusWithLabel }}</i>
				    @endif
				<br>
				@if($item->user_comment)
                                <b>User Comment</b> : <i>{{ $item->user_comment }}</i><br>
				@endif
				@if($item->courier_comment)
                                <b>Courier Comment</b> : <i>{{ $item->courier_comment }}</i><br>
				@endif
				<b>Price</b> : <i><?php echo $item->delivery_price_with_color; ?></i><br>
                                <b>Paid</b> : <i @if($item->paid) style="color:green" @else style="color:red" @endif>{{ config('ase.attributes.package.paid')[$item->paid] }}</i><br>
				<b>Money Received</b> : <i>{{ config('ase.attributes.yes_no')[$item->recieved] }}</i><br>
                            </p>
                        </div>
                    </div>
                    <div class="row" style="padding-bottom: 20px">
                        <div class="col-md-4">
			@if($item->status<3 || $item->status==7)
                        {!! Form::open(['id' => 'paid_form', 'method' => 'get' ,'id' => $item->id]) !!}
			    <input id="done" type="hidden" name="sent" value="yes">
                            <button type="submit" id="delivery_form_button" class="btn btn-info btn-icon" style="margin-top: 10px;"><i
                                        class="icon-search4"></i>Götürüldü</button>
                        {!! Form::close() !!}
			<br><br>
			@endif
			@if($item->status<4 || $item->status==7)
                        {!! Form::open(['id' => 'paid_form', 'method' => 'get' ,'id' => $item->id]) !!}
			    <input id="done" type="hidden" name="delivered" value="yes">
                            <button type="submit" id="delivery_form_button" class="btn btn-success btn-icon" style="margin-top: 10px;"><i
                                        class="icon-search4"></i>Təslim edildi</button>
                        {!! Form::close() !!}
			@endif
                        </div>
                        <div class="col-md-5">
			@foreach(config('ase.attributes.cd.notDeliveredStatusCd') as $nd_status=>$nd_value)
			  @if($nd_status)
                            {!! Form::open(['id' => 'paid_form', 'method' => 'get' ,'id' => $item->id,'nd_status'=>$nd_status]) !!}
			    <input id="done" type="hidden" name="not_delivered" value="{{$nd_status}}">
                            <button type="submit" id="delivery_form_button" class="btn btn-danger btn-icon" style="margin-top: 10px;"><i
                                        class="icon-search4"></i>{{$nd_value}}</button>
                            {!! Form::close() !!}
			  @endif
			@endforeach
                        </div>
                        <div class="col-md-3">
                        {!! Form::open(['id' => 'paid_form', 'method' => 'get' , 'route' => 'cd.index', 'id' => $item->id]) !!}
                            <button type="submit" id="delivery_form_button" class="btn btn-primary btn-icon" style="margin-top: 10px;"><i
                                        class="icon-search4"></i>Bağla</button>
                        {!! Form::close() !!}
                        </div>
                    </div>
                    @if($item->photo)
                    <div class="row">
                        <div class="col-md-12">
                            <img src='{{$item->photo_url}}'>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</main>
</body>
</html>
