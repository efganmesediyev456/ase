@extends(config('saysay.crud.layout'))

@section('title', 'Check Container')

@section('content')

    @if(session('status') == 'error')
        <audio id="successPlayer" autoplay>
            <source src="{{ url('/sounds/error.wav') }}" type="audio/wav">
            Your browser does not support the audio element.
        </audio>
    @endif
    @if(session('status') == 'success')
        <audio id="successPlayer" autoplay>
            <source src="{{ url('/sounds/success.mp3') }}" type="audio/mpeg">
            Your browser does not support the audio element.
        </audio>
    @endif
    <div class="card">
        <div class="card-body">
            <div class="card box-primary">
                <div class="col-md-6">
                    <h3 style="margin-bottom: 0px">New Container:</h3>

                </div>
                <div class="card-header">
                    @if ($errors->any())
                        @foreach ($errors->all() as $err)
                            <div class="alert alert-danger">{{ $err }}</div>
                        @endforeach
                    @endif

                    @if (session('message'))
                        <div class="alert alert-{{ session('status') == 'error' ? 'danger' : 'success' }}">
                            {{ session('message') }}
                        </div>
                    @endif
                    <form method="POST" action="{{ route('containers.store') }}" class="form-prevent-multiple-submits">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="form-group mb-3">
                            <label for="container_name">Container Name</label>
                            <input type="text" name="container_name" id="container_name" class="form-control" autofocus
                                   value="{{ old('container_name') }}">
                        </div>

                        <div class="form-group mb-3">
                            <label for="airbox_name">Airbox Name</label>
                            <input type="text" name="airbox_name" id="airbox_name" class="form-control" autofocus
                                   value="{{ old('airbox_name') }}">
                        </div>

                        <div class="form-group mb-3">
                            <label for="airbox_name">Tracks</label>
                            <select name="tracks[]" id="" class="select2" multiple>
                            </select>
                        </div>

                        <div class="form-group">
                            <button class="btn btn-primary legitRipple" name="button" type="submit" id="">Create
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

@endsection


@push("js")
    <script>
        $(document).ready(function () {
            $('.select2').select2({
                placeholder: "Track seçin",
                allowClear: true,
                ajax: {
                    url: "/containers/search",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term // search term
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data.results
                        };
                    },
                    cache: true
                }
            });
        });
    </script>
@endpush
