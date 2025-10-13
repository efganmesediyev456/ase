<!-- Main sidebar -->
<div class="sidebar sidebar-main">
    <div class="sidebar-content">

        <!-- User menu -->
        <div class="sidebar-user">
            <div class="category-content">
                <div class="media">
                    <a href="#" class="media-left"><img src="{{ $_avatar }}" class="img-circle img-sm" alt=""></a>
                    <div class="media-body">
                        <span class="media-heading text-semibold">{{ $_name }}</span>
                        <div class="text-size-mini text-muted">
                            <i class="icon-user-tie text-size-small"></i> &nbsp; {{ $_panelName }}
                        </div>
                    </div>

                    
                    <div class="media-right media-middle dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="icon-cog3"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-right">
                            @if ($_profileUrl)
                            <li>
                                <a href="{{ $_profileUrl }}">
                                    <i class="icon-cog5"></i> Account settings
                                </a>
                            </li>
                            @endif
                            <li>
                                @include('vendor.saysay.base.elements.logout')
                            </li>
                        </ul>
                       
                    </div>

                    
                </div>
            </div>
        </div>
        <!-- /user menu -->

        <!-- Main navigation -->
        @if($_viewDir)
        <div class="sidebar-category sidebar-category-visible">
            <div class="category-content no-padding">
                @include($_viewDir . '.base.sidebar')
            </div>
        </div>
	@endif
        <!-- /main navigation -->

    </div>
</div>
<!-- /main sidebar -->
