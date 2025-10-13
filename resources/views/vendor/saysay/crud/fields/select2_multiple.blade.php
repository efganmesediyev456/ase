<!-- select2 -->
<div @include('crud::inc.field_wrapper_attributes') >
    @if (isset($field['label']))        <label>{!! $field['label'] !!}</label>    @endif
    @include('crud::inc.field_translatable_icon')
    <select
            name="{{ $field['name'] }}[]"
            @include('crud::inc.field_attributes', ['default_class' =>  'form-control select2 select-search select2-hidden-accessible'])
            multiple>

        @if (isset($field['allowNull']) and $field['allowNull'])
            <option value="">{{ is_string($field['allowNull']) ? $field['allowNull'] : '-' }}</option>
        @endif

        @if (isset($field['model']))
            @foreach ($field['model']::all() as $connected_entity_entry)
                <option value="{{ $connected_entity_entry->getKey() }}"
                        @if ((  ! is_null(request()->get($field['name'])) && is_array(request()->get($field['name'])) && in_array($connected_entity_entry->getKey() , request()->get($field['name']))) ||  (isset($item->{$field['name']}) && in_array($connected_entity_entry->getKey(), $item->{$field['name']}->pluck($connected_entity_entry->getKeyName(), $connected_entity_entry->getKeyName())->toArray())) || ( old( $field["name"] ) && in_array($connected_entity_entry->getKey(), old( $field["name"])) ) )
                        selected
                        @endif
                >
                    <?php
                    $attributes = explode(",", $field['attribute']);
                    ?>
                    @foreach($attributes as $attribute)
                        <?php $entry = parseRelation($connected_entity_entry, $attribute);?>
                        {{ $entry }}
                        @if(! $loop->last)
                            {{ " - " }}
                        @endif
                    @endforeach

                </option>
            @endforeach
        @endif
    </select>

    @include('crud::inc.error_or_hint')
</div>