@extends(config('saysay.crud.layout'))

@section('title', 'Delivery Date Export')

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
        <form method="POST" class="form-inline" action="{{ route('export_delivery_info') }}" enctype="multipart/form-data">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <div class="form-group" style="margin-right: 10px;">
                <label for="type">Bağlama növü:</label>
                <select name="type" id="type" class="form-control">
                    <option value="">Hamısı</option>
                    @foreach([
                    'pudo' => 'PUDO',
                    'hd' => 'HD',
                    ] as $key => $value)
                    <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin-right: 10px;">
                <label for="file">Excel file:</label>
                <input type="file" value="{{request('file')}}" id="file" class="form-control" name="file">
            </div>
            <button type="submit" class="btn btn-primary">Export</button>
        </form>
    </div>
</div>

@endsection

@push('js')

@endpush
