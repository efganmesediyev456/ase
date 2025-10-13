<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">

<head>
    @include('front.widgets.metaTags')
    <title>{{ $title or config('app.name') }}</title>
    <meta name="facebook-domain-verification" content="a9efnsw62gqvtd6lubwxt4h2zmshst" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700|Merriweather:300,300i,400,400i,700,700i"
          rel="stylesheet">
    <!-- Bootstrap -->
    <link href="{{ elixir('front/all.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('front/css/custom.css') }}?v={{time()}}" rel="stylesheet" type="text/css">
@stack('css')
<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js" defer></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js" defer></script>
    <![endif]-->
    <!-- Google Tag Manager -->
    <script>(function (w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start':
                    new Date().getTime(), event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-{{ env('GTM') }}');</script>
    <!-- End Google Tag Manager -->
    <!-- Meta Pixel Code -->
    <script>
	!function(f,b,e,v,n,t,s)
	{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
	n.callMethod.apply(n,arguments):n.queue.push(arguments)};
	if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
	n.queue=[];t=b.createElement(e);t.async=!0;
	t.src=v;s=b.getElementsByTagName(e)[0];
	s.parentNode.insertBefore(t,s)}(window, document,'script',
	'https://connect.facebook.net/en_US/fbevents.js');
	fbq('init', '357694728892560');
	fbq('track', 'PageView');
    </script>
    <script>
	var lang_locale='{{\App::getLocale()}}';
    </script>

    <noscript><img height="1" width="1" style="display:none"
	src="https://www.facebook.com/tr?id=357694728892560&ev=PageView&noscript=1"
    /></noscript>
    <!-- End Meta Pixel Code -->
</head>

<body class="">
<!-- Google Tag Manager (noscript) -->
<noscript>
    <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-{{ env('GTM') }}"
            height="0" width="0" style="display:none;visibility:hidden"></iframe>
</noscript>
<!-- End Google Tag Manager (noscript) -->
@include('front.widgets.loading')
@if(!isset($hideNavBar))
    @include('front.sections.nav-bar')
@endif

@yield('content')

@include('front.sections.footer')

<style>
input[type=checkbox]
{
  /* Double-sized Checkboxes */
  -ms-transform: scale(2); /* IE */
  -moz-transform: scale(2); /* FF */
  -webkit-transform: scale(2); /* Safari and Chrome */
  -o-transform: scale(2); /* Opera */
  transform: scale(2);
  padding: 10px;
  display: inline;
}

/* Might want to wrap a span around your checkbox text */
.checkboxtext
{
  /* Checkbox text */
  font-size: 110%;
  padding: 10px;
  display: inline;
}
</style>
<link href="{{ asset('front/css/alert.css') }}?v=1.0.0.1" rel="stylesheet" />
<script src="{{ elixir('front/all.js') }}"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="{{ asset('front/js/package.js') }}?v={{time()}}"></script>

@stack('js')

</body>

</html>
