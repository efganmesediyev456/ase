<?php
$field['prefix'] = isset($field['prefix']) ? $field['prefix'] : '<i class="icon-envelop5"></i>';
?>
@include('crud::fields.text', ['type' => 'email'])