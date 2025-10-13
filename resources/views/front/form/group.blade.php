<?php
$type = $type ?? 'text';

$value = !array_key_exists('notOld', $options)
    ? (old($key) ?: (\Request::has($key) ? \Request::get($key) : (isset($item) ? (array_key_exists('relation', $options) ? $item->{$key}->{$options['relation']} : ($item->{$key} ?? null)) : (array_key_exists('value', $options) ? $options['value'] : null))))
    : null;

switch ($type) {
    case 'text':
        $field = Form::text($key, $value, $options);
        break;
    case 'textarea':
        $field = Form::textarea($key, $value, $options);
        break;
    case 'password':
        $field = Form::password($key, $options);
        break;
    case 'datetime':
        $field = Form::input('dateTime-local', $key, $value, $options);
        break;
    case 'file':
        $field = '<div><div style="margin:5px;">' . Form::file($key, ['class' => "file-input", 'data-show-caption' => "false", 'data-show-upload' => "false", 'accept' => '.jpg, .jpeg, .pdf, .gif, .png']) . '</div><div style="margin:5px"><img src="' . $item->{$key} . '"></div></div>';
        break;
    case 'select':
        if (is_object($selects) && !$selects->count()) {
            $selects = [0 => trans('front.not')];
        } else {
            if (isset($options['empty1'])) {
                $selects = ['' => trans('front.not_choose')] + $selects;
            }
            if (isset($options['empty'])) {
                $selects = [0 => trans('front.not_choose')] + $selects;
            }
        }
        $options['data-hiddenvalue'] = $value;
        $field = Form::select($key, $selects, $value, $options);
        break;
    case 'multi-select':
        $field = '<select class="select2_multiple ' . ($options['class'] ?? '') . '" name="' . $key . '[]" multiple="multiple" ' . (array_key_exists('size', $options) ? 'size="' . $options['size'] . '"' : '') . '>';
        $mainCategories = old($key) ?: (isset($item) ? $item->{$key}->pluck('id')->all() : null);
        if (array_key_exists('value', $options) && $options['value'] && is_array($options['value'])) {
            $mainCategories = array_keys($options['value']);
        }
        if (isset($options['selected_id']) && $options['selected_id']) {
            if (!$mainCategories || !is_array($mainCategories)) {
                $mainCategories = [$options['selected_id']];
            } else {
                $mainCategories[] = $options['selected_id'];
            }
        }
        foreach ($selects as $id => $select) {
            $selected = ($mainCategories && in_array($id, $mainCategories)) ? 'selected' : '';
            $field .= '<option ' . $selected . ' value="' . $id . '">' . $select . '</option>';
        }
        $field .= '</select>';
        break;
    case 'select3':
        $parent_id = 0;
        $field = '<select id="firstselect" class="firstselect select2 ' . ($options['class'] ?? '') . '" name="' . $key_parent . '"' . ($options['data-validation'] ?? '') . '>';
        $field .= '<option value="">-</option>';
        foreach ($selects as $select) {
            if ($select['parent_id'] > 0) continue;
            $selected = '';
            if (isset($item) && $item->{$key_parent} == $select['id']) {
                $parent_id = $select['id'];
                $selected = 'selected';
            }
            $field .= '<option ' . $selected . ' value="' . $select['id'] . '">' . $select['name_' . \App::getLocale()] . '</option>';
        }
        $field .= '</select>';
        $field .= '<select id="secondselect" class="secondselect select2 ' . ($options['class'] ?? '') . '" name="' . $key . '"' . ($options['data-validation'] ?? '') . '>';
        $field .= '<option value="">-</option>';
        foreach ($selects as $select) {
            if ($select['parent_id'] <= 0 || $select['parent_id'] != $parent_id) continue;
            $selected = (isset($item) && $item->{$key} == $select['id']) ? 'selected' : '';
            $field .= '<option ' . $selected . ' value="' . $select['id'] . '">' . $select['name_' . \App::getLocale()] . '</option>';
        }
        $field .= '</select>';
        break;
    case 'radio':
        $field = '<br/>';
        foreach ($values as $value => $title) {
            $field .= '&nbsp' . $title . '&nbsp:&nbsp' . Form::radio($key, $value, (old($key) && old($key) == $value) ? true : (isset($item->{$key}) && $item->{$key} == $value), ['class' => 'flast', 'id' => $key . '_' . $value]);
        }
        break;
    case 'checkbox':
        $field = '<br/>';
        $field .= Form::checkbox($key, $value, (old($key) && old($key) == $value) ? true : (isset($item->{$key}) && $item->{$key} == $value), ['class' => 'flatd', 'id' => $key]) . '&nbsp&nbsp' . $title;
        break;
    case 'date-picker':
        $field = '<div id="reportrange_right" class="pull-left" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc">
                      <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                      <span>December 30, 2014 - January 28, 2015</span> <b class="caret"></b>
                  </div>';
        break;
    case 'numeric':
        $field = '<button data-type="-" class="btn btn-round btn-warning btn-sm"><i class="fa fa-minus"></i></button>';
        $field .= Form::text($key, $value, $options);
        $field .= '<button data-type="+" class="btn btn-round btn-success btn-sm"><i class="fa fa-plus"></i></button>
                  <a style="position: relative;left: 10px;top: 5px;" data-toggle="tooltip" data-placement="top" title="' . $label . '">
                      <span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
                  </a>';
        break;
}

$errorKey = str_replace(']', '', str_replace('[', '.', $key));
?>
<div class="form-group {{ $errors->has($errorKey) ? ' has-error' : '' }}">
    @if(isset($label))
        {!! Form::label($key, $label) !!}
    @endif
    {!! $field !!}
    @if ($errors->has($errorKey))
        <span class="help-block">
            <strong>{!! $errors->first($errorKey) !!}</strong>
        </span>
    @endif
</div>
