<div @include('crud::inc.field_wrapper_attributes') >
    @if (isset($field['label']))
        <label>{!! $field['label'] !!}</label>
    @endif
    @include('crud::inc.field_translatable_icon')

    <select
            name="{{ $field['name'] }}"
            @include('crud::inc.field_attributes', ['default_class' =>  'form-control select2 select-search'])
            id="select2_{{ $field['name'] }}"
    >
        @if (isset($field['allowNull']) and $field['allowNull'])
            <option value="">{{ is_string($field['allowNull']) ? $field['allowNull'] : '-' }}</option>
        @endif

        @if (isset($field['model']))
            @php
                $entries = $field['model']::all();
                $grouped = $entries->groupBy('type');
            @endphp

            @foreach ($grouped as $type => $items)
                <option value="group_{{ $type }}"
                        @if(old($field['name']) == "group_$type"
                            || Request::get($field['name']) == "group_$type"
                            || (isset($item->{$field['name']}) && $item->{$field['name']} == "group_$type"))
                            selected
                        @endif
                >
                    {{ $type }}
                </option>
            @endforeach


            @foreach ($grouped as $type => $items)

                {{-- TYPE altındakı itemlər --}}
                @foreach ($items as $connected_entity_entry)
                    <option value="{{ $connected_entity_entry->getKey() }}"
                            @if ((! is_null(Request::get($field['name'])) && Request::get($field['name']) == $connected_entity_entry->getKey())
                                || (old($field['name']) && old($field['name']) == $connected_entity_entry->getKey())
                                || (isset($item->{$field['name']}) && $connected_entity_entry->getKey() == $item->{$field['name']}))
                                selected
                            @endif
                    >
                        @php
                            $attributes = explode(",", $field['attribute']);
                        @endphp
                        @foreach($attributes as $attribute)
                            @php $entry = parseRelation($connected_entity_entry, $attribute); @endphp
                            {{ $entry }} @if(! $loop->last)
                                -
                            @endif
                        @endforeach
                    </option>
                @endforeach

            @endforeach
        @endif

    </select>

    @include('crud::inc.error_or_hint')
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function () {
        $('#select2_{{ $field['name'] }}').select2();

        $("body").on('click', '.select2-container--open .select2-results__group', function () {
            let $optGroupHeading = $(this);
            $optGroupHeading.siblings().toggle();
            $optGroupHeading.toggleClass('group--open');
        });

        $('#select2_{{ $field['name'] }}').on('select2:open', function () {
            $('.select2-dropdown--below').css('opacity', 0);
            setTimeout(() => {
                let groups = $('.select2-container--open .select2-results__group');
                $.each(groups, (index, v) => {
                    $(v).siblings().hide();
                })
                $('.select2-dropdown--below').css('opacity', 1);
            }, 0);
        });
    });
</script>


<style>
    .select2-results__group {
        cursor: pointer;
        user-select: none;
        font-weight: bold;
        padding: 8px 0 !important;
    }

    .select2-results__group:hover {
        background-color: #f0f0f0 !important;
    }

    .select2-results__group.group--open::after {
        content: " ▲";
    }

    .select2-results__option {
        padding-left: 20px !important;
    }
</style>

