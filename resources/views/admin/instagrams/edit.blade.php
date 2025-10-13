@extends(config('saysay.crud.layout'))

@section('title', 'Instagram postunu redaktə et')

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('instagrams.update', $instagram->id) }}" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="_method" value="PUT">

                <div class="form-group">
                    <label for="image">Şəkil</label>
                    @if($instagram->image)
                        <div class="mb-2">
                            <img src="{{ asset( $instagram->image) }}" alt="Current image" width="150">
                        </div>
                    @endif
                    <input type="file" class="form-control" id="image" name="image">
                    @if($errors->first('image'))
                        <small class="form-text text-danger">{{ $errors->first('image') }}</small>
                    @endif
                </div>

                <div class="form-group">
                    <label for="url">Url</label>
                    <input type="text" class="form-control" id="url" name="url" value="{{ old('url', $instagram->url) }}">
                    @if($errors->first('url'))
                        <small class="form-text text-danger">{{ $errors->first('url') }}</small>
                    @endif
                </div>

                <button type="submit" class="btn btn-primary">Yenilə</button>
                <a href="{{ route('instagrams.index') }}" class="btn btn-secondary">Geri</a>
            </form>
        </div>
    </div>
@endsection
