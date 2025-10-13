
<div>
    {{ $entry }}
    <a href="https://parcelsapp.com/en/tracking/{{$entry}}" target="_blank">
        <i class="icon-arrow-right14"></i>
    </a>
</div>
@php
    use App\Models\Package;
    use App\Models\UkrExpressModel;

    $package = Package::where('tracking_code', $entry)->first();
    $photos = [];

    try {
        if ($package && $package->ukr_express_id && $package->user && $package->user->ukr_express_id) {
            $ue = new UkrExpressModel();
            $photos = $ue->track_get_photos($package->ukr_express_id, $package->user->ukr_express_id);
        }
    } catch (\Exception $e) {
        $photos = [];
    }

    // Error yoxlaması
    $hasError = false;
    if (is_array($photos)) {
        foreach ($photos as $p) {
            $value = is_object($p) ? json_encode($p) : $p;
            if (is_string($value) && (str_contains($value, 'Error') || str_contains($value, 'error'))) {
                $hasError = true;
                break;
            }
        }
    }
@endphp

<style>
    .icon-photo {
        width: 24px;
        height: 24px;
        color: #555;
    }
</style>

@if(!empty($photos) && !$hasError)
    <div class="photo-thumbnails">
        @foreach($photos as $photo)
            <a href="{{ is_object($photo) ? $photo->url : $photo }}" target="_blank" class="photo-thumbnail" title="{{ is_object($photo) ? $photo->description : '' }}">
                <svg class="icon-photo" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M10.5 1a1 1 0 0 1 .8.4l.943 1.257H14a1 1 0 0 1 1 1V13a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V3.657a1 1 0 0 1 1-1h1.757L4.7 1.4A1 1 0 0 1 5.5 1h5zM8 12a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm0-1.5A1.5 1.5 0 1 1 8 8a1.5 1.5 0 0 1 0 3.5z"/>
                </svg>
            </a>
        @endforeach
    </div>
@else

@endif

