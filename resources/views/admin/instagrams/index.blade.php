@extends(config('saysay.crud.layout'))

@section('title', 'Instagram postları')

@section('content')

    <div class="card">
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade in" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Bağla">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    {{ session('success') }}
                </div>
            @endif

            <div class="row mb-3">
                <div class="col-xs-12">
                    <div class="box box-primary box-filter">
                        <div class="box-body">
                            <div class="form-group">
                                <p>Ümumi say: <b>{{ $posts->total() }}</b></p>
                                <a href="{{ route('instagrams.create') }}" class="btn btn-success">+ Yeni əlavə et</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <div class="box box-primary box-filter">
                        <div class="box-body">
                            <table class="table table-bordered table-striped">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Şəkil</th>
                                    <th>Url</th>
                                    <th>Tarix</th>
                                    <th>Əməliyyat</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($posts as $post)
                                    <tr>
                                        <td>{{ $post->id }}</td>
                                        <td>
                                            @if($post->image)
                                                <img src="{{ asset($post->image) }}" alt="Instagram Image" width="120">
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ $post->url }}" target="_blank">{{ $post->url }}</a>
                                        </td>
                                        <td>{{ $post->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('instagrams.edit', $post->id) }}" class="btn btn-primary btn-sm">Redaktə</a>
                                            <form action="{{ route('instagrams.destroy', $post->id) }}"
                                                  method="POST"
                                                  style="display:inline-block;"
                                                  onsubmit="return confirm('Silmək istədiyinizə əminsiniz?')">
                                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                <input type="hidden" name="_method" value="DELETE">
                                                <button type="submit" class="btn btn-danger btn-sm">Sil</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Post tapılmadı</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($posts->hasPages())
                            <div class="box-footer">
                                {{ $posts->appends(request()->all())->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection
