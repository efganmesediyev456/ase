<!-- bootstrap daterange picker input -->

<?php
// if the column has been cast to Carbon or Date (using attribute casting)
// get the value as a date string
if (isset($item->{$field['name']}) && ($item->{$field['name']} instanceof \Carbon\Carbon || $item->{$field['name']} instanceof \Jenssegers\Date\Date)) {
    $item->{$field['name']} = $item->{$field['name']}->format('Y-m-d H:i:s');
}

//Do the same as the above but for the range end field
if (isset($entry) && ($entry->{$field['end_name']} instanceof \Carbon\Carbon || $entry->{$field['end_name']} instanceof \Jenssegers\Date\Date)) {
    $end_name = $entry->{$field['end_name']}->format('Y-m-d H:i:s');
} else {
    $end_name = null;
}
?>

<div @include('crud::inc.field_wrapper_attributes') >
    <input class="datepicker-range-start" type="hidden" name="{{ $field['start_name'] }}"
           value="{{ Request::has($field['start_name']) ? Request::get($field['start_name']) : (old($field['start_name']) ? old($field['start_name']) : (isset($item->{$field['name']}) ? $item->{$field['name']} : (isset($field['start_default']) ? $field['start_default'] : '' ))) }}">
    <input class="datepicker-range-end" type="hidden" name="{{ $field['end_name'] }}"
           value="{{ Request::has($field['end_name']) ? Request::get($field['end_name']) : (old($field['end_name']) ? old($field['end_name']) : (!empty($end_name) ? $end_name : (isset($field['end_default']) ? $field['end_default'] : '' ))) }}">

    @if (isset($field['label']))
        <label>{!! $field['label'] !!}</label>
    @endif

    <div class="input-group date">
        <button type="button" class="btn btn-danger filter-daterange-ranges" @include('crud::inc.field_attributes')>
            <i class="icon-calendar22 position-left"></i> <span></span> <b class="caret"></b>
        </button>
    </div>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>