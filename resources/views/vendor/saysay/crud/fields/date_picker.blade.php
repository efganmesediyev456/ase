<!-- bootstrap datepicker input -->

<?php
if (isset($item->{$field['name']}) && ($item->{$field['name']} instanceof \Carbon\Carbon || $item->{$field['name']} instanceof \Jenssegers\Date\Date)) {
    $item->{$field['name']} = $item->{$field['name']}->format('Y-m-d');
}

$field_language = isset($field['date_picker_options']['language']) ? $field['date_picker_options']['language'] : \App::getLocale();
?>

<div @include('crud::inc.field_wrapper_attributes') >
    @if (isset($field['label']))
        <label>{!! $field['label'] !!}</label>
    @endif

    @include('crud::inc.field_translatable_icon')

    <div class="input-group ">
        <input
                type="text"
                id="pickadate"
                data-name="{{ $field['name'] }}"
                data-value="{{ old($field['name']) ? old($field['name']) : (isset($item->{$field['name']}) ? $item->{$field['name']} : (isset($field['default']) ? $field['default'] : '' )) }}"
                @include('crud::inc.field_attributes')
        >
        <div class="input-group-addon">
            <span class="glyphicon glyphicon-calendar"></span>
        </div>
    </div>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>
