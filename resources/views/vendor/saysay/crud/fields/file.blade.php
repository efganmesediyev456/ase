<div @include('crud::inc.field_wrapper_attributes') data-preview="#{{ $field['name'] }}"
     data-aspectRatio="{{ isset($field['aspect_ratio']) ? $field['aspect_ratio'] : 0 }}"
     data-crop="{{ isset($field['crop']) ? $field['crop'] : false }}" @include('crud::inc.field_wrapper_attributes')>
    <div>
        @if (isset($field['label']))        <label>{!! $field['label'] !!}</label>    @endif
        @include('crud::inc.field_translatable_icon')
        <input name="{{ $field['name'] }}" type="file" class="file-input" data-show-caption="false"
               data-show-upload="false" accept="{{ $field['accept'] or '*' }}">
    </div>

    @if(isset($item) && $item->{$field['name']})
        <div class="row">
            <div class="col-lg-12" style="margin-top: 20px;">
                <?php $pathInfo = pathinfo($item->{$field['name']});?>

                @if($item->{$field['name']})
                    <a target="_blank" href="{{ $item->{$field['name']} }}">Click to see the file</a>
                    <br/>
                    <a target="_blank" href="{{ $item->{$field['name']} }}">
			@if (array_key_exists('extension',$pathInfo))
                        @if (in_array($pathInfo['extension'], ['png', 'jpeg', 'jpg', 'gif', 'svg']))
                            <img src="{{ $item->{$field['name']} }}" class="{{ $head['class'] or null }}"
                                 id="{{ $head['id'] or null }}"
                                 width="{{ $head['width'] or '150' }}">
                        @else
                            <embed src="{{ $item->{$field['name']} }}" class="{{ $head['class'] or null }}"
                                   width="{{ $head['width'] or '150' }}" height="{{ $head['height'] or '200' }}"
                                   type='application/{{ $pathInfo['extension'] }}'>
                            </embed>

                        @endif
                        @endif
                    </a>
                @endif
            </div>
        </div>
    @endif

    @include('crud::inc.error_or_hint')
</div>
