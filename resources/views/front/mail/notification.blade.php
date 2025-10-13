<!doctype html>
<html>
<head>
    <meta name="viewport" content="width=device-width"/>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>Simple Transactional Email</title>
    <style>
        body {
            font-family: "Roboto", sans-serif;
            font-size: 14px;
            line-height: 1.7;
            font-weight: 400;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            position: relative;
            background: #e0e8f3;
            min-width: 320px;
            color: #495463;
        }

        img {
            max-width: 100%;
        }

        h1, h2, h3, h4, h5, h6, p, ul:not([class]), ol, table {
            margin-bottom: 12px;
        }

        h1:last-child, h2:last-child, h3:last-child, h4:last-child, h5:last-child, h6:last-child, p:last-child, ul:not([class]):last-child, ol:last-child, table:last-child {
            margin-bottom: 0;
        }

        h1, h2, h3, h4, h5, h6, label {
            font-weight: 400;
            line-height: 1.3;
        }

        h1, h2, h4, h5 {
            color: #253992;
        }

        h1, .h1 {
            font-size: 1.37em;
        }

        h2, .h2 {
            font-size: 1.3em;
        }

        h3, .h3 {
            font-size: 1.2em;
        }

        h4, .h4 {
            font-size: 1.15em;
        }

        h5, .h5 {
            font-size: 1.07em;
        }

        h6, .h6 {
            font-size: .93em;
        }

        p {
            font-size: 1em;
        }

        p.lead {
            font-size: 1.15em;
        }

        p.large {
            font-size: 1.1em;
        }

        @media (min-width: 576px) {
            h1, .h1 {
                font-size: 1.714em;
            }

            h2, .h2 {
                font-size: 1.62em;
            }

            h3, .h3 {
                font-size: 1.52em;
            }

            h4, .h4 {
                font-size: 1.29em;
            }

            p.large {
                font-size: 1.2em;
            }
        }

        @media (min-width: 768px) {
            body {
                font-size: 15px;
            }
        }

        h1 span, h2 span, h3 span, h4 span, h5 span, h6 span, p span {
            color: #2c80ff;
        }

        ul, ol {
            padding: 0px;
            margin: 0px;
        }

        ul li, ol li {
            list-style: none;
        }

        .relative {
            position: relative;
        }

        p + h1, p + h2, p + h3, p + h4, p + h5, p + h4, ul + h1, ul + h2, ul + h3, ul + h4, ul + h5, ul + h4, ol + h1, ol + h2, ol + h3, ol + h4, ol + h5, ol + h4, table + h1, table + h2, table + h3, table + h4, table + h5, table + h4 {
            margin-top: 30px;
        }

        ul + p:not([class]), ul + ul:not([class]), ul + ol:not([class]), ol + ol:not([class]), ol + ul:not([class]), ul + table:not([class]), ol + table:not([class]) {
            margin-top: 35px;
        }

        b, strong {
            font-weight: 500;
        }

        blockquote {
            font-size: 1.3em;
            background: #e0e8f3;
            padding: 20px 30px;
            font-style: italic;
        }

        a {
            outline: 0;
            transition: all 0.5s;
            color: #1c65c9;
        }

        a:link, a:visited {
            text-decoration: none;
        }

        a:hover, a:focus, a:active {
            outline: 0;
            color: #253992;
        }

        p a {
            color: #1c65c9;
        }

        p a:hover, p a:focus {
            color: #2c80ff;
            box-shadow: 0 1px 0 currentColor;
        }

        /*! Email Template Preview Purpose */
        .email-wraper {
            background: #fff;
            font-size: 14px;
            line-height: 22px;
            font-weight: 400;
            color: #758698;
            width: 100%;
            text-align: center;
            font-size: 16px;
        }

        .email-wraper a {
            color: #1babfe;
            word-break: break-all;
        }

        .email-wraper .link-block {
            display: block;
        }

        .email-ul {
            margin: 5px 0;
            padding: 0;
        }

        .email-ul:not(:last-child) {
            margin-bottom: 10px;
        }

        .email-ul li {
            list-style: disc;
            list-style-position: inside;
        }

        .email-ul-col2 {
            display: flex;
            flex-wrap: wrap;
        }

        .email-ul-col2 li {
            width: 50%;
            padding-right: 10px;
        }

        .email-body {
            padding: 20px;
            width: 96%;
            max-width: 620px;
            margin: 0 auto;
            background: #e3210f05;
            border: 1px solid #e6effb;
            border-bottom: 4px solid #e3210f;
        }

        .email-success {
            border-bottom-color: #00d285;
        }

        .email-warning {
            border-bottom-color: #ffc100;
        }

        .email-btn {
            background: #1babfe;
            border-radius: 4px;
            color: #ffffff !important;
            display: inline-block;
            font-size: 13px;
            font-weight: 600;
            line-height: 44px;
            text-align: center;
            text-decoration: none;
            text-transform: uppercase;
            padding: 0 30px;
        }

        .email-btn-sm {
            line-height: 38px;
        }

        .email-header, .email-footer {
            width: 100%;
            max-width: 620px;
            margin: 0 auto;
        }

        .email-logo {
            height: 40px;
        }

        .email-title {
            font-size: 13px;
            color: #1babfe;
            padding-top: 12px;
        }

        .email-heading {
            font-size: 18px;
            color: #1babfe;
            font-weight: 600;
            margin: 0;
        }

        .email-heading-sm {
            font-size: 16px;
        }

        .email-heading-s2 {
            font-size: 15px;
            color: #000000;
            font-weight: 600;
            margin: 0;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .email-heading-s3 {
            font-size: 18px;
            color: #1babfe;
            font-weight: 400;
            margin-bottom: 8px;
        }

        .email-heading-success {
            color: #00d285;
        }

        .email-heading-warning {
            color: #ffc100;
        }

        .email-note {
            margin: 0;
            font-size: 13px;
            line-height: 22px;
            color: #6e81a9;
        }

        .email-copyright-text {
            font-size: 13px;
        }

        .email-social li {
            display: inline-block;
            padding: 4px;
        }

        .email-social li a {
            display: inline-block;
            height: 30px;
            width: 30px;
            border-radius: 50%;
            background: #ffffff;
        }

        .email-social li a img {
            width: 30px;
        }

        @media (max-width: 480px) {
            .email-preview-page .card {
                border-radius: 0;
                margin-left: -20px;
                margin-right: -20px;
            }

            .email-ul-col2 li {
                width: 100%;
            }
        }

        .card {
            border-radius: 4px;
            margin-bottom: 15px;
            border: none;
            background: #fff;
            transition: all .4s;
            vertical-align: top;
            flex-direction: column;
            min-width: 0;
            word-wrap: break-word;
        }
        /*# sourceMappingURL=style-email.css.map */
        .card-innr {
            padding: 16px 20px;
            border-color: #e6effb !important;
        }
        .padding_inner {
            padding: 25px 10px;
        }

    </style>
</head>
<body class="">
<?php $setting = App\Models\Setting::find(1); ?>
<div class="card">
<div class="card-innr">
    <table class="email-wraper">
        <tr>
            <td class="padding_inner">
                <table class="email-header">
                    <tbody>
                    <tr>
                        <td class="text-center pdb-2-5x">
                            <a href="#"><img class="email-logo" src="https://aseshop.az/uploads/setting/59e0ea432e511.png"
                                             alt="logo"></a>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <table class="email-body">
                    <tbody>
                    <tr>
                        <td class="pd-3x pdb-3x">
                            <p class="mgb-1x">{!! $content !!}</p>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <table class="email-footer">
                    <tbody>
                    <tr>
                        <td class="text-center pdt-2-5x pdl-2x pdr-2x">
                            <p class="email-copyright-text">Bütün hüquqlar qorunur © {{ date('Y') }} {{ env('APP_NAME') }}
                                . </p>
                            <ul class="email-social">
                                @if ($setting->facebook)
                                    <li><a href="{{ $setting->facebook }}"><img
                                                    src="{{ asset('email_assets/images/fb.png') }}" alt="brand"></a>
                                    </li>
                                @endif
                                @if ($setting->twitter)
                                    <li><a href="{{ $setting->twitter }}"><img src="{{ asset('email_assets/images/tw.png') }}"
                                                                               alt="brand"></a></li>
                                @endif
                                @if ($setting->instagram)
                                    <li><a href="{{ $setting->instagram }}"><img src="{{ asset('email_assets/images/ins.png') }}"
                                                                                 alt="brand"></a></li>
                                @endif
                                @if ($setting->linkedin)
                                    <li><a href="{{ $setting->linkedin }}"><img src="{{ asset('email_assets/images/li.png') }}"
                                                                                alt="brand"></a></li>
                                @endif
                            </ul>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>
</div>
</div>
</body>
</html>