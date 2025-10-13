<!-- checkbox field -->

<div @include('crud::inc.field_wrapper_attributes') >
    @include('crud::inc.field_translatable_icon')
    <div class="checkbox">
        <label class="checkbox-inline no-padding-top">
            <input type="hidden" name="{{ $field['name'] }}" value="0">
            <input type="checkbox" class="styled" value="1"

                   name="{{ $field['name'] }}"

                   @if (isset($item->{$field['name']}))
                   @if( ((int) $item->{$field['name']} == 1 || old($field['name']) == 1 || request()->get($field['name']) == 1) && old($field['name']) !== '0' )
                   checked="checked"
                   @endif
                   @elseif (isset($field['default']) && $field['default'])
                   checked="checked"
                   @elseif (request()->get($field['name']) == 1)
                   checked="checked"

            @endif

            @if (isset($field['attributes']))
                @foreach ($field['attributes'] as $attribute => $value)
                    {{ $attribute }}="{{ $value }}"
                @endforeach
            @endif
            > {!! $field['label_pre'] or null !!}
            <input
                    type="{{ $field['input']['name'] or 'text' }}"
                    name="{{ $field['input']['name'] }}"
                    value="{{ Request::has($field['input']['name']) ? Request::get($field['input']['name']) : (old($field['input']['name']) ? old($field['input']['name']) : (isset($item->{$field['input']['name']}) ? $item->{$field['input']['name']} : (isset($field['input']['default']) ? $field['input']['default'] : '' ))) }}"

                    @include('crud::inc.field_attributes', ['field' => $field['input']])
            >
            {!! $field['label_post'] or null !!}
        </label>

        {{-- HINT --}}
        @if (isset($field['hint']))
            <p class="help-block">{!! $field['hint'] !!}</p>
        @endif
    </div>
</div>
