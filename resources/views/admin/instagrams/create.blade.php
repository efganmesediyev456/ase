@extends(config('saysay.crud.layout'))

@section('title', 'Instagram postu əlavə et')

@section('content')

    <div class="card">
        <div class="card-body">
            <form action="{{ route('instagrams.store') }}" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                <div class="form-group">
                    <label for="image">Şəkil</label>
                    <input type="file" class="form-control" id="image" name="image">
                    @if($errors->first('image'))
                        <small class="form-text text-danger">{{ $errors->first('image') }}</small>
                    @endif
                </div>

                <div class="form-group">
                    <label for="url">Url</label>
                    <input type="text" class="form-control" id="url" name="url" value="{{ old('url') }}">
                    @if($errors->first('url'))
                        <small class="form-text text-danger">{{ $errors->first('url') }}</small>
                    @endif
                </div>

                <button type="submit" class="btn btn-primary">Yadda saxla</button>
                <a href="{{ route('instagrams.index') }}" class="btn btn-secondary">Geri</a>
            </form>
        </div>
    </div>

@endsection
