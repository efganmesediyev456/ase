<!DOCTYPE html>
<html lang="en">

@include('vendor.saysay.base.sections.head')

<body class="{{ $bodyClass or ((auth()->guard('admin')->check() && ! auth()->guard('admin')->user()->show_menu) ? 'sidebar-xs' : '') }}">

@if(! isset($hideNavBar))
    @include('vendor.saysay.base.sections.navbar')
@endif

@include('vendor.saysay.base.elements.loading')
<!-- Page container -->
<div class="page-container">

    <!-- Page content -->
    <div class="page-content">

    @if(! isset($hideSideBar))
        <!-- Main sidebar -->
        @include('vendor.saysay.base.sections.sidebar')
    @endif

    <!-- Main content -->
        <div class="content-wrapper">

            <!-- Content area -->
            <div class="content">

                @yield('content')

            </div>
            <!-- /content area -->

        </div>
        <!-- /main content -->

    </div>
    <!-- /page content -->

</div>

<!-- /page container -->
<!-- Core JS files -->
<script type="text/javascript" src="{{ elixir('admin/all.js') }}"></script>
<script src="{{ asset('admin/js/custom.js') }}?v=1.0.7.8"></script>

@stack('head_js')
<!-- /theme JS files -->
@stack('js')

@include('sweet::alert')
@include('vendor.saysay.crud.inc.modal')

@if(isset($_viewDir) && $_viewDir && $_viewDir!='cd')
    <a target="_blank"></a>
    <div id="scan_url" data-scan-url="{{ route($_viewDir . '.barcode.scan') }}"></div>
    <div id="create_package" data-url="{{ route($_createPackage) }}"></div>
    <div id="delete_url" data-delete-url="{{ route('w-parceling.detach') }}"></div>
    <div id="auto_print" data-enabled="{{ $_autoPrint ? 'yes' : 'no' }}"></div>
    <div id="auto_print_package" data-enabled="{{ $_autoPrintPackage ? 'yes' : 'no' }}"></div>
    <div id="auto_print_parcel" data-enabled="{{ $_autoPrintParcel ? 'yes' : 'no' }}"></div>
    <div id="auto_print_package_invoice" data-enabled="{{ $_autoPrintPackageInvoice ? 'yes' : 'no' }}"></div>
    <div id="auto_print_parcel_invoice" data-enabled="{{ $_autoPrintParcelInvoice ? 'yes' : 'no' }}"></div>
    <div id="fake_invoice" data-enabled="{{ $_fakeInvoice ? 'yes' : 'no' }}"></div>
    <div id="show_label" data-enabled="{{ $_showLabel ? 'yes' : 'no' }}"></div>
    <div id="show_invoice" data-enabled="{{ $_showInvoice ? 'yes' : 'no' }}"></div>

    @if($_autoPrint)
        <script src="https://cdn.jsdelivr.net/npm/js-cookie@beta/dist/js.cookie.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bluebird/3.3.5/bluebird.min.js"></script>
        <script src="{{ asset('admin/js/printer/zip.js') }}"></script>
        <script src="{{ asset('admin/js/printer/zip-ext.js') }}"></script>
        <script src="{{ asset('admin/js/printer/deflate.js') }}"></script>
        <script src="{{ asset('admin/js/printer/JSPrintManager.js') }}"></script>
        <script src="{{ asset('admin/js/printer/custom.js') }}?v=1.0.1.8"></script>
    @endif

    @if($_cellUpdate)
        <script src="https://js.pusher.com/6.0/pusher.min.js"></script>
        <script src="{{ asset('admin/js/ion.sound.min.js') }}"></script>
        <div id="cities" data-value="[{{ implode(",", $_cities) }}]"></div>
        <div id="store_status" data-value="{{ $_store_status }}"></div>
        <script>
            ion.sound({
                sounds: [
                    {
                        alias: "find_it",
                        name: "bell_ring",
                        loop: 2
                    },
                    {name: "water_droplet_2"}
                ],
                path: "/sounds/",
                preload: false,
                volume: 1
            });

            // Enable pusher logging - don't include this in production
            Pusher.logToConsole = true;

            var pusher = new Pusher('<?= env('PUSHER_APP_KEY') ?>', {
                cluster: 'eu'
            });

            var channel = pusher.subscribe('my-channel');
            channel.bind('my-event', function (data) {
                var cities = $("#cities").data("value");
                var store_status = $("#store_status").data("value");
                var show = true;
                if (Array.isArray(cities) && cities.length && $.inArray(data.city_id, cities) === -1) {
                    show = false;
                }
		//admin in baku but package/track in kobia
		/*if(store_status!=2 && ((data.track==0 && data.status==8) || (data.track==1 && data.status==20)))
		    show=false;
		//admin in kobia but package/track in baku
		if(store_status==2 && ((data.track==0 && data.status==2) || (data.track==1 && data.status==16)))
			show=false;*/
		if(store_status != data.status)
			show=false;

                if (show) {
                    if (data.success) {
                        ion.sound.play("water_droplet_2");
                        swal({
                            "timer": 3000,
                            "title": data.success,
                            "showConfirmButton": false,
                            "type": "success"
                        });
			const url = new URL( window.location.href );
			if(url.pathname != '/courier_deliveries') {
                          location.reload();
			}
                    } else if (data.package) {
                        ion.sound.play("find_it");
			const url = new URL( window.location.href );
			if(url.pathname != '/courier_deliveries') {
                          setTimeout(
                            function () {
                                window.location = data.package;
                            }, 3000);
			}
                    }
                }

            });
        </script>
    @endif

    <script src="{{ asset('admin/js/jquery.pos.js') }}"></script>
    @if(!Request::is('container/check/*'))
    <script src="{{ asset('admin/js/scanner.js') }}?v=1.2.1.79"></script>
    @endif
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="{{ asset('admin/js/types.js') }}?v=1.0.1.4"></script>
@endif


</body>
</html>
