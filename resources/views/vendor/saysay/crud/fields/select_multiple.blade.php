<!-- select multiple -->
<div @include('crud::inc.field_wrapper_attributes') >
    @if (isset($field['label']))        <label>{!! $field['label'] !!}</label>    @endif
    @include('crud::inc.field_translatable_icon')
    <select
            class="form-control"
            name="{{ $field['name'] }}[]"
            @include('crud::inc.field_attributes')
            multiple>

        <option value="">{{ is_string($field['allowNull']) ? $field['allowNull'] : '-' }}</option>

        @if (isset($field['model']))
            @foreach ($field['model']::all() as $connected_entity_entry)
                <option value="{{ $connected_entity_entry->getKey() }}"
                        @if ( (isset($item->{$field['name']}) && in_array($connected_entity_entry->getKey(), $item->{$field['name']}->pluck($connected_entity_entry->getKeyName(), $connected_entity_entry->getKeyName())->toArray())) || ( old( $field["name"] ) && in_array($connected_entity_entry->getKey(), old( $field["name"])) ) )
                        selected
                        @endif
                >{{ $connected_entity_entry->{$field['attribute']} }}</option>
            @endforeach
        @endif

    </select>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>