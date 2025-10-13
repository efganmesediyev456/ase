<?php
$field['prefix'] = isset($field['prefix']) ? $field['prefix'] : '<i class="icon-link2"></i>';
?>
@include('crud::fields.text', ['type' => 'url'])