<?php
$field['prefix'] = isset($field['prefix']) ? $field['prefix'] : '<i class="icon-hash"></i>';
?>
@include('crud::fields.text', ['default_class' => 'form-control tokenfield input-sm'])