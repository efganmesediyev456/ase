<!-- textarea -->
<div @include('crud::inc.field_wrapper_attributes') >
    @if (isset($field['label']))        <label>{!! $field['label'] !!}</label>    @endif
    @include('crud::inc.field_translatable_icon')

    @if(isset($field['prefix']) || isset($field['suffix']))
        <div class="input-group"> @endif
            @if(isset($field['prefix']))
                <div class="input-group-addon">{!! $field['prefix'] !!}</div> @endif
            <textarea
                    name="{{ $field['name'] }}"
                    @include('crud::inc.field_attributes')>
                {{ Request::has($field['name']) ? Request::get($field['name']) : (old($field['name']) ? old($field['name']) : (isset($item->{$field['name']}) ? $item->{$field['name']} : (isset($field['default']) ? $field['default'] : null ))) }}            </textarea>

            @if(isset($field['suffix']))
                <div class="input-group-addon">{!! $field['suffix'] !!}</div> @endif
            @if(isset($field['prefix']) || isset($field['suffix'])) </div> @endif

    @include('crud::inc.error_or_hint')
</div>
