<?php $errorClass = isset($field['name']) && $errors->has($field['name']) ? ' has-error' : ''; ?>
@if (isset($field['wrapperAttributes']))
    @foreach ($field['wrapperAttributes'] as $attribute => $value)
    	@if (is_string($attribute) and $attribute != 'class')
        {{ $attribute }}="{{ $value }}"
        @endif
    @endforeach

    @if (!isset($field['wrapperAttributes']['class']))
		class="form-group col-lg-12 {{ $errorClass }}"
    @else
        class="{{ $field['wrapperAttributes']['class'] . $errorClass }}"
    @endif
@else
	class="form-group col-lg-12 {{ $errorClass }}"
@endif