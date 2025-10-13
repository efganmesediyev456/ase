@extends(config('saysay.crud.layout'))

@section('title', 'Box Dashboard')

@section('content')

    <div class="card">
        <div class="card-body">

            @include('admin.hub.filter')

            <div class="row">
                <div class="col-xs-12">
                    <div class="box box-primary box-filter">
                        <div class="box-body">
                            <div class="form-group">
                                <p>Bağlama sayı: <b>{{ $total_packages??0 }}</b></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xs-12 clearfix"></div>

                <div class="col-xs-12">
                    <div class="box box-primary box-filter">
                        <div class="box-body">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>#No</th>
                                    <th>Qutu</th>
                                    <th>Tracking</th>
                                    <th>Qutuya əlavə edilmə tarixi</th>
                                    <th>Əməliyyatlar</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($packages as $key => $package)
                                    <tr>
                                        <td>#{{  ++$key }}</td>

                                        <td>
                                            <a href="{{ route('hub.boxes.show', $package->box->id) }}"><b>{{ $package->box->name }}</b></a>
                                        </td>

                                        <td>
                                            @if($package->tracking)
                                                @if($package->parcel_type == 'package')
                                                    <a href="{{route('packages.index', ['q' => $package->tracking])}}"
                                                       class="" target="_blank">
                                                        {{ $package->tracking }}
                                                    </a>
                                                @else
                                                    <a href="{{route('tracks.index', ['q' => $package->tracking])}}"
                                                       class="" target="_blank">
                                                        {{ $package->tracking }}
                                                    </a>
                                                @endif
                                            @else
                                                <p class="text-danger-400">Tapılmadı</p>
                                            @endif
                                        </td>

                                        <td>{{ $package->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="text-right">
                                            <a href="{{ route('hub.boxes.parcels.delete', [$package->box->id, $package->id]) }}" class="btn btn-danger" style="color: white !important;">
                                                <i class="icon-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($packages->hasPages())
                            <div class="box-footer">
                                {{ $packages->appends( Request::all() )->links() }}
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        function confirmFunction() {
            if (confirm("Əminsiz?")) {
                return true;
            } else {
                event.preventDefault();
                return false;
            }
        }

        $(".panel-select3").select2();
    </script>
@endsection
