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
                    <h3 style="margin-bottom: 0px">Konteyner adı: {{$result->name ?? ''}}</h3>
                    @if($result->first_check_date)
                        <h3 style="margin-top: 0px">First check date: {{$result->first_check_date }}</h3>
                    @endif
                </div>
                <div class="card-header">
                    @if (session('message'))
                        <div class="alert alert-{{ session('status') == 'error' ? 'danger' : 'success' }}">
                            {{ session('message') }}
                        </div>
                    @endif
                    <form method="POST" action="{{ route('containerCheckPost') }}" class="form-prevent-multiple-submits">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="id" value="{{ $id }}">
                        <input type="hidden" name="type" value="{{ $type }}">
                        <div class="form-group mb-3">
                            <label for="barcode">Barcode</label>
                            <input type="text" name="barcode" id="barcode" class="form-control" autofocus>
                        </div>

                        <div class="form-group">
                            <button class="btn btn-primary legitRipple" name="button" type="submit" id="">Check</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6 no-padding">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Yoxlanılmadı ({{ $parcels->where('check',0)->count() }})</h5>
                        <table class="table table-bordered table-striped">
                            <thead class="thead-dark">
                            <tr>
                                <th>Sifariş kodu</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($parcels->where('check',0) as $parcel)
                            <tr>
                                <td style="background: #ffabab">
{{--                                    {{ substr($parcel->barcode, 0, -4) . '****' }}--}}
                                    {{ $parcel->barcode }}
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6 no-padding">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('containerCheckedExcel') }}" class="form-prevent-multiple-submits">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="id" value="{{ $id }}">
                            <input type="hidden" name="type" value="{{ $type }}">
                            <div class="form-group">
                                <button class="btn btn-success" name="button" type="submit" id="">Excel</button>
                            </div>
                        </form>
                        <h5 class="card-title">Yoxlanıldı ({{ $parcels->where('check',1)->count() }})</h5>
                        <table class="table table-bordered table-striped">
                            <thead class="thead-dark">
                            <tr>
                                <th>Sifariş kodu</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($parcels->where('check',1) as $parcel)
                                <tr>
                                    <td style="background: #afffab">{{ $parcel->barcode }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

