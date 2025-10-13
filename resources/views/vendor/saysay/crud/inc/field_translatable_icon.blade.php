@if (isset($crud) && $crud['translatable'] && in_array($field['name'], $crud['translatable']) && config('saysay.crud.show_translatable_field_icon'))
    <span class="input-group-addon pull-{{ config('saysay.crud.translatable_field_icon_position') }}"><i class="icon-flag3"></i></span>
@endif