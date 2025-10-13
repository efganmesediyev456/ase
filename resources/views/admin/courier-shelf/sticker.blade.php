<!DOCTYPE html>

<html>

<head>
    <meta charset="UTF-8">
    <meta name="description" content="Label # {{ $result->barcode }}">
    <meta name="author" content="ASE">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Label # {{ $result->barcode }}</title>

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
            /*border: 1px solid black;*/
            width: 41%;
        }

        .row{
            flex-wrap: nowrap !important;
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
           /*border: 1px solid black;*/
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
            <div class="col-md-7 table-right">
                <div id="table-right-header">
                    <div class="row">
                        <div class="col-md-12">
                                    <div class="pt-2" id="table-right-barcode2">
                                        <img src="http://barcode.tec-it.com/barcode.ashx?data={{ $result->barcode }}&code=Code128&dpi=300&dataseparator="
                                             alt="">
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
