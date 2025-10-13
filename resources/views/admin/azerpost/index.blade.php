@extends(config('saysay.crud.layout'))

@section('title', 'Azerpost Dashboard')

@section('content')

    <div class="card">
        <div class="card-body">

            @include('admin.azerpost.index_filter')

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
                                    <th>ID</th>
                                    <th>Kod</th>
                                    <th>Status</th>
                                    <th>Konteyner</th>
                                    <th>Tarix</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($packages as $package)
                                    <tr>
                                        <td>{{ $package->package_id}}</td>
                                        <td>
                                            @if($package->barcode)
                                                @if($package->type == 'package')
                                                    <a href="{{route('packages.index', ['q' => $package->barcode])}}"
                                                       class="" target="_blank">
                                                        {{ $package->barcode }}
                                                    </a>
                                                @else
                                                    <a href="{{route('tracks.index', ['q' => $package->barcode])}}"
                                                       class="" target="_blank">
                                                        {{ $package->barcode }}
                                                    </a>
                                                @endif
                                            @else
                                                <p class="text-danger-400">Tapılmadı</p>
                                            @endif
                                        </td>
                                        <td>
                                            <b>{{ __('admin.azerpost_warehouse_package_status_'.$package->status) }}</b>
                                        </td>
                                        <td>
                                            <a href="{{ route('azerpost.containers.edit', $package->container->id) }}"><b>{{ $package->container->name }}</b></a>
                                        </td>
                                        <td>{{ $package->updated_at->format('d/m/Y H:i') }}</td>
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
