@if($item->not_inserted)
    {{ Form::open(['class' => 'sure-that no-padding', 'route' => [$crud['route'] . '.insert', $item->id], 'method' => 'post', 'data-button' => 'Yes insert the parcel to AseLogic']) }}
    <button class="{{ (isset($class) ? $class : '') . "dropdown-item" }}" data-spinner-color="#fff"
            data-style="slide-left" type="submit">
        <i class="icon-upload"></i> AseLogic
    </button>
    {{ Form::close() }}
@endif