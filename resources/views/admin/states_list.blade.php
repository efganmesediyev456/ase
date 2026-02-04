@extends(config('saysay.crud.layout'))

@section('content')
    <style>
        .text-success {
            background: #d4edda;
            color: #155724 !important;
            padding: 2px 6px;
            border-radius: 3px;
        }
        .text-danger {
            background: #f8d7da;
            color: #721c24 !important;
            padding: 2px 6px;
            border-radius: 3px;
        }
    </style>
    <div class="container mt-4">
        <h2>Track Status History - {{ $track->tracking_code }}</h2>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <!-- Status Geçmişi Tablosu -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Status History</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Note</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($statuses as $status)
                            <tr class="{{ empty($status['note']) || str_contains(strtolower($status['note']), 'ok') ? 'text-success' : 'text-danger' }}">
                                <td>{{ $status['status'] }}</td>
                                <td>{{ $status['date'] }}</td>
                                <td class="">
                                    {{ $status['note'] ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">No status history</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Yeni Status Seçim Formu -->
        <div class="card">
            <div class="card-header">
                <h5>Update Status</h5>
            </div>
            <div class="card-body">
                <form action="" method="POST">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="form-group mb-3">
                        <label for="status">Select New Status</label>
                        <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                            <option value="">-- Choose Status --</option>
                            @foreach($statusOptions as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                    <a href="" class="btn btn-secondary">Back</a>
                </form>
            </div>
        </div>
    </div>
@endsection
