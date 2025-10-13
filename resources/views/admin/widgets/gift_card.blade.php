<!DOCTYPE html>

<html>

<head>
    <meta charset="UTF-8">
    <meta name="description" content="Label # {{ $item->id }}">
    <meta name="author" content="{{ env('APP_NAME') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Label # {{ $item->id }}</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css">
    <style>

        html, body {
            height: 98%;
            page-break-after: avoid !important;
            page-break-before: avoid !important;
            overflow: hidden !important;
        }
        @media print {
            @page {
                size: auto;  margin: 0mm;
                height: 98%;
                page-break-after: avoid !important;
                page-break-before: avoid !important;
            }
        }

        img {
            width: 575px;
            position: absolute;
            top: 0;
            left: 0;
            padding: 20px;
            border-right: 1px dashed #ccc;
            border-bottom: 1px dashed #ccc;
        }

        .amount {
            z-index: 9999;
            position: absolute;
            font-size: 26px;
            top: 60px;
            left: 440px;
            font-weight: 600;
        }
        .card_number {
            z-index: 9999;
            position: absolute;
            font-size: 57px;
            top: 197px;
            left: 132px;
        }

    </style>

</head>

<body>

<main>
    <div class="container" style="width: 100%;">
        <div class="card_number">{{ $item->card_number }}</div>
        <div class="amount">{{ $item->amount }}</div>
        <img src="{{ asset('admin/images/gift_card.png') }}" >
    </div>
</main>

</body>

</html>