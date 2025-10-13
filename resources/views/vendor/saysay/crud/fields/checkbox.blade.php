<!-- checkbox field -->

<div @include('crud::inc.field_wrapper_attributes') >
    @include('crud::inc.field_translatable_icon')
    <div class="checkbox">
        <label class="checkbox-inline">
            <input type="hidden" name="{{ $field['name'] }}" value="0">
            <input type="checkbox" class="styled" value="1"

                   name="{{ $field['name'] }}"

                   @if (isset($item->{$field['name']}))
                   @if( ((int) $item->{$field['name']} == 1 || old($field['name']) == 1 || request()->get($field['name']) == 1) && old($field['name']) !== '0' )
                   checked="checked"
                   @endif
                   @elseif (isset($field['default']) && $field['default'] && !request()->has($field['name']))
                   checked="checked"
                   @elseif (request()->get($field['name']) == 1)
                   checked="checked"
                   @elseif (old($field['name']) == 1)
                   checked="checked"

            @endif

            @if (isset($field['attributes']))
                @foreach ($field['attributes'] as $attribute => $value)
                    {{ $attribute }}="{{ $value }}"
                @endforeach
            @endif
            > {!! $field['label'] !!}
        </label>

        {{-- HINT --}}
        @if (isset($field['hint']))
            <p class="help-block">{!! $field['hint'] !!}</p>
        @endif
    </div>
</div>
