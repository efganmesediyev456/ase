<div class="form-group col-md-12 image" data-preview="#{{ $field['name'] }}"
     data-aspectRatio="{{ isset($field['aspect_ratio']) ? $field['aspect_ratio'] : 0 }}"
     data-crop="{{ isset($field['crop']) ? $field['crop'] : false }}" @include('crud::inc.field_wrapper_attributes')>
    <div>
        @if (isset($field['label']))        <label>{!! $field['label'] !!}</label>    @endif
        @include('crud::inc.field_translatable_icon')
    </div>
    <!-- Wrap the image or canvas element with a block element (container) -->
    <div class="row">
        <div class="col-sm-6" style="margin-bottom: 20px;">
            <img id="mainImage"
                 src="{{ url(old($field['name']) ? old($field['name']) : (isset($item->{$field['name']}) ? $item->{$field['name']} : (isset($field['default']) ? $field['default'] : '') )) }}">
        </div>
        @if(isset($field['crop']) && $field['crop'])
            <div class="col-sm-3">
                <div class="docs-preview clearfix">
                    <div id="{{ $field['name'] }}" class="img-preview preview-lg">
                        <img src=""
                             style="display: block; min-width: 0px !important; min-height: 0px !important; max-width: none !important; max-height: none !important; margin-left: -32.875px; margin-top: -18.4922px; transform: none;">
                    </div>
                </div>
            </div>
        @endif
    </div>
    <div class="btn-group">
        <label class="btn btn-primary btn-file">
            {{ trans('saysay::crud.choose_file') }} <input type="file" accept="image/*"
                                                           id="uploadImage" @include('crud::inc.field_attributes', ['default_class' => 'hide'])>
            <input type="hidden" id="hiddenImage" name="{{ $field['name'] }}">
        </label>
        @if(isset($field['crop']) && $field['crop'])
            <button class="btn btn-default" id="rotateLeft" type="button" style="display: none;"><i
                        class="fa fa-rotate-left"></i></button>
            <button class="btn btn-default" id="rotateRight" type="button" style="display: none;"><i
                        class="fa fa-rotate-right"></i></button>
            <button class="btn btn-default" id="zoomIn" type="button" style="display: none;"><i
                        class="fa fa-search-plus"></i></button>
            <button class="btn btn-default" id="zoomOut" type="button" style="display: none;"><i
                        class="fa fa-search-minus"></i></button>
            <button class="btn btn-warning" id="reset" type="button" style="display: none;"><i class="fa fa-times"></i>
            </button>
        @endif
        <button class="btn btn-danger" id="remove" type="button"><i class="fa fa-trash"></i></button>
    </div>

    @include('crud::inc.error_or_hint')
</div>
