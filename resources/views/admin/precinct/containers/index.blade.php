@extends(config('saysay.crud.layout'))

@section('title', 'qapida - Excel')

@section('content')
    <div class="card">
        <div class="card_body">
            <div class="box box-primary p-15">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <form method="post" action="{{ route('precinct.containers') }}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <input type="text" name="name" value="" class="form-control"
                                                   placeholder="Ad"
                                                   autofocus>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <select name="precinct_office_id" class="form-control select2">
                                                @foreach($offices as $office)
                                                    <option value="{{ $office->id }}">{{$office->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <button class="btn btn-success">
                                            <i class="fa fa-plus"></i>
                                            Yarat
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="row">
                        <form method="get" action="{{ route('precinct.containers') }}">
                            <div class="col-md-4">
                                <div class="form-group" style="display: flex;align-items: center">
                                    <select name="office" class="form-control">
                                        <option value="">Filial seç</option>
                                        @foreach($offices as $office)
                                            <option value="{{ $office->id }}"
                                                    @if(request()->filled('office') && request()->get('office') == $office->id) selected="selected" @endif>
                                                {{ ucfirst($office->name) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group" style="display: flex;align-items: center">
                                    <select id="status_" name="status" class="form-control">
                                        <option value="">Birini seç</option>
                                        @foreach($statuses as $value => $key)
                                            @if($value === 'SENDING') @php continue; @endphp @endif
                                            <option value="{{ $key }}"
                                                    @if(request()->filled('status') && request()->get('status') == $key) selected="selected" @endif>{{ __('admin.precinct_warehouse_group_status_'.$key) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group" style="display: flex;align-items: center">
                                    <input class="datepicker-range-start" type="hidden" name="date-from"
                                           value="{{ (request()->filled('date-from') && request()->get('date-from')) ? request()->get('date-from') :  null }}">
                                    <input class="datepicker-range-end" type="hidden" name="date-to"
                                           value="{{ (request()->filled('date-to') && request()->get('date-to')) ? request()->get('date-to') :  null }}">
                                    <div class="input-group date">
                                        <button type="button"
                                                class="btn btn-danger filter-daterange-ranges" @include('crud::inc.field_attributes')>
                                            <i class="icon-calendar22 position-left"></i> <span></span> <b
                                                    class="caret"></b>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <button class="btn btn-success">
                                    <i class="fa fa-plus"></i>
                                    Axtar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="box box-primary mt-15 p-15">
                <div class="box-body table-responsive no-padding">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="text-center pull-left">
                                <b>Ümumi bağlama sayı: {{ $totalPackagesCount }}</b>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($rows->count() > 0)
                <div class="box box-primary mt-15">
                    <div class="box-body table-responsive no-padding">
                        <form method="post" id="itemsForm" action="{{ route('precinct.index') }}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <table class="table table-hover table-striped">
                                <tr>
                                    <th>Adı</th>
                                    <th>Status</th>
                                    <th>Yaradan</th>
                                    <th>Yaradılıb</th>
                                    <th>Göndərən</th>
                                    <th>✅❌⭕️</th>
                                    <th>Göndərilmə tarixi</th>
                                    <th></th>
                                </tr>
                                @foreach($rows as $row)

                                    <tr>
                                        <td style="display: flex; justify-content: space-evenly; gap: 5px">
                                            <a href="{{ route('containerCheck', ['id' => $row->id, 'type' => 'precinct']) }}"
                                               class="btn btn-primary" type="button">
                                                <span class="text-white">Check</span>
                                            </a>
                                            <div style="display: flex">
                                                <a href="{{route('precinct.containers.edit', $row->id)}}"
                                                   class="btn btn-primary btn-block">
                                                    {!! $row->name !!}
                                                </a>
                                                <button type="button" class="btn btn-primary ml-5">{{ $row->packages_count }}</button>
                                            </div>
                                        </td>
                                        <td>{{ __('admin.precinct_warehouse_group_status_'.$row->status) }}</td>
                                        <td>{{ $row->creator->name ?? '' }}</td>
                                        <td>{{ $row->created_at }}</td>
                                        <td>{{ $row->sender->name ?? '' }}</td>
                                        <td>
                                            {{$row->sent_count}} / {{$row->not_sent_count}} / <span style="background-color: red; color: white;">{{$row->not_accepted_count}}</span>
                                        </td>
                                        <td>{{ $row->sent_at }}</td>
                                        <td>
{{--                                            <a href="{{ route('containerSend', ['id' => $row->id, 'type' => 'precinct']) }}"--}}
{{--                                               class="btn btn-success" type="button">--}}
{{--                                                <span class="text-white">Göndər</span>--}}
{{--                                            </a>--}}
                                            <a href="{{ route('containerCheck', ['id' => $row->id, 'type' => 'precinct']) }}"
                                               class="btn btn-primary" type="button">
                                                <span class="text-white">Check</span>
                                            </a>
                                            {{--                                            @if (Auth::user()->role->name === "administrator")--}}
                                            <button onclick="editFlightName({{$row->id}}, '{{$row->name}}')"
                                                    class="btn btn-primary" type="button">
                                                <i class="icon-pencil"></i>
                                            </button>
                                            {{--                                                @if($row->status != AzeriExpressService::STATUSES_IDS['SENT'])--}}
                                            @if (Auth::user()->role->name === "super_admin")
                                            <button
                                                    formaction="{{ route('precinct.delete_group', $row->id) }}"
                                                    class="btn btn-danger" type="submit"
                                                    onclick="confirmFunction()">
                                                <i class="icon-trash"></i>
                                            </button>
                                             @endif
                                            {{--                                            @endif--}}
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </form>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="text-center pull-left">
                                    {{ $rows->appends(request()->query())->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="modal edit-modal fade" id="flightNameModal" role="dialog"
         aria-labelledby="editDeclarationPriceFromListModal">
        <div class="modal-dialog modal-sm" role="document">
            <form action="{{ route('precinct.containers.edit', 0) }}" method="post">
                <input type="hidden" name="_method" value="PUT">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="div" style="position: relative">


                            <h4>Change name</h4>
                            <input type="hidden" name="id" id="id">
                            <div class="form-group">
                                <input type="text" id="name" name="name" class="form-control">
                            </div>

                            <div class="form-group">
                                <select name="status" class="form-control">
                                    <option value="">Birini seç</option>
                                    @foreach($statuses as $value => $key)
                                        <option value="{{ $key }}">{{ __('admin.precinct_warehouse_group_status_'.$key) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default button-close" data-dismiss="modal">Bağla</button>
                        <button type="submit" class="btn btn-primary button-save">Qeyd et</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('js')
    <script>
        $(document).ready(function () {
            $('.select2').select2()
        });

        function confirmFunction() {
            if (confirm("Əminsiniz?")) {
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
            modal.modal('show');
        }
    </script>
@endpush



