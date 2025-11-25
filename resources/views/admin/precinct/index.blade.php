@php use App\Models\Precinct\PrecinctPackage; @endphp
@extends(config('saysay.crud.layout'))

@section('title', 'Precinct Dashboard')

@section('content')

    <div class="card">
        <div class="card-body">

            @include('admin.precinct.index_filter')

            <div class="row">
                <div class="col-xs-12">
                    <div class="box box-primary box-filter">
                        <div class="box-body">
                            <div class="col-md-6 form-group" style="line-height: 38px">
                                <p style="margin: 0">Bağlama sayı: <b>{{ $total_packages??0 }}</b></p>
                            </div>
                            <div class="col-md-6 form-group text-right">
                                <button class="btn btn-primary" id="printButton" data-url="{{ route('precinct.receipt') }}" disabled>Çap et</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xs-12 clearfix"></div>

                <div class="col-xs-12">
                    <div class="box box-primary box-filter">
                        <div class="box-body">
                            <table class="table table-responsive display-block">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Kod</th>
                                    <th>Müştəri</th>
                                    <th>Rəf</th>
                                    <th>Müştəri nömrəsi</th>
                                    <th>Fin kod</th>
                                    <th>Status</th>
                                    <th>Konteyner</th>
                                    <th>Ödəniş statusu</th>
                                    <th>Tarix</th>
                                    <th>Məntəqəyə çatıb</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($packages as $package)
                                    @php
                                        $_package = $package->type == 'package' ? $package->package : $package->track;
                                    @endphp
                                    <tr>
                                        <td>
                                            <input type="checkbox"
                                                   name="{{ $package->type == 'package' ? '_packages' : '_tracks' }}"
                                                   value="{{ $package->type . '-' . $package->package_id }}">
                                            &nbsp {{  $package->package_id }}
                                        </td>
                                        <td>
                                            @if($package->type == 'package')
                                                <a href="{{route('packages.index', ['q' => $_package->custom_id ?? ''])}}"
                                                   target="_blank">
                                                    <b> {{ $_package->tracking??'Tapılmadı, ola bilsin silinib!' }}</b>
                                                </a>
                                            @else
                                                <a href="{{route('tracks.index', ['q' => $_package->tracking_code??''])}}"
                                                   target="_blank">
                                                    <b> {{ $_package->tracking_code??'Tapılmadı, ola bilsin silinib!' }}</b>
                                                </a>
                                            @endif
                                        </td>
                                        <td>{{ $package->type == 'package'?$_package->user->full_name??'Tapılmadı, ola bilsin silinib!' : $_package->customer->fullname??'Tapılmadı, ola bilsin silinib!'}}</td>
                                        <td>{{ $_package->cell??'Tapılmadı, ola bilsin silinib!'}}</td>
                                        <td>{{ $package->type == 'package'?$_package->user->customer_id??'Tapılmadı, ola bilsin silinib!' : $_package->customer->id??'Tapılmadı, ola bilsin silinib!'}}</td>
                                        <td>{{ $package->type == 'package'?$_package->user->fin??'Tapılmadı, ola bilsin silinib!' : $_package->customer->fin??'Tapılmadı, ola bilsin silinib!'}}</td>
                                        <td>
                                            @php
                                                $class = "success";
                                                if($package->status == 0){
                                                    $class = "danger";
                                                }
                                                if($package->status == 1){
                                                    $class = "primary";
                                                }
                                            @endphp
                                            <span class="label label-{{ $class }}">{{ __('admin.azeriexpress_warehouse_package_status_'.$package->status) }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('precinct.containers.edit', $package->container->id) }}"><b>{{ $package->container->name }}</b></a>
                                        </td>

                                        <td>
                                            @if($_package && $_package->paid == 1 || $package->type == 'track')
                                                <b class="text-success">Ödənilib</b>
                                            @else
                                                <b class="text-danger">Ödənilməyib</b>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $package->updated_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td>
                                            {{ $package->accepted_at ? $package->accepted_at->format('d/m/Y H:i') : ''}}
                                        </td>
                                        <td>
                                            @if($package->status === PrecinctPackage::STATUSES['DELIVERED'])
                                                <span class="btn btn-success">Təhvil verildi</span>
                                            @else
                                                @if($_package && $_package->paid || $package->type == 'track')
                                                    <button type="button" class="btn btn-primary handover"
                                                            data-url="{{ route('precinct.handover', $package->id) }}">
                                                        Təhvil ver
                                                    </button>
                                                @else
                                                    <span class="btn btn-danger cursor-default">Ödənilməyib</span>
                                                @endif
                                            @endif
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

@push('js')
    <script>
        $('.handover').click(function () {
            var check = confirm('əminsiniz?');
            if (check === true) {
                var button = $(this);
                var url = button.data('url');
                $.ajax({
                    url: url,
                    type: 'POST',
                    contentType: 'application/json',
                    success: function (response) {
                        if (response.status) {
                            button.text('Təhvil verildi')
                            button.attr('disabled', true)
                        } else {
                            alert(response.message)
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Request failed with status:', status);
                        console.error('Error:', error);
                    }
                });
            }
        });

        $(".panel-select3").select2();

        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('input[type="checkbox"][name="_packages"], input[type="checkbox"][name="_tracks"]');
            const printButton = document.getElementById('printButton');

            // Function to handle checkbox changes
            function handleCheckboxChange() {
                const isAnyChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);
                printButton.disabled = !isAnyChecked;
            }

            // Add change event listener to all checkboxes
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', handleCheckboxChange);
            });

            // Handle print button click
            printButton.addEventListener('click', function() {
                const selectedValues = Array.from(checkboxes)
                    .filter(checkbox => checkbox.checked)
                    .map(checkbox => {
                        const [type, id] = checkbox.value.split('-');
                        return { type, id };
                    });

                // Separate tracks and packages
                const tracks = selectedValues
                    .filter(item => item.type === 'track')
                    .map(item => item.id);

                const packages = selectedValues
                    .filter(item => item.type === 'package')
                    .map(item => item.id);

                // Build URL manually without encoding
                let url = printButton.getAttribute('data-url') + '?';

                // Add tracks
                tracks.forEach((track, index) => {
                    url += `tracks[]=${track}`;
                    if (index < tracks.length - 1) url += '&';
                });

                // Add packages
                if (tracks.length > 0 && packages.length > 0) url += '&';
                packages.forEach((pkg, index) => {
                    url += `packages[]=${pkg}`;
                    if (index < packages.length - 1) url += '&';
                });

                // Open in new window
                window.open(url, '_blank');
            });
        });
    </script>
@endpush
