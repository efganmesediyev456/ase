@extends(config('saysay.crud.layout'))

@section('content')
    <div class="panel-heading" style="padding-left: 10px;padding-right: 10px;">

        <div style="padding: 20px 0px 5px 0px;" class="bg-white">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible  show" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible show" role="alert">
                    {{ session('error') }}
                </div>
            @endif
            <div class="row align-items-center mb-3">
                <div class="col-md-4 col-12">
                    <h5 class="mb-0 ml-20">Courier Shelf</h5>
                </div>
                <div class="col-md-8 col-12 text-md-end mt-2 mt-md-0" style="text-align: end">
                    <a href="{{ route('courier.shelf.add.product') }}" class="btn btn-primary mr-20">Add product</a>
                    <a href="{{ route('courier.shelf.create') }}" class="btn btn-primary mr-20">Create new shelf</a>
                </div>
            </div>


        </div>
    </div>

    <div class="col-lg-12 col-lg-offset-0 col-md-12 col-xs-12">
        <div class="panel panel-flat">

            <div class="panel-body">
                <div class="table-responsive overflow-visible">
                    <table class="table table-hover responsive table-striped">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Barcode</th>
                            <th>Product count</th>
                            <th>Courier</th>
                            <th width="20%">#</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($results as $result)
                            <tr @if($result->total_products == 0) style="background-color: #cf9292" @endif>
                                <td>{{ $result->name }}</td>
                                <td>{{ $result->barcode }}</td>
                                <td>{{ $result->total_products ?? 0 }}</td>
                                <td>{{ @$result->courier->name }}</td>
                                <td style="display: flex">
                                    <a href="{{ route('courier.shelf.sticker',$result->id) }}" class="btn btn-primary" style="color: white !important;margin: 5px;" target="_blank">
                                        <i class="icon-camera"></i> Sticker
                                    </a>
                                    <a href="{{ route('courier.shelf.products',$result->id) }}" class="btn btn-primary" style="color: white !important;margin: 5px;" target="_blank">
                                        <i class="icon-package"></i> Məhsullar
                                    </a>
                                    @if($result->barcode != 'courier-page')
                                    <a href="{{ route('courier.shelf.edit',$result->id) }}" class="btn btn-primary" style="color: white !important;margin: 5px;" target="_blank">
                                        Edit
                                    </a>
                                    @endif
                                    @if($result->total_products == 0 && $result->barcode != 'courier-page')
                                    <form action="{{ route('courier.shelf.delete',$result->id) }}" method="POST">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <button type="submit" class="btn btn-danger" style="color: white !important;margin: 5px;" >
                                            <i class="icon-trash"></i> Delete
                                        </button>
                                    </form>
                                        @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
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
