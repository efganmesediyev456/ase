@extends(config('saysay.crud.layout'))

@section('title', 'Kargomat Dashboard')

@section('content')

    <div class="card">
        <div class="card-body">

            <form method="get" action="{{ route('kargomat.not-send-packages') }}">
                <div class="box box-primary box-filter">
                    <div class="box-body">
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="row">
{{--                                    <div class="col-xs-12 col-md-3">--}}
{{--                                        <div class="form-group">--}}
{{--                                            <label>Rayon</label>--}}
{{--                                            <select name="region" class="form-control">--}}
{{--                                                <option value="">Hamısı</option>--}}
{{--                                                <option value="0" {{ request()->filled('region') && request()->get('region') == 0 ? 'selected' : '' }}>--}}
{{--                                                    Yasamal--}}
{{--                                                </option>--}}
{{--                                            </select>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}

                                    <div class="col-xs-12 col-md-3">
                                        <div class="form-group">
                                            <label>Ödəniş Statusu</label>
                                            <select name="status" class="form-control">
                                                <option value="">Hamısı</option>
                                                <option value="0" {{ request()->filled('status') && request()->get('status') == 0 ? 'selected' : '' }}>
                                                    Ödənilməyib
                                                </option>
                                                <option value="1" {{ request()->filled('status') && request()->get('status') == 1 ? 'selected' : '' }}>
                                                    Ödənilib
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-xs-12 col-sm-2">
                                        <div class="form-group" style="padding: 28px 0 0;">
                                            <button class="btn btn-primary" type="submit"><i class="icon-search4"></i>
                                            </button>
                                            <a href="?action=reset" class="btn btn-danger">
                                                <i class="icon-trash"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

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
                                    <th>Ödəniş status</th>
                                    <th>Tarix</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($packages as $package)
                                    <tr>
                                        <td>{{  $package->package_id }}</td>
                                        <td>
                                            <a href="{{route('packages.index', ['q' => $package->barcode ?? ''])}}"
                                               class="" target="_blank">
                                                {{ $package->barcode ?? 'Tapılmadı!' }}
                                            </a>
                                        </td>
                                        <td>
                                            <b>{{ $package->package->paid?'Ödənilib':'Ödənilməyib' }}</b>
                                        </td>
                                        <td>
                                            {{ $package->updated_at->format('d/m/Y H:i') }}
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
