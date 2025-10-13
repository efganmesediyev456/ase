<div class="page-header page-header-default">
    <div class="page-header-content">
        <div class="page-title">
            <h5>
                <i class="icon-arrow-left52 position-left"></i>
                <span class="text-semibold">{{ isset($_view['name']) ? str_plural($_view['name']) : null }}</span>
                <small class="display-block">{{ $_view['sub_title'] or null }}</small>
            </h5>
        </div>
    </div>

    <div class="breadcrumb-line"><a class="breadcrumb-elements-toggle"><i class="icon-menu-open"></i></a>
        <ul class="breadcrumb">
            <li><a href="index.html"><i class="icon-home2 position-left"></i> Home</a></li>
            <li><a href="#">Current</a></li>
            <li class="active">Location</li>
        </ul>

        <ul class="breadcrumb-elements">
            @if($_can['create'])
                <li>
                    <a href="{{ route($_params['route'] . '.create') }}"
                       class="btn btn-success btn-sm legitRipple">
                        <i class="icon-plus2 position-left"></i>
                        {{ __('saysay::crud.create_button', ['title' => lcfirst($_view['name'])]) }}
                    </a>
                </li>
            @endif
        </ul>
    </div>
</div>