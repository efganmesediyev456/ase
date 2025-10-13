@extends(config('saysay.crud.layout'))

@section('title', 'YeniPoct - Konteyner')

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

    <div class="card box-primary">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <b>Konteyner adı:</b> {{$container->name ?? ''}}
                    <br>
                    <b>Konteyner Status: </b>{{ __('admin.yenipoct_warehouse_group_status_'.$container->status) }}
                    <br>

                    <a href="{{ route('yenipoct.containers.edit', $container->id) }}">
                        Ümumi Bağlama sayı: {{$packagesCount ?? ''}}
                    </a>
                    <br>
                    <a href="{{ route('yenipoct.containers.edit', $container->id) }}" class="text-success">
                        Göndərilən Bağlama sayı: {{$sentPackagesCount ?? ''}}
                    </a>
                    <br>
                    <a href="{{ route('yenipoct.containers.edit', $container->id) }}" class="text-warning">
                        Göndərilməyən Bağlama sayı: {{$notSentPackagesCount ?? ''}}
                    </a>
                    <br>
                    <a href="{{ route('yenipoct.containers.edit', $container->id) }}?status=2" class="text-danger">
                        Problemli Bağlamalər: {{$packagesProblemCount}}
                    </a>
                </div>

                <div class="col-md-6 text-right">
                    <a href="?export=true" class="btn btn-primary mr-5">Export</a>
                    <form method="post"
                          action="{{ route('yenipoct.send-packages', $container->id) }}"
                          class="form-prevent-multiple-submits"
                          style="display: initial"
                          id="Form">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        {{--                        <button type="button" class="btn btn-warning"--}}
                        {{--                                data-url="{{ route('panel.clear-cache', 'qapida-active-branch') }}"--}}
                        {{--                        >--}}
                        {{--                            Keşi sil--}}
                        {{--                        </button>--}}
{{--                        @if($container->status === \App\Models\YeniPoct\YenipoctOrder::STATUSES['WAITING'])--}}

{{--                       --}}
{{--                        @endif--}}
                        <button class="btn btn-success"
                                data-url="{{ route('yenipoct.send-packages', $container->id) }}"
                        >
                            Göndər
                        </button>
                        <button class="btn btn-success"
                                data-url="{{ route('yenipoct.send-packages', $container->id) }}?temu=true"
                        >
                            Göndər Temu
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
    @if($errors->any())
        <div class="card box-primary">
            <div class="card-header">
                @foreach($errors->all() as $error)
                    <span class="text-danger text-bold">{{ $error }}</span>
                @endforeach
            </div>
        </div>
    @endif

    <div class="card" style="max-width: 300px">
        <div class="card-header">
            <form method="get"
                  action="{{ route('yenipoct.containers.edit', $container->id) }}"
                  class="form-prevent-multiple-submits"
            >
                <div class="form-group mb-3">
                    <label for="barcode">Partner</label>
                    <select name="partner_id" class="form-control">
                        <option value="">Select</option>
                        @foreach($partners as $partner)
                            <option value="{{$partner->id}}" {{request()->partner_id == $partner->id? 'selected' : ''}}>{{$partner->name}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <button class="btn btn-primary" type="submit">Axtar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card box-primary">
        <div class="card-header">
            <form method="post"
                  action="{{ route('yenipoct.store', $container->id) }}"
                  enctype="multipart/form-data"
                  class="form-prevent-multiple-submits"
            >
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="form-group mb-3">
                    <label for="barcode">Barcode</label>
                    <input type="text" name="barcode" id="barcode"
                           class="form-control"
                           autocomplete="off"
                           value="" autofocus>
                </div>

                <div class="form-group">
                    <button class="btn btn-primary" name="button" type="submit" id="">Əlavə et</button>
                </div>
            </form>
        </div>
    </div>

    @if (strpos(strtolower($container->name), 'kuryer') !== false)
        <div class="card box-primary">
            <div class="card-header">
                <form method="post"
                      action="{{ route('yenipoct.store', $container->id) }}"
                      enctype="multipart/form-data"
                      class="form-prevent-multiple-submits"
                >
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="form-group mb-3">
                        <label for="barcodes">Fayl seç</label>
                        <input type="file" name="barcodes" id="barcodes"
                               class="form-control"
                               value="" autofocus>
                    </div>

                    <div class="form-group">
                        <button class="btn btn-primary" name="button" type="submit" id="">Import et</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($packages->count() > 0)
        <div class="card box-primary">
            <div class="card-body table-responsive no-padding">
                <form method="post" id="itemsForm" action="">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <table class="table table-hover table-striped">
                        <tr>
                            <th>Sifariş kodu</th>
                            <th>YP Status</th>
                            <th>Package Status</th>
                            <th>Kenteyner adı</th>
                            <th>Əlavə edən</th>
                            <th>Comment</th>
                            <th>Əlavə edilmə tarixi</th>
                            <th></th>
                        </tr>
                        @foreach($packages as $row)
                            @php
                                $type = $row->type == 'package' ? 'packages' : 'tracks';
                            @endphp
                            <tr>
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
                                    <span class="label label-{{ $class }}">{{ __('admin.yenipoct_warehouse_package_status_'.$row->status) }}</span>
                                </td>
                                <td>
                                    @php
                                        $_type = "package";
                                        if($row->type == 'track'){
                                            $_type = "track";
                                        }
                                        $_status = $row->type == 'track'?$row->track->status:$row->package->status;
                                    @endphp
                                    <span class="label label-primary">{{ __('admin.'.$_type.'_status_'.$_status) }}</span>
                                </td>
                                <td>{{ $row->container->name ?? '' }}</td>
                                <td>{{ $row->creator->name??'' }}</td>

                                <td title="{{ $row->comment }}">
                                    <p class="text-warning-400">{{ str_limit($row->comment, 50) }}</p>
                                </td>

                                <td>{{ $row->created_at?$row->created_at->format('d M, Y H:i'):'' }}</td>
{{--                                @if (Auth::user()->role->name === "super_admin")--}}
                                @permission('delete-package_from_container')
                                <td>
                                    <button formaction="{{ route('yenipoct.delete-package', $row->id) }}"
                                            class="btn btn-danger"
                                            type="submit"
                                            formmethod="get"
                                            onclick="confirmationDelete()">
                                        <i class="icon-trash"></i>
                                    </button>
                                </td>
                                @endpermission
                                {{--                                @endif--}}
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


