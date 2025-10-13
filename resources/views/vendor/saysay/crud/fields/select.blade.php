<!-- select -->

<div @include('crud::inc.field_wrapper_attributes') >

    @if (isset($field['label']))        <label>{!! $field['label'] !!}</label>    @endif
    @include('crud::inc.field_translatable_icon')

    <?php $entity_model = $crud->model; ?>
    <select
            name="{{ $field['name'] }}"
            @include('crud::inc.field_attributes')
    >

        @if ($entity_model::isColumnNullable($field['name']))
            <option value="">{{ is_string($field['allowNull']) ? $field['allowNull'] : '-' }}</option>
        @endif

        @if (isset($field['model']))
            @foreach ($field['model']::all() as $connected_entity_entry)
                <option value="{{ $connected_entity_entry->getKey() }}"

                        @if ( ( ! is_null(Request::get($field['name'])) && Request::get($field['name']) == $connected_entity_entry->getKey() ) || ( old($field['name']) && old($field['name']) == $connected_entity_entry->getKey() ) || (!old($field['name']) && isset($item->{$field['name']}) && $connected_entity_entry->getKey()==$item->{$field['name']}))

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