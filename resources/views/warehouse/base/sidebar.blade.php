<link href="{{ asset('admin/css/base.css') }}?v=1.0.0.1" rel="stylesheet">
<ul class="navigation navigation-main navigation-accordion">

    <!-- Main -->
    <li class="navigation-header"><span>Main</span> <i class="icon-menu" title="Main pages"></i></li>
    @if($_logged->package_processing)
            <li {!! classActiveRoute('w-process') !!}><a href="{{ route('w-process') }}"><i class="icon-codepen"></i> <span>Package Processing</span></a></li>
    @endif
    @if($_logged->parcelling)
            <li {!! classActiveRoute('my.dashboard') !!}><a href="{{ route('my.dashboard') }}"><i class="icon-barcode2"></i> <span>Parcel Processing</span></a></li>
    @endif
    <li>
        <a href="#"><i class="icon-package"></i> <span>Packages</span></a>
        <ul>
            <li {!! classActiveRoute('w-packages.index') !!}><a href="{{ route('w-packages.index') }}"><i class="icon-package"></i> <span>All packages</span></a></li>

            <li {!! classActiveRoute('w-processed') !!}><a href="{{ route('w-processed', 'sent') }}"><i class="icon-airplane3"></i> <span>Sent</span></a></li>
            <li {!! classActiveRoute('w-processed') !!}><a href="{{ route('w-processed') }}"><i class="icon-checkbox-checked"></i> <span>Delivered</span></a></li>
	    @if (auth()->guard('worker')->user()->warehouse->id == 12)
                <li {!! classActiveRoute('w-newtypes.index') !!}><a href="{{ route('w-newtypes.index') }}"><i class="icon-list"></i> <span>Types</span></a></li>
	    @endif
        </ul>
    </li>
</ul>
