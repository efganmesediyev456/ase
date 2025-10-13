@if(count($entry->photos))
    <div class="photo-thumbnails">
        @foreach($entry->photos as $photo)
            <a href="{{ $photo->url }}" target="_blank" class="photo-thumbnail">
                <i class="icon-camera"></i>
            </a>
        @endforeach
    </div>
@else
    -
@endif