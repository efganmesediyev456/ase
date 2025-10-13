@php
    use App\Models\Hub\Box;
@endphp
@extends(config('saysay.crud.layout'))

@section('title', 'Qutular')

@section('content')
    @if($errors->any())
        <div class="card box-primary">
            <div class="card-header">
                @foreach($errors->all() as $error)
                    <span class="text-danger text-bold">{{ $error }}</span>
                @endforeach
            </div>
        </div>
    @endif
    <div class="card">
        <div class="card_body">
            <div class="box box-primary p-15">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-8">
                            <form method="post" action="{{ route('hub.boxes') }}">
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
                                            <select name="carrier" class="form-control select2">
                                                @foreach(Box::CARRIERS as $key => $carrier)
                                                    <option value="{{ $key }}">{{ $carrier }}</option>
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
                    <hr>
                    <div class="row">
                        <form method="get" action="{{ route('azeriexpress.containers') }}">
                            <div class="col-md-4">
                                <div class="form-group" style="display: flex;align-items: center">
                                    <select name="carrier" class="form-control">
                                        <option value="">Şirkət seç</option>
                                        @foreach(Box::CARRIERS as $key => $carrier)
                                            <option value="{{ $key }}"
                                                    @if(request()->filled('carrier') && request()->get('carrier') == $key) selected="selected" @endif>
                                                {{ $carrier }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            {{--                            <div class="col-md-4">--}}
                            {{--                                <div class="form-group" style="display: flex;align-items: center">--}}
                            {{--                                    <select id="status_" name="status" class="form-control">--}}
                            {{--                                        <option value="">Birini seç</option>--}}
                            {{--                                        @foreach($statuses as $value => $key)--}}
                            {{--                                            @if($value === 'SENDING') @php continue; @endphp @endif--}}
                            {{--                                            <option value="{{ $key }}"--}}
                            {{--                                                    @if(request()->filled('status') && request()->get('status') == $key) selected="selected" @endif>{{ __('admin.azeriexpress_warehouse_group_status_'.$key) }}</option>--}}
                            {{--                                        @endforeach--}}
                            {{--                                    </select>--}}
                            {{--                                </div>--}}
                            {{--                            </div>--}}
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
                            <div class="col-md-3"></div>
                            <div class="col-md-1">
                                <button class="btn btn-success">
                                    <i class="fa fa-plus"></i>
                                    Axtar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <hr>
            <div class="box box-primary p-15">
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
                        <form method="post" id="itemsForm" action="{{ route('hub.boxes') }}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <table class="table table-hover table-striped">
                                <tr>
                                    <th>Adı</th>
                                    <th>Şirkət</th>
                                    <th>Status</th>
                                    <th>Yaradan</th>
                                    <th>Yaradılıb</th>
                                    <th></th>
                                </tr>
                                @foreach($rows as $row)
                                    <tr>
                                        <td class="text-right">
                                            <div style="display: flex">
                                                <a href="{{route('hub.boxes.show', $row->id)}}"
                                                   class="btn btn-primary btn-block">
                                                    {!! $row->name !!}
                                                </a>
                                                <button type="button"
                                                        class="btn btn-primary ml-5">{{ $row->parcels_count }}</button>
                                            </div>
                                        </td>
                                        <td>
                                            {{ Box::CARRIERS[$row->carrier] }}
                                        </td>
                                        <td>
                                            <span class="{{ $row->status == 1 ? 'btn btn-success ' : 'btn btn-danger ' }}">
                                                {{ Box::STATUSES[$row->status] ?? '-' }}
                                            </span>
                                        </td>
                                        <td>{{ $row->creator->name ?? '' }}</td>
                                        <td>{{ $row->created_at }}</td>
                                        <td class="text-right">
                                            <a href="#" class="btn btn-success"
                                               style="color: white !important; font-weight: bold">
                                                <i class="icon-printer2"></i>
                                            </a>
                                            <button onclick="editContainer('{{ route('hub.boxes.update', $row->id) }}', '{{$row->name}}', '{{ $row->status }}')"
                                                    class="btn btn-primary" type="button">
                                                <i class="icon-pencil"></i>
                                            </button>
                                            <button
                                                    formaction="{{ route('hub.boxes.delete', $row->id) }}"
                                                    class="btn btn-danger" type="submit"
                                                    onclick="confirmFunction()">
                                                <i class="icon-trash"></i>
                                            </button>
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
    <div class="modal edit-modal fade" id="editBoxModal" role="dialog"
         aria-labelledby="editBoxModal">
        <div class="modal-dialog modal-sm" role="document">
            <form action="" method="post">
                <input type="hidden" name="_method" value="PUT">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="div" style="position: relative">
                            <h4>Change name</h4>
                            <div class="form-group">
                                <input type="text" id="name" name="name" class="form-control">
                            </div>

                            <div class="form-group">
                                <select name="status" class="form-control">
                                    <option value="">Status</option>
                                    @foreach(Box::STATUSES as $key => $status)
                                        <option value="{{ $key }}"> {{ $status }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default button-close" data-dismiss="modal">Bağla</button>
                        <button type="submit" class="btn btn-primary button-save">Yenilə</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function (e) {
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

        function editContainer(url, name, status) {
            const modal = $('#editBoxModal');
            modal.find('form').attr('action', url);
            modal.find('#name').val(name);
            modal.find('select[name="status"]').val(status); // Adjust the selector as needed
            modal.modal('show');
        }
    </script>
@endpush



