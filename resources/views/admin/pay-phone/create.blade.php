@extends(config('saysay.crud.layout'))

@section('title', 'Yeni ödəniş əlavə et')

@section('content')

    <div class="card">
        <div class="card-body">
            <form action="{{ route('pay-phone.store') }}" method="POST">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                <div class="form-group">
                    <label for="phone">Telefon nömrəsi</label>
                    <input type="number" class="form-control" id="phone" name="phone" value="{{old('phone')}}" >
                    @if($errors->first('phone'))
                        <small class="form-text text-danger">{{ $errors->first('phone') }}</small>
                    @endif
                </div>

                <div class="form-group">
                    <label for="amount">Məbləğ (AZN)</label>
                    <input type="text" class="form-control" id="amount" name="amount" value="{{old('amount')}}">
                    @if($errors->first('amount'))
                        <small class="form-text text-danger">{{ $errors->first('amount') }}</small>
                    @endif
                </div>

                <button type="submit" class="btn btn-primary">Yadda saxla</button>
                <a href="{{ route('pay-phone.index') }}" class="btn btn-secondary">Geri</a>
            </form>
        </div>
    </div>

@endsection
