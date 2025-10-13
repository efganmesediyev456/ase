@if(!$item->departed && !$item->sent)
    {{ Form::open(['class' => 'sure-that no-padding', 'route' => [$crud['route'] . '.departed', $item->id], 'method' => 'post', 'data-button' => 'Yes departed the parcel']) }}
    <button class="{{ (isset($class) ? $class : '') . "dropdown-item" }}" data-spinner-color="#fff"
            data-style="slide-left" type="submit">
        <i class="icon-truck"></i> Departed
    </button>
    {{ Form::close() }}
@endif
