@extends(config('saysay.crud.layout'))

@section('content')
    <div class="col-lg-12 col-lg-offset-0 col-md-12 col-xs-12 bg-white p-20">
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
        <form action="{{ route('courier.shelf.create.post') }}" method="POST">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <div class="col-lg-4 col-lg-offset-0 col-md-6 col-xs-12">
                <label for="name">Name</label>
                <input type="text" class="form-control" name="name" required>
            </div>
            <div class="col-lg-4 col-lg-offset-0 col-md-6 col-xs-12">
                <label for="name">Barcode</label>
                <input type="text" class="form-control" maxlength="6" name="barcode" required>
            </div><div class="col-lg-4 col-lg-offset-0 col-md-6 col-xs-12">
                <label for="courier_id">Courier</label>
                <select name="courier_id" id="courier_id" class="form-control" required>
                    <option value="{{null}}">Select Courier</option>
                    @foreach($couriers as $courier)
                        <option value="{{ $courier->id }}">{{ $courier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-4 col-lg-offset-0 col-md-4 col-xs-12 pt-20">
                <button class="btn btn-primary">Yadda saxla</button>
            </div>
        </form>
    </div>
@endsection

@push('js')
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-form-validator/2.3.26/jquery.form-validator.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
@endpush
