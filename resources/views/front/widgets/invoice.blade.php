<html>
<head><meta http-equiv=Content-Type content="text/html; charset=UTF-8">
    <style type="text/css">
        <!--
        span.cls_011{font-family:Tahoma,serif;font-size:13.3px;color:rgb(0,0,0);font-weight:bold;font-style:normal;text-decoration: underline}
        div.cls_011{font-family:Tahoma,serif;font-size:13.3px;color:rgb(0,0,0);font-weight:bold;font-style:normal;text-decoration: none}
        span.cls_003{font-family:Tahoma,serif;font-size:10.0px;color:rgb(0,0,0);font-weight:bold;font-style:normal;text-decoration: none}
        div.cls_003{font-family:Tahoma,serif;font-size:10.0px;color:rgb(0,0,0);font-weight:bold;font-style:normal;text-decoration: none}
        span.cls_004{font-family:Tahoma,serif;font-size:8.4px;color:rgb(0,0,0);font-weight:normal;font-style:normal;text-decoration: none}
        div.cls_004{font-family:Tahoma,serif;font-size:8.4px;color:rgb(0,0,0);font-weight:normal;font-style:normal;text-decoration: none}
        span.cls_008{font-family:Arial,serif;font-size:8.8px;color:rgb(0,0,0);font-weight:bold;font-style:normal;text-decoration: none}
        div.cls_008{font-family:Arial,serif;font-size:8.8px;color:rgb(0,0,0);font-weight:bold;font-style:normal;text-decoration: none}
        span.cls_005{font-family:Tahoma,serif;font-size:8.4px;color:rgb(0,0,0);font-weight:bold;font-style:normal;text-decoration: none}
        div.cls_005{font-family:Tahoma,serif;font-size:8.4px;color:rgb(0,0,0);font-weight:bold;font-style:normal;text-decoration: none}
        span.cls_006{font-family:Tahoma,serif;font-size:7.5px;color:rgb(0,0,0);font-weight:normal;font-style:normal;text-decoration: none}
        div.cls_006{font-family:Tahoma,serif;font-size:7.5px;color:rgb(0,0,0);font-weight:normal;font-style:normal;text-decoration: none}
        span.cls_007{font-family:Tahoma,serif;font-size:6.6px;color:rgb(0,0,0);font-weight:bold;font-style:normal;text-decoration: none}
        div.cls_007{font-family:Tahoma,serif;font-size:6.6px;color:rgb(0,0,0);font-weight:bold;font-style:normal;text-decoration: none}
        span.cls_009{font-family:Arial,serif;font-size:9.0px;color:rgb(0,0,0);font-weight:normal;font-style:normal;text-decoration: none}
        div.cls_009{font-family:Arial,serif;font-size:9.0px;color:rgb(0,0,0);font-weight:normal;font-style:normal;text-decoration: none}
        span.cls_010{font-family:Tahoma,serif;font-size:9.2px;color:rgb(0,0,0);font-weight:bold;font-style:normal;text-decoration: none}
        div.cls_010{font-family:Tahoma,serif;font-size:9.2px;color:rgb(0,0,0);font-weight:bold;font-style:normal;text-decoration: none}
        -->
    </style>
</head>
<body>
    <?php $gCount=0; if ($item->goods && count($item->goods)>0) $gCount=count($item->goods); ?>
<div style="position:absolute;left:50%;margin-left:-297px;top:0px;width:595px;height:1041px;overflow:hidden">
    <div style="position:absolute;left:0px;top:0px">
    <?php $top=0; ?>
    @if ($gCount<=1)
        <img src="{{ asset('front/images/background1.jpg') }}" width=595 height=841></div>
        <?php $top=3; ?>
    @endif
    @if ($gCount>1 && $gCount<4)
        <img src="{{ asset('front/images/background2.jpg') }}" width=595 height=841></div>
        <?php $top=54; ?>
    @endif
    @if ($gCount>=4 && $gCount<7)
        <img src="{{ asset('front/images/background3.jpg') }}" width=595 height=841></div>
        <?php $top=139; ?>
    @endif
    @if ($gCount>=7 && $gCount<10)
        <img src="{{ asset('front/images/background4.jpg') }}" width=595 height=841></div>
        <?php $top=244; ?>
    @endif
    @if ($gCount>=10)
        <img src="{{ asset('front/images/background5.jpg') }}" width=595 height=841></div>
        <?php $top=329; ?>
    @endif
    @if ($item->warehouse && $item->warehouse->country && $item->warehouse->country->code=='ru')
    <div style="position:absolute;left:20px;top:0px;">
        <img src="{{ asset('front/images/logo_utrade.jpeg') }}" width="203px" heigth="33px"></div>
    <div style="position:absolute;left:261.95px;top:30.60px" class="cls_011"><span class="cls_011">PROFORMA INVOICE</span></div>
    <div style="position:absolute;left:22.40px;top:65.15px" class="cls_003"><span class="cls_003">SENDER's</span></div>
    <div style="position:absolute;left:342.40px;top:64.75px" class="cls_004"><span class="cls_004">INVOICE NUMBER</span></div>
    <div style="position:absolute;left:435.20px;top:63.60px" class="cls_004"><span class="cls_004">:</span></div>
    <div style="position:absolute;left:448.00px;top:63.60px" class="cls_008"><span class="cls_008">{{ $item->fake_invoice_id }}</span></div>
    <div style="position:absolute;left:22.40px;top:83.95px" class="cls_004"><span class="cls_004">COMPANY NAME</span></div>
    <div style="position:absolute;left:112.00px;top:83.95px" class="cls_004"><span class="cls_004">:</span></div>
    <div style="position:absolute;left:121.60px;top:82.80px" class="cls_008"><span class="cls_008">AO "UNITRADE"</span></div>
    <div style="position:absolute;left:342.40px;top:90.35px" class="cls_004"><span class="cls_004">DATE</span></div>
    <div style="position:absolute;left:435.20px;top:90.35px" class="cls_004"><span class="cls_004">:</span></div>
    <div style="position:absolute;left:448.00px;top:89.20px" class="cls_008"><span class="cls_008">{{ date_format($item->created_at, "d.m.Y") }}</span></div>
    <div style="position:absolute;left:22.40px;top:112.75px" class="cls_004"><span class="cls_004">ADDRESS</span></div>
    <div style="position:absolute;left:112.00px;top:112.75px" class="cls_004"><span class="cls_004">:</span></div>
    <div style="position:absolute;left:121.60px;top:112.75px" class="cls_008"><span class="cls_008">Street Novaya Basmannaya 14, build 4</span></div>
    <div style="position:absolute;left:332.80px;top:138.35px" class="cls_005"><span class="cls_005">RECEIVER's</span></div>
    <div style="position:absolute;left:22.40px;top:144.75px" class="cls_004"><span class="cls_004">POSTAL CODE</span></div>
    <div style="position:absolute;left:112.00px;top:144.75px" class="cls_004"><span class="cls_004">:</span></div>
    <div style="position:absolute;left:121.60px;top:144.75px" class="cls_008"><span class="cls_008">107078</span></div>
    <div style="position:absolute;left:22.40px;top:160.75px" class="cls_004"><span class="cls_004">CITY</span></div>
    <div style="position:absolute;left:112.00px;top:160.75px" class="cls_004"><span class="cls_004">:</span></div>
    <div style="position:absolute;left:121.60px;top:159.60px" class="cls_008"><span class="cls_008">Moscow</span></div>
    <div style="position:absolute;left:332.80px;top:160.65px" class="cls_006"><span class="cls_006">AIR WAYBILL NO</span></div>
    <div style="position:absolute;left:435.20px;top:160.75px" class="cls_004"><span class="cls_004">:</span></div>
    <div style="position:absolute;left:444.80px;top:159.60px" class="cls_008"><span class="cls_008">{{ $item->tracking_code }}</span></div>
    <div style="position:absolute;left:22.40px;top:173.55px" class="cls_004"><span class="cls_004">COUNTRY</span></div>
    <div style="position:absolute;left:112.00px;top:173.55px" class="cls_004"><span class="cls_004">:</span></div>
    <div style="position:absolute;left:121.60px;top:172.40px" class="cls_008"><span class="cls_008">{{ ($item->warehouse && $item->warehouse->country) ? (isset($item->warehouse->country->translate('en')->name) ? $item->warehouse->country->translate('en')->name : $item->warehouse->country->name) : "-" }}</span></div>
    <div style="position:absolute;left:22.40px;top:186.35px" class="cls_004"><span class="cls_004">PHONE</span></div>
    <div style="position:absolute;left:112.00px;top:186.35px" class="cls_004"><span class="cls_004">:</span></div>
    <div style="position:absolute;left:121.60px;top:186.35px" class="cls_008"><span class="cls_008">{{ $item->warehouse->addresses[0]->phone }}</span></div>
    @else
    <div style="position:absolute;left:261.95px;top:30.60px" class="cls_011"><span class="cls_011">INVOICE</span></div>
    <div style="position:absolute;left:22.40px;top:65.15px" class="cls_003"><span class="cls_003">SENDER's</span></div>
    <div style="position:absolute;left:342.40px;top:64.75px" class="cls_004"><span class="cls_004">INVOICE NUMBER</span></div>
    <div style="position:absolute;left:435.20px;top:63.60px" class="cls_004"><span class="cls_004">:</span></div>
    <div style="position:absolute;left:448.00px;top:63.60px" class="cls_008"><span class="cls_008">{{ str_replace("E", "C", $item->tracking_code) }}</span></div>
    <div style="position:absolute;left:22.40px;top:83.95px" class="cls_004"><span class="cls_004">COMPANY NAME</span></div>
    <div style="position:absolute;left:112.00px;top:83.95px" class="cls_004"><span class="cls_004">:</span></div>
    <div style="position:absolute;left:121.60px;top:82.80px" class="cls_008"><span class="cls_008">{{ strtoupper(getOnlyDomain($item->website_name)) }}</span></div>
    <div style="position:absolute;left:342.40px;top:90.35px" class="cls_004"><span class="cls_004">DATE</span></div>
    <div style="position:absolute;left:435.20px;top:90.35px" class="cls_004"><span class="cls_004">:</span></div>
    <div style="position:absolute;left:448.00px;top:89.20px" class="cls_008"><span class="cls_008">{{ date_format($item->created_at, "d.m.Y") }}</span></div>
    <div style="position:absolute;left:22.40px;top:112.75px" class="cls_004"><span class="cls_004">ADDRESS</span></div>
    <div style="position:absolute;left:112.00px;top:112.75px" class="cls_004"><span class="cls_004">:</span></div>
    <div style="position:absolute;left:121.60px;top:111.60px" class="cls_008"><span class="cls_008">{{ $item->fake_address }}</span></div>
    <div style="position:absolute;left:332.80px;top:138.35px" class="cls_005"><span class="cls_005">RECEIVER's</span></div>
    <div style="position:absolute;left:22.40px;top:144.75px" class="cls_004"><span class="cls_004">POSTAL CODE</span></div>
    <div style="position:absolute;left:112.00px;top:144.75px" class="cls_004"><span class="cls_004">:</span></div>
    <div style="position:absolute;left:22.40px;top:160.75px" class="cls_004"><span class="cls_004">CITY</span></div>
    <div style="position:absolute;left:112.00px;top:160.75px" class="cls_004"><span class="cls_004">:</span></div>
    <div style="position:absolute;left:121.60px;top:159.60px" class="cls_008"><span class="cls_008">{{ ucfirst(str_slug(($item->warehouse && $item->warehouse->addresses) ? $item->warehouse->addresses()->first()->city : "-")) }}</span></div>
    <div style="position:absolute;left:332.80px;top:160.65px" class="cls_006"><span class="cls_006">AIR WAYBILL NO</span></div>
    <div style="position:absolute;left:435.20px;top:160.75px" class="cls_004"><span class="cls_004">:</span></div>
    <div style="position:absolute;left:444.80px;top:159.60px" class="cls_008"><span class="cls_008">{{ $item->tracking_code }}</span></div>
    <div style="position:absolute;left:22.40px;top:173.55px" class="cls_004"><span class="cls_004">COUNTRY</span></div>
    <div style="position:absolute;left:112.00px;top:173.55px" class="cls_004"><span class="cls_004">:</span></div>
    <div style="position:absolute;left:121.60px;top:172.40px" class="cls_008"><span class="cls_008">{{ ($item->warehouse && $item->warehouse->country) ? (isset($item->warehouse->country->translate('en')->name) ? $item->warehouse->country->translate('en')->name : $item->warehouse->country->name) : "-" }}</span></div>
    <div style="position:absolute;left:22.40px;top:186.35px" class="cls_004"><span class="cls_004">CONTACT NAME</span></div>
    <div style="position:absolute;left:112.00px;top:186.35px" class="cls_004"><span class="cls_004">:</span></div>
    @endif
    <div style="position:absolute;left:332.80px;top:186.25px" class="cls_006"><span class="cls_006">TAX NO</span></div>
    <div style="position:absolute;left:435.20px;top:186.35px" class="cls_004"><span class="cls_004">:</span></div>
    <div style="position:absolute;left:22.40px;top:212.35px" class="cls_003"><span class="cls_003">RECEIVER's</span></div>
    <div style="position:absolute;left:336.00px;top:215.05px" class="cls_006"><span class="cls_006">PHONE</span></div>
    <div style="position:absolute;left:435.20px;top:215.15px" class="cls_004"><span class="cls_004">:</span></div>
    <div style="position:absolute;left:447.23px;top:215.00px" class="cls_008"><span class="cls_008">{{ $item->user ? $item->user->cleared_phone : "-" }}</span></div>
    <div style="position:absolute;left:22.40px;top:231.15px" class="cls_004"><span class="cls_004">COMPANY NAME</span></div>
    <div style="position:absolute;left:112.00px;top:231.15px" class="cls_004"><span class="cls_004">:</span></div>
    <div style="position:absolute;left:121.60px;top:230.00px" class="cls_008"><span class="cls_008">{{$item->user ? ($item->user->company && $item->user->voen) ? $item->user->company : $item->user->full_name : '-' }}</span></div>
    <div style="position:absolute;left:336.00px;top:243.85px" class="cls_006"><span class="cls_006">FAX / TELEX</span></div>
    <div style="position:absolute;left:435.20px;top:243.95px" class="cls_004"><span class="cls_004">:</span></div>
    <div style="position:absolute;left:22.40px;top:263.15px" class="cls_004"><span class="cls_004">ADDRESS</span></div>
    <div style="position:absolute;left:112.00px;top:263.15px" class="cls_004"><span class="cls_004">:</span></div>
    <div style="position:absolute;left:121.60px;top:262.00px; height: 40px; width: 180px;" class="cls_008"><span class="cls_008"  style="font-family: firefly, DejaVu Sans, sans-serif;">{{ $item->user ? $item->user->address : "-" }}</span></div>
    <div style="position:absolute;left:336.00px;top:272.65px" class="cls_006"><span class="cls_006">NUMBER OF PIECES</span></div>
    <div style="position:absolute;left:435.20px;top:272.75px" class="cls_004"><span class="cls_004">:</span></div>
    <div style="position:absolute;left:447.23px;top:271.60px" class="cls_008"><span class="cls_008">1</span></div>
    <div style="position:absolute;left:22.40px;top:295.15px" class="cls_004"><span class="cls_004">POSTAL CODE</span></div>
    <div style="position:absolute;left:112.00px;top:295.15px" class="cls_004"><span class="cls_004">:</span></div>
    <div style="position:absolute;left:121.60px;top:294.00px" class="cls_008"><span class="cls_008">{{ $item->user ? $item->user->fin : "-" }}</span></div>
    <div style="position:absolute;left:336.00px;top:304.65px" class="cls_006"><span class="cls_006">TOTAL GROSS WEIGHT</span></div>
    <div style="position:absolute;left:435.20px;top:304.75px" class="cls_004"><span class="cls_004">:</span></div>
    <div style="position:absolute;left:444.80px;top:303.60px" class="cls_008"><span class="cls_008">{{ number_format($item->weight, 2, ",", " ") . " KG" }} </span></div>
    <div style="position:absolute;left:22.40px;top:311.15px" class="cls_004"><span class="cls_004">CITY</span></div>
    <div style="position:absolute;left:112.00px;top:311.15px" class="cls_004"><span class="cls_004">:</span></div>
    <div style="position:absolute;left:121.60px;top:310.00px" class="cls_008"><span class="cls_008">{{ ($item->user) ? ucfirst(str_slug($item->user->city_name, '_')) : "-" }}</span></div>
    <div style="position:absolute;left:22.40px;top:323.95px" class="cls_004"><span class="cls_004">COUNTRY</span></div>
    <div style="position:absolute;left:112.00px;top:323.95px" class="cls_004"><span class="cls_004">:</span></div>
    <div style="position:absolute;left:121.60px;top:322.80px" class="cls_008"><span class="cls_008">Azerbaijan</span></div>
    <div style="position:absolute;left:336.00px;top:333.45px" class="cls_006"><span class="cls_006">TOTAL NET WEIGHT</span></div>
    <div style="position:absolute;left:435.20px;top:333.55px" class="cls_004"><span class="cls_004">:</span></div>
    <div style="position:absolute;left:444.80px;top:332.40px" class="cls_008"><span class="cls_008">{{ number_format($item->net_weight, 2, ",", " ") . " KG" }} </span></div>
    <div style="position:absolute;left:22.40px;top:336.75px" class="cls_004"><span class="cls_004">CONTACT NAME</span></div>
    <div style="position:absolute;left:112.00px;top:336.75px" class="cls_004"><span class="cls_004">:</span></div>
    <div style="position:absolute;left:121.60px;top:336.75px" class="cls_008"><span class="cls_008">{{$item->user ? $item->user->full_name : '-' }}</span></div>
    <div style="position:absolute;left:57.15px;top:371.75px" class="cls_007"><span class="cls_007">FULL DESCRIPTION OF GOODS</span></div>
    <div style="position:absolute;left:246.55px;top:371.75px" class="cls_007"><span class="cls_007">CUSTOMS</span></div>
    <div style="position:absolute;left:331.60px;top:371.75px" class="cls_007"><span class="cls_007">COUNTRY</span></div>
    <div style="position:absolute;left:389.05px;top:371.75px" class="cls_007"><span class="cls_007">QTY</span></div>
    <div style="position:absolute;left:419.10px;top:371.75px" class="cls_007"><span class="cls_007">UNIT VALUE AND</span></div>
    <div style="position:absolute;left:508.55px;top:371.75px" class="cls_007"><span class="cls_007">SUB TOTAL</span></div>
    <div style="position:absolute;left:232.15px;top:381.50px" class="cls_007"><span class="cls_007">COMMODITY CODE</span></div>
    <div style="position:absolute;left:330.35px;top:381.50px" class="cls_007"><span class="cls_007">OF ORIGIN</span></div>
    <div style="position:absolute;left:430.35px;top:381.50px" class="cls_007"><span class="cls_007">CURRENCY</span></div>
    <div style="position:absolute;left:508.95px;top:381.50px" class="cls_007"><span class="cls_007">VALUE AND</span></div>
    <div style="position:absolute;left:510.60px;top:391.25px" class="cls_007"><span class="cls_007">CURRENCY</span></div>
    @if ($item->goods && count($item->goods)>0)
    <?php $t=0; ?>
    @foreach ($item->goods as $good)
    <div style="position:absolute;left:22.40px;top:{{415.05+$t}}px; width: 195px; line-height: 0.7em;" class="cls_009"><span class="cls_009" style="font-family: firefly, DejaVu Sans, sans-serif;">{{ $good->name }}</span></div>
    <div style="position:absolute;left:341.25px;top:{{415.10+$t}}px" class="cls_009"><span class="cls_009">{{ ($item->warehouse && $item->warehouse->country) ? strtoupper($item->warehouse->country->code) : "-" }}</span></div>
    <div style="position:absolute;left:391.94px;top:{{415.10+$t}}px" class="cls_009"><span class="cls_009">{{ $good->number_items }}</span></div>
    @if ($item->warehouse && $item->warehouse->country && $item->warehouse->country->code=='ru')
    <div style="position:absolute;left:424.44px;top:{{415.10+$t}}px" class="cls_009"><span class="cls_009">{{ $good->shipping_price_ru }}</span></div>
    <div style="position:absolute;left:506.94px;top:{{415.10+$t}}px" class="cls_009"><span class="cls_009">{{ $good->shipping_price_ru }}</span></div>
    @else
    <div style="position:absolute;left:424.44px;top:{{415.10+$t}}px" class="cls_009"><span class="cls_009">{{ $good->shipping_price }}</span></div>
    <div style="position:absolute;left:506.94px;top:{{415.10+$t}}px" class="cls_009"><span class="cls_009">{{ $good->shipping_price }}</span></div>
    @endif
    <?php $t+=30; ?>
    @endforeach
    @else
    <div style="position:absolute;left:22.40px;top:415.05px; width: 195px" class="cls_009"><span class="cls_009">{{ $item->detailed_type }}</span></div>
    <div style="position:absolute;left:341.25px;top:415.10px" class="cls_009"><span class="cls_009">{{ ($item->warehouse && $item->warehouse->country) ? strtoupper($item->warehouse->country->code) : "-" }}</span></div>
    <div style="position:absolute;left:393.94px;top:415.10px" class="cls_009"><span class="cls_009">1</span></div>
    @if ($item->warehouse && $item->warehouse->country && $item->warehouse->country->code=='ru')
    <div style="position:absolute;left:444.44px;top:415.10px" class="cls_009"><span class="cls_009">{{ $item->shipping_price_ru }}</span></div>
    <div style="position:absolute;left:519.94px;top:415.10px" class="cls_009"><span class="cls_009">{{ $item->shipping_price_ru }}</span></div>
    @else
    <div style="position:absolute;left:444.44px;top:415.10px" class="cls_009"><span class="cls_009">{{ $item->shipping_price }}</span></div>
    <div style="position:absolute;left:519.94px;top:415.10px" class="cls_009"><span class="cls_009">{{ $item->shipping_price }}</span></div>
    @endif
    @endif
    <div style="position:absolute;left:415.50px;top:{{458.10+$top}}px" class="cls_007"><span class="cls_007">TOTAL VALUE</span></div>
    <div style="position:absolute;left:415.50px;top:{{467.85+$top}}px" class="cls_007"><span class="cls_007">AND CURRENCY</span></div>
    @if ($item->warehouse && $item->warehouse->country && $item->warehouse->country->code=='ru')
    <div style="position:absolute;left:503.45px;top:{{463.10+$top}}px" class="cls_010"><span class="cls_010">{{ $item->shipping_price_ru }}</span></div>
    @else
    <div style="position:absolute;left:503.45px;top:{{463.10+$top}}px" class="cls_010"><span class="cls_010">{{ $item->shipping_price }}</span></div>
    @endif
    <div style="position:absolute;left:23.25px;top:{{486.85+$top}}px" class="cls_005"><span class="cls_005">CUSTOM PURPOSE</span></div>
    <div style="position:absolute;left:22.50px;top:{{507.80+$top}}px" class="cls_005"><span class="cls_005">TERMS OF PAYMENT</span></div>
    <div style="position:absolute;left:147.00px;top:{{507.80+$top}}px" class="cls_005"><span class="cls_005">:</span></div>
    <div style="position:absolute;left:160.50px;top:{{507.85+$top}}px" class="cls_005"><span class="cls_005">Sender Pays</span></div>
    <div style="position:absolute;left:22.50px;top:{{530.30+$top}}px" class="cls_005"><span class="cls_005">REASON FOR SENDING</span></div>
    <div style="position:absolute;left:147.00px;top:{{530.30+$top}}px" class="cls_005"><span class="cls_005">:</span></div>
    <div style="position:absolute;left:22.50px;top:{{552.80+$top}}px" class="cls_005"><span class="cls_005">TERMS OF DELIVERY</span></div>
    <div style="position:absolute;left:147.00px;top:{{552.80+$top}}px" class="cls_005"><span class="cls_005">:</span></div>
    @if ($item->warehouse && $item->warehouse->country && $item->warehouse->country->code=='ru')
    <div style="position:absolute;left:160.50px;top:{{552.85+$top}}px" class="cls_005"><span class="cls_005">DAP Baku</span></div>
    <div style="position:absolute;left:371.25px;top:{{592.30+$top}}px" class="cls_005"><span class="cls_005">Place and Date:</span></div>
    <div style="position:absolute;left:371.25px;top:{{608.05+$top}}px" class="cls_005"><span class="cls_005">Name:</span></div>
    <div style="position:absolute;left:371.25px;top:{{627.55+$top}}px" class="cls_005"><span class="cls_005">Signature:</span></div>
    @else
    <div style="position:absolute;left:160.50px;top:{{552.85+$top}}px" class="cls_005"><span class="cls_005">DDU Baku</span></div>
    <div style="position:absolute;left:22.50px;top:{{575.30+$top}}px" class="cls_005"><span class="cls_005">COUNTRY OF ORIGIN</span></div>
    <div style="position:absolute;left:147.00px;top:{{575.30+$top}}px" class="cls_005"><span class="cls_005">:</span></div>
    <div style="position:absolute;left:160.50px;top:{{575.35+$top}}px" class="cls_005"><span class="cls_005">{{ ($item->warehouse && $item->warehouse->country) ? (isset($item->warehouse->country->translate('en')->name) ? $item->warehouse->country->translate('en')->name : $item->warehouse->country->name) : "-" }}</span></div>
    <div style="position:absolute;left:22.50px;top:{{592.35+$top}}px" class="cls_009"><span class="cls_009">The exporter of product covered by this document (customs autorization No:…………..….…….)</span></div>
    <div style="position:absolute;left:22.50px;top:{{603.75+$top}}px" class="cls_009"><span class="cls_009">declares that, except where otherwise clearly indicated , these products are of {{ ($item->warehouse && $item->warehouse->country) ? (isset($item->warehouse->country->translate('en')->name) ? $item->warehouse->country->translate('en')->name : $item->warehouse->country->name) : "-" }} preferential origin.</span></div>
    <div style="position:absolute;left:371.25px;top:{{632.30+$top}}px" class="cls_005"><span class="cls_005">Place and Date:</span></div>
    <div style="position:absolute;left:371.25px;top:{{648.05+$top}}px" class="cls_005"><span class="cls_005">Name:</span></div>
    <div style="position:absolute;left:371.25px;top:{{667.55+$top}}px" class="cls_005"><span class="cls_005">Signature:</span></div>
    @endif
</div>

</body>
</html>
