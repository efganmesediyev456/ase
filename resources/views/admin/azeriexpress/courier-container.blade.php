@extends(config('saysay.crud.layout'))

@section('title', 'Azeriexpress - Konteyner')

@section('content')
    @if(Session::has('success'))
        <audio id="successPlayer" autoplay>
            <source src="{{ url('/sounds/success.mp3') }}" type="audio/mpeg">
            Your browser does not support the audio element.
        </audio>
    @endif

    @if(Session::has('warning'))
        <audio id="successPlayer" autoplay>
            <source src="{{ url('/sounds/warning.mp3') }}" type="audio/wav">
            Your browser does not support the audio element.
        </audio>
    @endif

    @if ($errors->any())
        <audio id="successPlayer" autoplay>
            <source src="{{ url('/sounds/error.wav') }}" type="audio/wav">
            Your browser does not support the audio element.
        </audio>
    @endif

    <style>
        td {
            position: relative;
        }

        td:hover {
            position: relative;
        }

        td:hover::before {
            content: attr(title);
            white-space: normal;
            position: absolute;
            z-index: 1;
            background-color: #fff;
            padding: 10px;
            border: 1px solid #ccc;
            top: 100%;
            left: 0;
        }
    </style>

    @if($errors->any())
        <div class="card box-primary">
            <div class="card-header">
                @foreach($errors->all() as $error)
                    <span class="text-danger text-bold">{{ $error }}</span>
                @endforeach
            </div>
        </div>
    @endif

    <div class="card box-primary">
        <div class="card-body">
            <form method="GET" class="form-inline" action="{{ route('azeriexpress.courier-containers.edit', $container->id) }}">
                <div class="form-group" style="margin-right: 10px;">
                    <label for="package_number">Bağlama kodu:</label>
                    <input type="text" value="{{request('package_number')}}" id="package_number" class="form-control" name="package_number">
                </div>
                <div class="form-group" style="margin-right: 10px;">
                    <label for="status">Status:</label>
                    <select name="status" id="status" class="form-control">
                        <option value="">Hamısı</option>
                        @foreach([
                            1 => 'Göndərildi',
                            30 => 'Anbarda',
                            31 => 'Yoldadır',
                            32 => 'Məntəqəyə çatıb',
                            33 => 'Təhvil verildi',
                            34 => 'Təhvil verilmədi',
                            35 => 'Naməlum status',
                            36 => 'Kuryer təyin edilib'
                        ] as $key => $value)
                            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin-right: 10px;">
                    <label for="partner_id">Partnyor:</label>
                    <select name="partner_id" id="partner_id" class="form-control">
                        <option value="">Hamısı</option>
                        @foreach($partners as $partner)
                            <option value="{{ $partner->id }}" {{ request('partner_id') == $partner->id ? 'selected' : '' }}>
                                {{ $partner->name }} ({{ $partner->email }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-right: 10px;">
                    <label for="admin_id">Əlavə edən:</label>
                    <select name="admin_id" id="admin_id" class="form-control">
                        <option value="">Hamısı</option>
                        @foreach($admins as $admin)
                            <option value="{{ $admin->id }}" {{ request('admin_id') == $admin->id ? 'selected' : '' }}>
                                {{ $admin->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-right: 10px;">
                    <label for="start_date">Başlanğıc tarix:</label>
                    <input type="date" value="{{request('start_date')}}" id="start_date" class="form-control" name="start_date">
                </div>
                <div class="form-group" style="margin-right: 10px;">
                    <label for="end_date">Son tarix:</label>
                    <input type="date" value="{{request('end_date')}}" id="end_date" class="form-control" name="end_date">
                </div>

                <button type="submit" class="btn btn-primary">Axtar</button>
                <a href="{{route('azeriexpress.courier-containers.edit', $id)}}" class="btn btn-primary">Sıfırla</a>
            </form>
        </div>
    </div>

    @if($packages->count() > 0)
        <div class="card box-primary">
            <div class="card-body table-responsive no-padding">
                <form method="post" id="itemsForm" action="">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <table class="table table-hover table-striped">
                        <tr>
                            <th>id</th>
                            <th>Sifariş kodu</th>
                            <th>AzEx Status</th>
                            <th>Package Status</th>
                            <th>Kenteyner adı</th>
                            <th>Əlavə edən</th>
                            <th>Comment</th>
                            <th>Əlavə edilmə tarixi</th>
                        </tr>
                        @foreach($packages as $row)
                            @php
                                $type = $row->type == 'package' ? 'packages' : 'tracks';
                            @endphp
                            <tr>
                                <td>{{$row->id}}</td>
                                <td class="text-right">
                                    <div style="display: flex">
                                        @if($row->type == 'track')
                                            <a href="{{route($type . '.index', ['q' => $row->track->tracking_code ?? ''])}}"
                                               class="" target="_blank">
                                                {{ $row->track->tracking_code ?? '-Tapılmadı!' }}
                                            </a>
                                        @else
                                            <a href="{{route('packages.index', ['q' => $row->package->tracking ?? ''])}}"
                                               class="" target="_blank">
                                                {{ $row->package->tracking ?? '-Tapılmadı!' }}
                                            </a>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $class='primary';
                                        if($row->status == 1){
                                            $class = "danger";
                                        }
                                        if($row->status == 0){
                                            $class = "warning";
                                        }

                                    @endphp
                                    <span class="label label-{{ $class }}">{{ __('admin.azeriexpress_courier_status_'.$row->status) }}</span>
                                </td>
                                <td>
                                    @php
                                        $_type = "package";
                                        if($row->type == 'track'){
                                            $_type = "track";
                                        }
                                        $_status = $row->type == 'track' ? $row->track->status : $row->package->status;
                                    @endphp
                                    <span class="label label-primary">{{ __('admin.'.$_type.'_status_'.$_status) }}</span>
                                </td>
                                <td>{{ $row->container->name ?? '' }}</td>
                                <td>{{ $row->creator->name??'' }}</td>

                                <td title="{{ $row->comment }}">
                                    <p class="text-warning-400">{{ str_limit($row->comment, 50) }}</p>
                                </td>

                                <td>{{ $row->created_at?$row->created_at->format('d M, Y H:i'):'' }}</td>

                            </tr>
                        @endforeach
                    </table>
                </form>

                <div class="row">
                    <div class="col-md-12">
                        <div class="text-center pull-left">
                            {{$packages->links()}}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @endif

@endsection

@push('js')
    <script>
        function confirmFunction() {
            if (confirm("Əminsiz?")) {
                return true;
            } else {
                event.preventDefault();
                return false;
            }
        }

        $('.select-all').click(function () {
            //console.log('sss');
            $(this).parents('form').find('input:checkbox').prop('checked', $(this).is(':checked'));
        });
        $('input:checkbox:not(.select-all)').click(function () {
            form = $(this).parents('form');

            selectAll = true;
            form.find('input:checkbox:not(.select-all)').each(function () {
                if (!$(this).prop('checked')) {
                    selectAll = false;
                }
            });

            $(this).parents('form').find('input:checkbox.select-all').prop('checked', selectAll);
        });


        function editFlightName(id, name) {
            var html = '';

            var modal = $('#flightNameModal');

            modal.find('#id').val(id);
            modal.find('#name').val(name);


            //modal.find('.modal-body').html(html);
            modal.modal('show');

        }


        $(document).ready(function () {
            let form = $('#Form');
            form.on('click', 'button', function (e) {
                e.preventDefault();
                console.log('SSS');
                let btn = $(this);
                form.attr('action', btn.data('url'));
                let check = confirm('Are you sure?');
                if (check) {
                    form.submit();
                }
            });
        });

        function confirmationDelete(url) {
            var check = confirm('əminsiniz?');

            if (check == true) {
                $('#itemsForm').attr('action', url).submit();
            } else {
                event.preventDefault();
                return false;
            }
        }
    </script>
@endpush


