<span id="filial-name-{{ $item->id }}">{{ $item->filial_name }}</span>
{{--@php--}}
{{--dd($item->filial_name)--}}
{{-- @endphp--}}
<br>
@include('crud::components.columns.select-editable')
