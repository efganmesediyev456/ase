@if (isset($field['name']) && $errors->has($field['name']))
    <span class="help-block">
        <strong>{!! $errors->first($field['name']) !!}</strong>
    </span>
@elseif (isset($field['hint']))
    <p class="help-block">{!! $field['hint'] !!}</p>
@endif