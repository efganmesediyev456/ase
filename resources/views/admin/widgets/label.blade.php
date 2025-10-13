<!DOCTYPE html>

<html>

<head>
    <meta charset="UTF-8">
    <meta name="description" content="Label # {{ $item->id }}">
    <meta name="author" content="ASE">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Label # {{ $item->id }}</title>

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
            <div class="col-md-5 table-left">
                <div id="table-left-header">
                    <div class="row" style="">
                        <div class="col-md-6">
                            <img src="{{ asset('admin/images/logo_cert.jpg') }}"
                                 alt="">
                        </div>
                        <div class="col-md-6">
                            <p style="padding-top: 20px;">INTERNATIONAL AIRWAY BILL </p>
                        </div>
                    </div>
                </div>
                <div class="line"></div>
                <div id="table-left-shipper">
                    <div class="row">
                        <div class="col-md-12">
                            <p><b>SHIPPER:</b> {{ $shipper->address->phone or '-' }}</p>
                            <p class="ml-2">{{ getOnlyDomain($item->website_name) }} <br>
                                @if ($shipper)
                                    @if($item->fake_address)
                                        {{ $item->fake_address }}<br>
                                    @else
                                        {{ $shipper->company_name or '-' }} <br>
                                        {{ $shipper->address->address_line_1 or '-' }} <br>
                                    @endif

                                    {{ $shipper->address->city or null }}
                                    , {{ $shipper->address->state or null }} {{ $shipper->address->zip_code or null }}
                                    <br>
                                    {{ $shipper->country->translateOrDefault('en')->name or '-' }}
                                @endif
                            </p>
                        </div>
                        {{--<div class="col-md-4">
                            <p><b>ORIGIN:</b><br>
                                SBR - JFK</p>
                        </div>--}}
                    </div>
                </div>
                <div class="line"></div>
                <div id="table-left-recipient">
                    <div class="row">
                        <div class="col-md-12">
                            <p><b>RECIPIENT:</b>
                                <i>{{$item->user ? ($item->user->company && $item->user->voen) ? $item->user->company : $item->user->full_name : '-' }}</i>
                                <br></p>
                            <p class="ml-2">
                                <b>Address</b> : <i>{{ $item->user ? $item->user->address : '-' }}</i><br>
                                <b>Phone</b> : <i>{{ $item->user ? $item->user->phone : '-' }}</i><br>
                                <b>Passport</b> : <i>{{ $item->user ? $item->user->passport : '-' }}</i><br>
                                <b>Fin </b>: <i>{{ $item->user ? $item->user->fin : '-' }}</i><br>
                                @if ($item->user->real_azerpoct_send && $item->user->azerpost_office && ($item->azerpost_office_id = $item->user->azerpost_office->id))
                                    <b>AZP Index </b>: <i>{{ $item->user->azerpost_office->name ?? '-' }}</i><br>
                                @endif
                            </p>
                            <p class="ml-2" style="font-size: 18px;margin-top: -7px;margin-bottom: 6px;">Azerbaijan</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-7 table-right">
                <div id="table-right-header">
                    <div class="row">
                        <div class="col-md-7">
                            <p><b>Ship Date:</b> <i>{{ $item->updated_at or '-' }}</i> <br>
                                <b>Actl Wght: </b> <i>{{ $item->weight_with_type or '-' }}</i> <br>
                                <b>Acc #:</b> <i>{{ $item->user ? $item->user->customer_id : '-' }}</i> <br>
                            </p>
                        </div>
                        <div class="col-md-5">
                            <p>
                                <b>Qty:</b> <i>1 pc{{--{{ $item->number_items_goods ?: '-' }}--}}</i> <br/>
                                <b>Dims:</b> <i>[{{ $item->width ? $item->full_size : '-' }}]</i>
                            </p>
                        </div>
                        <div class="col-md-12" style="margin-top: -15px;">
                            <b>DEC #:</b> <i>{{ $item->carrier->ecoM_REGNUMBER or "-" }}</i>
                        </div>
                        <div class="col-md-12">
                            @if (isset($shipper->country->code) && $shipper->country->code == 'us')
                                <b>REF #:</b>
                                @if ($item->tracking_code)
                                    <div class="pt-2" id="table-right-barcode2">
                                       {{-- <img src="{{$item->getBarcodeImage($item->tracking_code)}}"
                                             alt="">--}}
                                        <img src="http://barcode.tec-it.com/barcode.ashx?data={{ $item->tracking_code }}&code=Code128&dpi=300&dataseparator="
                                             alt="">
                                    </div>
                                @else
                                    <i>-</i>
                                @endif
                            @else
                                <p>
                                    <b>REF #:</b> <i>{{ $item->tracking_code or "-" }}</i>
                                </p>
                            @endif
                        </div>
                        <div class="line"></div>


                    </div>
                    <div class="pt-2" id="table-right-barcode">
                        @if ($item->custom_id)
{{--                            <img src="{{$item->getBarcodeImage($item->custom_id)}}"--}}
{{--                                 alt="">--}}
                            <img src="http://barcode.tec-it.com/barcode.ashx?data={{ $item->custom_id }}&code=Code128&dpi=300&dataseparator="
                                 alt="">
                        @endif
                    </div>
                    <div class="line"></div>
                    <div id="table-right-footer">
                        <div class="row">
                            <div class="col-md-5">
                                <p>
                                    <b>Shipping Bill To:</b> <br>
                                    <b>Taxes Bill To: </b><br>
                                    <b>Invoice Value: </b><br>
                                    <b>Delivery Value: </b><br>
                                    <b>Total Declared Value: </b><br>
                                    <b>Value Protection: </b><br>
                                    <b>Contents: </b><br>
                                </p>
                            </div>
                            <div class="col-md-7" style="float: left">
                                <p>
                                    <i>3RD PARTY</i><br>
                                    <i>3RD PARTY</i><br>
                                    <i>{{ $item->shipping_price_customs ? $item->shipping_price_customs : '-' }}</i><br>
                                    <i>{{ $item->delivery_price ? $item->delivery_usd_price . ' USD' : '-' }}</i><br>
                                    <i>{{ $item->total_price_customs ? $item->total_price_customs . ' USD' : '-' }}</i><br>
                                    <i>Std</i><br>
                                    <i>
                                        <?= $item->detailed_type . ($item->other_type ? "(" . $item->other_type . ")" : null) ?></i>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
</main>
<div class="print" onclick="window.print();">Print</div>
</body>
</html>
