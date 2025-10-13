@extends(config('saysay.crud.layout'))

@section('content')
    <div class="col-lg-12 col-lg-offset-0 col-md-12 col-xs-12 bg-white p-20">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible  show" role="alert">
                {{ session('success') }}
            </div>
            <audio id="successPlayer" autoplay>
                <source src="{{ url('/sounds/success.mp3') }}" type="audio/mpeg">
                Your browser does not support the audio element.
            </audio>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible show" role="alert">
                {{ session('error') }}
            </div>
                <audio id="successPlayer" autoplay>
                    <source src="{{ url('/sounds/warning.mp3') }}" type="audio/mpeg">
                    Your browser does not support the audio element.
                </audio>
        @endif
        <form action="{{ route('courier.shelf.add.product.post') }}" method="POST">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <div class="col-lg-6 col-lg-offset-0 col-md-6 col-xs-12">
                <label for="name">Product barcode</label>
                <input type="text" class="form-control" name="tracking_code" autofocus required>
            </div>
            <div class="col-lg-6 col-lg-offset-0 col-md-6 col-xs-12" style="display:none;">
                <label for="name">Shelf Barcode</label>
                <input type="text" class="form-control" name="shelf_barcode" value="{{ old('shelf_barcode') }}">
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
