<!-- select3 -->
<div @include('crud::inc.field_wrapper_attributes') >
    @if (isset($field['label']))        <label>{!! $field['label'] !!}</label>    @endif
    @include('crud::inc.field_translatable_icon')
    <?php $parent_id=0; ?>
    <select
            name="{{ $field['name_parent'] }}"
            @include('crud::inc.field_attributes', ['default_class' =>  'form-control select2 select-search firstselect'])
    >

        @if (isset($field['allowNull']) and $field['allowNull'])
            <option value="">{{ is_string($field['allowNull']) ? $field['allowNull'] : '-' }}</option>
        @endif

        @if (isset($field['model']))
            @foreach ($field['model']::where('parent_id',0)->get() as $connected_entity_entry)
                <option value="{{ $connected_entity_entry->getKey() }}"
                        @if ( (  ! is_null(Request::get($field['name_parent'])) && Request::get($field['name_parent']) == $connected_entity_entry->getKey() ) || ( old($field['name_parent']) && old($field['name_parent']) == $connected_entity_entry->getKey() ) || (isset($item->{$field['name_parent']}) && $connected_entity_entry->getKey() == $item->{$field['name_parent']}))
                        selected
			<?php $parent_id=$connected_entity_entry->getKey(); ?>
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
    @include('crud::inc.field_translatable_icon')
    <select
            name="{{ $field['name_child'] }}"
	    data-attributes="{{$field['attribute'] }}"
            @include('crud::inc.field_attributes', ['default_class' =>  'form-control select2 select-search secondselect'])
    >

        @if (isset($field['allowNull']) and $field['allowNull'])
            <option value="">{{ is_string($field['allowNull']) ? $field['allowNull'] : '-' }}</option>
        @endif

        @if (isset($field['model']))
            @foreach ($field['model']::where('parent_id','!=',0)->where('parent_id',$parent_id)->get() as $connected_entity_entry)
                <option value="{{ $connected_entity_entry->getKey() }}"
                        @if ( (  ! is_null(Request::get($field['name_child'])) && Request::get($field['name_child']) == $connected_entity_entry->getKey() ) || ( old($field['name_child']) && old($field['name_child']) == $connected_entity_entry->getKey() ) || (isset($item->{$field['name_child']}) && $connected_entity_entry->getKey() == $item->{$field['name_child']}))
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
<script>
  var categories = JSON.parse({!!json_encode(  $field['model']::where('parent_id','!=',0)->get()->toJSon()  ) !!});
</script>
