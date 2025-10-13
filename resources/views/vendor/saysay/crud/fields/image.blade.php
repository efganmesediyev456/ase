<div @include('crud::inc.field_wrapper_attributes') data-preview="#{{ $field['name'] }}"
     data-aspectRatio="{{ isset($field['aspect_ratio']) ? $field['aspect_ratio'] : 0 }}"
     data-crop="{{ isset($field['crop']) ? $field['crop'] : false }}" @include('crud::inc.field_wrapper_attributes')>
    <div>
        @if (isset($field['label']))        <label>{!! $field['label'] !!}</label>    @endif
        @include('crud::inc.field_translatable_icon')
        <input name="{{ $field['name'] }}" type="file" class="file-input" data-show-caption="false"
               data-show-upload="false" accept="image/*">
    </div>

    @if(isset($item))
        <div class="row">
            <div class="col-lg-12 mt-20">
                <?php $imageUrl = isset($field['asset']) ? asset($field['asset'] . $item->{$field['name']}) : $item->{$field['name']}; ?>
                @if($item->{$field['name']})
                    <a target="_blank" href="{{ $imageUrl }}">
                        <img src="{{ $imageUrl }}" id="{{ $field['id'] or null }}"
                             width="{{ $field['width'] or '200' }}">
                    </a>
                @endif
            </div>
        </div>
    @endif

    @include('crud::inc.error_or_hint')
</div>
