@extends(config('saysay.crud.layout'))

@section('title', 'Kargomat')

@section('content')
    <div class="card">
        <div class="card_body">
            <div class="box box-primary pt-15">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-8">
                            <form method="post" action="{{ route('kargomat.offices') }}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="">
                                    <div class="row form-group ml-10">
                                        <div class="col-md-6">
                                            <input type="text" name="name" value="" class="form-control"
                                                   placeholder="Ad"
                                                   autofocus>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" name="description" value="" class="form-control"
                                                   placeholder="Qeyd"
                                                   autofocus>
                                        </div>
                                    </div>
                                    <div class="row form-group ml-10">
                                        <div class="col-md-6">
                                            <input type="text" name="address" value="" class="form-control"
                                                   placeholder="Unvan"
                                                   autofocus>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" name="contact_name" value="" class="form-control"
                                                   placeholder="Elaqe Shexs"
                                                   autofocus>
                                        </div>
                                    </div>
                                    <div class="row form-group ml-10">
                                        <div class="col-md-6">
                                            <input type="text" name="latitude" value="" class="form-control"
                                                   placeholder="Latitude"
                                                   autofocus>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" name="longitude" value="" class="form-control"
                                                   placeholder="Longitude"
                                                   autofocus>
                                        </div>
                                    </div>
                                    <div class="row form-group ml-10">
                                        <div class="col-md-6">
                                            <input type="text" name="contact_phone" value="" class="form-control"
                                                   placeholder="Elaqe nomresi"
                                                   autofocus>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" name="foreign_id" value="" class="form-control"
                                                   placeholder="Foreign id"
                                                   autofocus>
                                        </div>
                                        <div class="col-md-6">
                                            <button class="btn btn-success">
                                                <i class="fa fa-plus"></i>
                                                Yarat
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>

            @if($offices->count() > 0)
                <div class="box box-primary mt-15 ml-10 mr-10">
                    <div class="box-body table-responsive no-padding">
                        <form method="post" action="{{ route('kargomat.index') }}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="_method" value="delete">
                            <table class="table table-hover table-striped">
                                <tr>
                                    <th>Id</th>
                                    <th>Ad</th>
                                    <th>Ad (En)</th>
                                    <th>Qeyd</th>
                                    <th>Qeyd (En)</th>
                                    <th>Unvan</th>
                                    <th>foreign ID</th>
                                    <th>Unvan (En)</th>
                                    <th>Yaradılıb</th>
                                    <th></th>
                                </tr>
                                @foreach($offices as $row)
                                    <tr>
                                        <td>{{ $row->id }}</td>
                                        <td>{{ $row->name }}</td>
                                        <td>{{ $row->name_en }}</td>
                                        <td>{{ $row->description }}</td>
                                        <td>{{ $row->description_en }}</td>
                                        <td>{{ $row->address }}</td>
                                        <td>{{ $row->foreign_id }}</td>
                                        <td>{{ $row->address_en }}</td>
                                        <td>{{ $row->created_at }}</td>
                                        <td>
                                            <button onclick="editOffice({{$row->id}}, '{{ json_encode($row) }}', '{{ route('kargomat.offices.update', $row->id) }}')"
                                                    class="btn btn-primary" type="button">
                                                <i class="icon-pencil"></i>
                                            </button>
                                            <button
                                                    formaction="{{ route('kargomat.offices.delete', $row->id) }}"
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
                                    {{$offices->links()}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="modal edit-modal fade" id="editOfficeModal" role="dialog"
         aria-labelledby="editDeclarationPriceFromListModal">
        <div class="modal-dialog modal-sm" role="document">
            <form action="{{ route('kargomat.offices') }}" method="post" id="officeForm">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="_method" value="PUT">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="div" style="position: relative">
                            <h4>Change name</h4>
                            <input type="hidden" name="id" id="id">
                            <div class="form-group">
                                <label for="name">Name:</label>
                                <input type="text" id="name" name="name" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="name_en">Name (En):</label>
                                <input type="text" id="name_en" name="name_en" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="description">Description:</label>
                                <input type="text" id="description" name="description" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="description_en">Description (En):</label>
                                <input type="text" id="description_en" name="description_en" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="address">Address:</label>
                                <input type="text" id="address" name="address" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="address_en">Address (En):</label>
                                <input type="text" id="address_en" name="address_en" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="contact_name">Contact Name:</label>
                                <input type="text" id="contact_name" name="contact_name" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="contact_phone">Contact Phone:</label>
                                <input type="text" id="contact_phone" name="contact_phone" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="latitude">Latitude:</label>
                                <input type="text" id="latitude" name="latitude" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="longitude">Longitude:</label>
                                <input type="text" id="longitude" name="longitude" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="foreign_id">Foreign id:</label>
                                <input type="text" id="foreign_id" name="foreign_id" class="form-control">
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

        function editOffice(id, row, action) {
            $('#officeForm').attr('action', action);
            var modal = $('#editOfficeModal');

            row = JSON.parse(row)
            modal.find('#id').val(id);
            modal.find('#name').val(row.name);
            modal.find('#name_en').val(row.name_en);
            modal.find('#description').val(row.description);
            modal.find('#description_en').val(row.description_en);
            modal.find('#address').val(row.address);
            modal.find('#address_en').val(row.address_en);
            modal.find('#contact_name').val(row.contact_name);
            modal.find('#contact_phone').val(row.contact_phone);
            modal.find('#latitude').val(row.latitude);
            modal.find('#longitude').val(row.longitude);
            modal.find('#foreign_id').val(row.foreign_id);
            modal.modal('show');
        }
    </script>
@endpush



