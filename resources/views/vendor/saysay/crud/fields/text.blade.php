<!-- text input -->
<div @include('crud::inc.field_wrapper_attributes') >
    @if (isset($field['label']))
        @if (isset($field['label']))        <label>{!! $field['label'] !!}</label>    @endif
    @endif
    @include('crud::inc.field_translatable_icon')

    @if(isset($field['prefix']) || isset($field['suffix']))
        <div class="input-group"> @endif
            @if(isset($field['prefix']))
                <div class="input-group-addon">{!! $field['prefix'] !!}</div> @endif
            <input
                    type="{{ $type or 'text' }}"
                    name="{{ $field['name'] }}"
                    @if(! isset($hideValue))
                    value="{{ Request::has($field['name']) ? Request::get($field['name']) : (old($field['name']) ? old($field['name']) : (isset($item->{$field['name']}) ? $item->{$field['name']} : (isset($field['default']) ? $field['default'] : '' ))) }}"
                    @endif
                    @include('crud::inc.field_attributes')
            >
            @if(isset($field['suffix']))
                <div class="input-group-addon">{!! $field['suffix'] !!}</div> @endif
            @if(isset($field['prefix']) || isset($field['suffix'])) </div> @endif

    @include('crud::inc.error_or_hint')
</div>