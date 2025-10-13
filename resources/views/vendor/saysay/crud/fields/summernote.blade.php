<!-- summernote editor -->
<div @include('crud::inc.field_wrapper_attributes') >
    @if (isset($field['label']))        <label>{!! $field['label'] !!}</label>    @endif
    @include('crud::inc.field_translatable_icon')
    <textarea
            name="{{ $field['name'] }}"
            @include('crud::inc.field_attributes', ['default_class' =>  'summernote note-editor'])
    >{{ old($field['name']) ? old($field['name']) : (isset($item->{$field['name']}) ? $item->{$field['name']} : (isset($field['default']) ? $field['default'] : '' )) }}</textarea>

    @include('crud::inc.error_or_hint')
</div>