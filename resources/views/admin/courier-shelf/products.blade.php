@extends(config('saysay.crud.layout'))

@section('content')
    <div class="panel-heading" style="padding-left: 10px;padding-right: 10px;">
    <div class="col-lg-12 col-lg-offset-0 col-md-12 col-xs-12">
        <div class="panel panel-flat">
            <div class="panel-body">
                <div class="table-responsive overflow-visible">
                    @if($packages)
                        <div>
                            <h5>Pacakges</h5>
                        </div>
                    <table class="table table-hover responsive table-striped">
                        <thead>
                        <tr>
                            <th>Tracking code</th>
                            <th>User</th>
                            <th>Shelf</th>
                            <th>Status</th>
                            <th>Filial</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($packages as $package)
                            <tr>
                                <td>{{ $package->custom_id }}</td>
                                <td>{{ $package->user->name.' '.$package->user->surname }}</td>
                                <td>{{ $package->shelf->name }}</td>
                                <td>{{ $package->status_label }}</td>
                                <td>{{ $package->filial_name  }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    @endif
                        @if($tracks)
                            <div>
                                <h5>Tracks</h5>
                            </div>
                            <table class="table table-hover responsive table-striped">
                                <thead>
                                <tr>
                                    <th>Tracking code</th>
                                    <th>User</th>
                                    <th>Shelf</th>
                                    <th>Status</th>
                                    <th>Filial</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($tracks as $track)
                                    <tr>
                                        <td>{{ $track->tracking_code }}</td>
                                        <td>{{ $track->fullname }}</td>
                                        <td>{{ $track->shelf->name }}</td>
                                        <td>{{ $track->status_with_label   }}</td>
                                        <td>{{ $track->filial_name ?? $track->address  }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @endif
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection

@push('js')
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-form-validator/2.3.26/jquery.form-validator.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
@endpush
