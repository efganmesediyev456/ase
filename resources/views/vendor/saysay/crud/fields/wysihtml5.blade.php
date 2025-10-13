<!-- wysihtml5 editor -->
<div @include('crud::inc.field_wrapper_attributes') >
    @if (isset($field['label']))        <label>{!! $field['label'] !!}</label>    @endif
    @include('crud::inc.field_translatable_icon')
    <textarea
            name="{{ $field['name'] }}"
            cols="10" rows="10"
            @include('crud::inc.field_attributes', ['default_class' => 'wysihtml5 wysihtml5-min form-control note-editor'])
    >{{ old($field['name']) ? old($field['name']) : (isset($item->{$field['name']}) ? $item->{$field['name']} : (isset($field['default']) ? $field['default'] : '' )) }}</textarea>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>