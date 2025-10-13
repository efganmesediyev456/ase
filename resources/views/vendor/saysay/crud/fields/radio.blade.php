<!-- radio -->
@php
    $optionPointer = 0;
    $optionValue = old($field['name']) ? old($field['name']) : (isset($item->{$field['name']}) ? $item->{$field['name']} : (isset($field['default']) ? $field['default'] : '' ));
@endphp

<div @include('crud::inc.field_wrapper_attributes') >

    <div>
        @if (isset($field['label']))        <label>{!! $field['label'] !!}</label>    @endif
        @include('crud::inc.field_translatable_icon')
    </div>

    @if( isset($field['options']) && $field['options'] = (array)$field['options'] )

        @foreach ($field['options'] as $value => $label )
            @php ($optionPointer++)

            @if( isset($field['inline']) && $field['inline'] )

                <label class="radio-inline" for="{{$field['name']}}_{{$optionPointer}}">
                    <input type="radio" id="{{$field['name']}}_{{$optionPointer}}" name="{{$field['name']}}"
                           value="{{$value}}" {{$optionValue == $value ? ' checked': ''}}> {!! $label !!}
                </label>

            @else

                <div class="radio">
                    <label for="{{$field['name']}}_{{$optionPointer}}">
                        <input type="radio" id="{{$field['name']}}_{{$optionPointer}}" name="{{$field['name']}}"
                               value="{{$value}}" {{$optionValue == $value ? ' checked': ''}}> {!! $label !!}
                    </label>
                </div>

            @endif

        @endforeach

    @endif

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif

</div>
