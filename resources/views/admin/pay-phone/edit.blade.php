@extends(config('saysay.crud.layout'))

@section('title', 'Ödənişi redaktə et')

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('pay-phone.update', $pay_phone->id) }}" method="POST">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="_method" value="PUT">

                <div class="form-group">
                    <label for="phone">Telefon nömrəsi</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="{{ $pay_phone->phone }}">
                    @if($errors->first('phone'))
                        <small class="form-text text-danger">{{ $errors->first('phone') }}</small>
                    @endif
                </div>

                <div class="form-group">
                    <label for="amount">Məbləğ (AZN)</label>
                    <input type="text" class="form-control" id="amount" name="amount" value="{{ $pay_phone->amount }}">
                    @if($errors->first('amount'))
                        <small class="form-text text-danger">{{ $errors->first('amount') }}</small>
                    @endif
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select class="form-control" id="status" name="status" >
                        <option value="pending" {{ $pay_phone->status == 'pending' ? 'selected' : '' }}>Gözləmədə</option>
                        <option value="success" {{ $pay_phone->status == 'success' ? 'selected' : '' }}>Uğurlu</option>
                        <option value="failed" {{ $pay_phone->status == 'failed' ? 'selected' : '' }}>Uğursuz</option>
                    </select>
                    @if($errors->first('status'))
                        <small class="form-text text-danger">{{ $errors->first('status') }}</small>
                    @endif
                </div>

                <button type="submit" class="btn btn-primary">Yenilə</button>
                <a href="{{ route('pay-phone.index') }}" class="btn btn-secondary">Geri</a>
            </form>
        </div>
    </div>
@endsection
