<link href="{{ asset('admin/css/base.css') }}?v=1.0.0.3" rel="stylesheet">
<ul class="navigation navigation-main navigation-accordion">

    <!-- Main -->
    <li class="navigation-header"><span>Main</span> <i class="icon-menu" title="Main pages"></i></li>
    <li {!! classActiveRoute('cd') !!}><a href="{{ route('cd') }}"><i class="icon-package"></i> <span>Courier Deliveries</span></a></li>
</ul>
