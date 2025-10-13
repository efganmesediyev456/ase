@php
    use App\Http\Controllers\Panel\FlightController;
@endphp
@extends(config('saysay.crud.layout'))

@section('title', 'qapida - Excel')


@section('content')
    <div class="card">
        <div class="card_body">

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
                        <form method="post" id="itemsForm" action="{{ route('azeriexpress.index') }}">
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
                                </tr>
                                @foreach($rows as $row)

                                    <tr>
                                        <td class="text-right">
                                            <div style="display: flex">
                                                <a href="{{route('azeriexpress.courier-containers.edit', $row->id)}}"
                                                   class="btn btn-primary btn-block">
                                                    {!! $row->name !!}
                                                </a>
                                                <button type="button" class="btn btn-primary ml-5">{{ $row->packages_count }}</button>
                                            </div>
                                        </td>
                                        <td>{{ __('admin.azeriexpress_warehouse_group_status_'.$row->status) }}</td>
                                        <td>{{ $row->creator->name ?? '' }}</td>
                                        <td>{{ $row->created_at }}</td>
                                        <td>{{ $row->sender->name ?? '' }}</td>
                                        <td>
                                            {{$row->sent_count}} / {{$row->not_sent_count}} / <span style="background-color: red; color: white;">{{$row->not_accepted_count}}</span>
                                        </td>
                                        <td>{{ $row->sent_at }}</td>
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
    <div class="modal edit-modal fade" id="containerEditModal" role="dialog"
         aria-labelledby="editDeclarationPriceFromListModal">
        <div class="modal-dialog modal-sm" role="document">
            <form action="{{ route('azeriexpress.update_name') }}" method="post">
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
                                    <option value="">Status</option>
                                    @foreach($statuses as $status => $key)
                                        <option value="{{ $key }}"> {{ __('admin.azeriexpress_warehouse_group_status_'.$key) }}</option>
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

        function editContainer(id, name) {
            var html = '';
            var modal = $('#containerEditModal');
            modal.find('#id').val(id);
            modal.find('#name').val(name);
            modal.modal('show');
        }
    </script>
@endpush



