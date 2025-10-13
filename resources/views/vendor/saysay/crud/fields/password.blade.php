<?php
$field['prefix'] = isset($field['prefix']) ? $field['prefix'] : '<i class="icon-key"></i>';
?>
@include('crud::fields.text', ['type' => 'password', 'hideValue' => true])