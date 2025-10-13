@extends(config('saysay.crud.layout'))

@section('title', 'Pay with phone Dashboard')
<style>
    .row-success {
        background-color: #d0e8d0;
        color: #1c3c1c;
    }

    .row-pending {
        background-color: #fff4b2;
        color: #5f4b00;
    }

    .row-failed {
        background-color: #f5c6cb;
        color: #5b1a1a;
    }
</style>


@section('content')

    <div class="card">
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade in" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Bağla"><span
                                aria-hidden="true">&times;</span></button>
                    {{ session('success') }}
                </div>
            @endif
            <form method="GET" action="{{ route('pay-phone.index') }}" class="form-inline mb-3">
                <div class="form-group">
                    <input type="text" name="phone" class="form-control" placeholder="Telefon"
                           value="{{ request('phone') }}">
                </div>

                <div class="form-group">
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>

                <div class="form-group">
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>

                <div class="form-group">
                    <select name="status" class="form-control">
                        <option value="">-- Status --</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        <!-- Add more statuses if applicable -->
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Filtrlə</button>
                <a href="{{ route('pay-phone.index') }}" class="btn btn-default">Təmizlə</a>

                <a href="{{ route('pay-phone.export', request()->query()) }}" class="btn btn-success"
                   style="margin-left: 10px;">
                    Excelə Yüklə
                </a>
            </form>
                <br><br>

            <div class="row">
                <div class="col-xs-12">
                    <div class="box box-primary box-filter">
                        <div class="box-body">
                            <div class="form-group">
                                <p>Ümumi say: <b>{{ $pay_phone->total ?? 0 }}</b></p>
                                <a href="{{ route('pay-phone.create') }}" class="btn btn-success">+ Yeni əlavə et</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xs-12 clearfix"></div>

                <div class="col-xs-12">
                    <div class="box box-primary box-filter">
                        <div class="box-body">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Phone</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Tarix</th>
                                    <th>Order ID</th>
                                    <th>Link</th>
                                    <th>Əməliyyat</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($pay_phones as $pay_phone)
                                    @php
                                        switch ($pay_phone->status) {
                                            case 'success':
                                                $rowClass = 'row-success';
                                                break;
                                            case 'failed':
                                                $rowClass = 'row-failed';
                                                break;
                                            case 'pending':
                                                $rowClass = 'row-pending';
                                                break;
                                            default:
                                                $rowClass = '';
                                        }
                                    @endphp

                                    <tr class="{{ $rowClass }}">
                                        <td>{{ $pay_phone->id }}</td>
                                        <td>{{ $pay_phone->phone }}</td>
                                        <td>{{ $pay_phone->amount }}</td>
                                        <td>{{ ucfirst($pay_phone->status) }}</td>
                                        <td>{{ $pay_phone->created_at->format('d/m/Y H:i') }}</td>
                                        <td>{{ $pay_phone->order_id }}</td>
                                        <td>
            <span onclick="copyToClipboard(this)" style="cursor: pointer; color: blue;" title="Kopyala">
                {{ $pay_phone->redirect_url }}
            </span>
                                        </td>
                                        <td>
                                            <form action="{{ route('pay-phone.destroy', $pay_phone->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Silmək istədiyinizə əminsiniz?')">
                                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                <input type="hidden" name="_method" value="DELETE">
                                                <button type="submit" class="btn btn-danger btn-sm">Sil</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach


                                </tbody>
                            </table>
                        </div>

                        @if($pay_phones->hasPages())
                            <div class="box-footer">
                                {{ $pay_phones->appends( Request::all() )->links() }}
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
<script>
    function copyToClipboard(element) {
        const text = element.innerText;

        navigator.clipboard.writeText(text)
            .then(() => {
                // Kiçik bildiriş göstərə bilərsən
                alert('Kopyalandı: ' + text);
            })
            .catch(err => {
                alert('Kopyalanmadı: ' + err);
            });
    }
</script>
