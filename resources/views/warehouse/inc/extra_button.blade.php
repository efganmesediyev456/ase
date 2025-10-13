@if(! $item->sent)
    {{ Form::open(['class' => 'sure-that no-padding', 'route' => [$crud['route'] . '.sent', $item->id], 'method' => 'post', 'data-button' => 'Yes sent the parcel']) }}
    <button class="{{ (isset($class) ? $class : '') . "dropdown-item" }}" data-spinner-color="#fff"
            data-style="slide-left" type="submit">
        <i class="icon-airplane2"></i> Sent
    </button>
    {{ Form::close() }}
@endif
