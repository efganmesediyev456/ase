<!DOCTYPE html>

<html>

<head>
    <meta charset="UTF-8">
    <meta name="description" content="Photos of {{ $item->custom_id}} -- {{ $item->tracking_code }}">
    <meta name="author" content="ASE">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UE {{ $item->custom_id}} -- {{ $item->tracking_code }} photos</title>

    <style>
    </style>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css">
</head>

<body>

<main>
    <div class="container">
    @if($track)
             <div class="row">
 	       @foreach ($photos as $photo)
               <div class="col-sm-6">
		   <a href="{{ $photo->url }}" target="_blank" title="{{ $photo->description }}">
                     <img src="{{ $photo->thumb_url }}">
		   </a>
               </div>
	       @endforeach
            </div>
    @endif
</main>
</body>
</html>
