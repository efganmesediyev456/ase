@php use App\Models\Hub\Box; @endphp
@extends(config('saysay.crud.layout'))

@section('title', 'Azeriexpress - Konteyner')

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

    <div class="card box-primary">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <b>Qutu adı:</b> {{ $box->name }}
                    <br>
                    <b>Şirkət: </b>{{ Box::CARRIERS[$box->carrier] }}
                    <br>
                    <b>Qutu status: </b>{{ Box::STATUSES[$box->status] }}
                    <br>
                    <b>Qutuda olan bağlama sayı: </b> {{$packagesCount ?? ''}}
                </div>

                <div class="col-md-6 text-right">
                    <form method="post" action="{{ route('hub.boxes.update', $box->id) }}">
                        <input type="hidden" name="_method" value="PUT">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="status" value="{{ $box->status ? 0 : 1 }}">
                        <a class="btn btn-primary" target="_blank" href="{{ route('hub.boxes.parcels.print', $box->id) }}">
                            Çap et
                        </a>
                        <button class="btn btn-{{ $box->status ? 'danger' : 'success' }}" type="submit">
                            {{ $box->status ? "Bağla" : "Aç" }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card box-primary">
        <div class="card-header">
            <form method="post" action="{{ route('hub.boxes.parcels', $box->id) }}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="form-group mb-3">
                    <label for="tracking">Tracking</label>
                    <input type="text" name="tracking" id="tracking"
                           class="form-control"
                           autocomplete="off" autofocus>
                </div>

                <div class="form-group">
                    <button class="btn btn-success" type="submit" id="">Əlavə et</button>
                </div>
            </form>
        </div>
    </div>

    @if($packages->count() > 0)
        <div class="card box-primary">
            <div class="card-body table-responsive no-padding">
                <form method="post" action="{{ route('hub.boxes.parcels', $box->id) }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <table class="table table-hover ">
                        <tr>
                            <th>#No</th>
                            <th>Tracking</th>
                            <th>Əlavə edən</th>
                            <th>Əlavə edilmə tarixi</th>
                            <th></th>
                        </tr>
                        @foreach($packages as $key => $row)
                            <tr>
                                <td>#{{ ++$key }}</td>
                                <td>
                                    <b>{{ $row->tracking }}</b>
                                </td>
                                <td>{{ $row->creator->name??'' }}</td>
                                <td>{{ $row->created_at?$row->created_at->format('d M, Y H:i'):'' }}</td>
                                <td class="text-right">
                                    <button formaction="{{ route('hub.boxes.parcels.delete', [$box->id, $row->id]) }}"
                                            class="btn btn-danger"
                                            type="submit"
                                            onclick="confirmationDelete()">
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


