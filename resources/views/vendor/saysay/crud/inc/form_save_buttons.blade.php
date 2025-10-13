<div class="heading-elements">
    <div class="heading-btn pull-right">
        <button type="submit" class="btn btn-info legitRipple">{{ trans('saysay::crud.save') }}</button>
        <a href="{{ route($crud['route']  . ".index", $crud['routeParams']) }}" class="btn btn-default legitRipple">{{ trans('saysay::crud.cancel') }}</a>
    </div>
</div>
