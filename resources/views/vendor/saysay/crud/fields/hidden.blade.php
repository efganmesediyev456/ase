<!-- hidden input -->
<input
        type="hidden"
        name="{{ $field['name'] }}"
        value="{{ old($field['name']) ? old($field['name']) : (isset($item->{$field['name']}) ? $item->{$field['name']} : (isset($field['default']) ? $field['default'] : '' )) }}">