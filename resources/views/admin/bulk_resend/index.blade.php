@extends(config('saysay.crud.layout'))

@section('title', 'Bulk Resend Status Requests')

@section('content')

    <style>
        .badge {
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
        }

        .badge-success {
            background-color: #28a745;
            color: white;
        }

        .badge-danger {
            background-color: #dc3545;
            color: white;
        }

        .badge-warning {
            background-color: #ffc107;
            color: black;
        }

        .badge-info {
            background-color: #17a2b8;
            color: white;
        }

        .badge-default {
            background-color: #6c757d;
            color: white;
        }

        pre {
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 4px;
            font-size: 12px;
        }

        .mb-3 {
            margin-bottom: 15px;
        }

        .form-control {
            width: 100%;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

    </style>
    @if(session('success'))
        <div class="alert alert-success" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="row">
        <div class="col-lg-12 col-md-12 col-xs-12">
            <div class="panel panel-flat">
                <div class="panel-heading">
                    <h6 class="panel-title">Bulk Resend Statuses Request</h6>
                </div>

                <div class="panel-body">
                    <form action="{{ route('bulk_resend_statuses.store') }}" method="POST">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        <div class="col-md-6">
                            <label><b>Enter Request Text</b></label>
                            <div class="input-group">
                                <textarea style="width: 700px; !important;" name="requestText" id="requestText"
                                          rows="10" class="form-control" required></textarea>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label><b>Enter Status Date and Time (Optional)</b></label>
                            <div class="input-group">
                                <input
                                        type="datetime-local"
                                        name="date"
                                        id="statusDate"
                                        class="form-control"
                                        style="width: 700px; !important;"
                                >
                            </div>
                        </div>

                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary" style="margin-top: 28px;">Submit</button>
                        </div>


                        <div class="col-md-6 mt-4" style="margin-top:40px">
                            <label><b>Status</b></label>
                            <div class="input-group">
                                <select name="status" id="" class="form-control">
                                    @foreach($statuses as $key=>$status)
                                        <option value="{{ $key }}">{{ $status }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <script>
        function onlyOne(selected) {
            document.querySelectorAll('input[name="action_type"]').forEach(function (checkbox) {
                if (checkbox !== selected) checkbox.checked = false;
            });
        }
    </script>



    <div class="row" style="margin-top: 40px;">
        <div class="col-lg-12 col-md-12 col-xs-12">
            <div class="panel panel-flat">
                <div class="panel-heading">
                    <h6 class="panel-title">
                        Track Status Logs
                        <span class="badge badge-info" style="float: right;">{{ $logs->count() }} Records</span>
                    </h6>
                </div>


                <div class="row" style="margin-top: 40px;">
                    <div class="col-lg-12 col-md-12 col-xs-12">
                        <div class="panel panel-flat">
                            <div class="panel-heading">
                                <h6 class="panel-title">Filters</h6>
                            </div>

                            <div class="panel-body">
                                <form action="{{ route('bulk_resend_statuses.index') }}" method="GET">
                                    <div class="row">
                                        <!-- Satır 1 -->
                                        <div class="col-md-4 col-sm-6 col-xs-12 mb-3">
                                            <label class="mr-2"><b>Log Type:</b></label>
                                            <select name="log_type" class="form-control">
                                                <option value="">-- All Types --</option>
                                                @foreach($logTypes as $type)
                                                    <option value="{{ $type }}" {{ request('log_type') === $type ? 'selected' : '' }}>
                                                        {{ ucfirst($type) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-4 col-sm-6 col-xs-12 mb-3">
                                            <label class="mr-2"><b>Status:</b></label>
                                            <select name="status" class="form-control">
                                                <option value="">-- All Status --</option>
                                                @foreach($statuses as $key => $status)
                                                    <option value="{{ $key }}" {{ request('status') === (string)$key ? 'selected' : '' }}>
                                                        {{ $status }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-4 col-sm-6 col-xs-12 mb-3">
                                            <label class="mr-2"><b>Tracking Code:</b></label>
                                            <input type="text" name="tracking_code" class="form-control"
                                                   placeholder="Search..."
                                                   value="{{ request('tracking_code') }}">
                                        </div>

                                        <!-- Satır 2 -->
                                        <div class="col-md-4 col-sm-6 col-xs-12 mb-3">
                                            <label class="mr-2"><b>Executed At (From):</b></label>
                                            <input type="date" name="executed_at_from" class="form-control"
                                                   value="{{ request('executed_at_from') }}">
                                        </div>

                                        <div class="col-md-4 col-sm-6 col-xs-12 mb-3">
                                            <label class="mr-2"><b>Executed At (To):</b></label>
                                            <input type="date" name="executed_at_to" class="form-control"
                                                   value="{{ request('executed_at_to') }}">
                                        </div>

                                        <div class="col-md-4 col-sm-6 col-xs-12 mb-3">
                                            <label class="mr-2">&nbsp;</label>
                                            <div>
                                                <button type="submit" class="btn btn-primary">Filter</button>
                                                <a href="{{ route('bulk_resend_statuses.index') }}"
                                                   class="btn btn-default">Reset</a>
                                            </div>
                                        </div>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>


                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="logsTable">
                            <thead>
                            <tr>
                                <th style="width: 5%;">ID</th>
                                <th style="width: 10%;">Tracking Code</th>
                                <th style="width: 8%;">Status</th>
                                <th style="width: 10%;">Place</th>
                                <th style="width: 10%;">Event Code</th>
                                <th style="width: 8%;">HTTP Code</th>
                                <th style="width: 10%;">Type</th>
                                <th style="width: 12%;">User</th>
                                <th style="width: 12%;">Executed At</th>
                                <th style="width: 15%;">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($logs as $log)
                                <tr class="log-row" data-log-type="{{ $log->log_type }}"
                                    data-tracking-code="{{ $log->tracking_code }}">
                                    <td>{{ $log->id }}</td>
                                    <td>
                                        <strong>{{ $log->tracking_code }}</strong>
                                    </td>
                                    <td>

                                        <span class="badge badge-default">{{ $statuses[$log->status] ?? '-' }}</span>
                                    </td>
                                    <td>{{ $log->place ?? '-' }}</td>
                                    <td>{{ $log->event_code ?? '-' }}</td>
                                    <td>
                                        @if($log->http_code)
                                            @if($log->http_code >= 200 && $log->http_code < 300)
                                                <span class="badge badge-success">{{ $log->http_code }}</span>
                                            @elseif($log->http_code >= 400)
                                                <span class="badge badge-danger">{{ $log->http_code }}</span>
                                            @else
                                                <span class="badge badge-warning">{{ $log->http_code }}</span>
                                            @endif
                                        @else
                                            <span class="badge badge-default">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->log_type === 'success')
                                            <span class="badge badge-success">{{ ucfirst($log->log_type) }}</span>
                                        @elseif($log->log_type === 'error')
                                            <span class="badge badge-danger">{{ ucfirst($log->log_type) }}</span>
                                        @elseif($log->log_type === 'warning')
                                            <span class="badge badge-warning">{{ ucfirst($log->log_type) }}</span>
                                        @else
                                            <span class="badge badge-info">{{ ucfirst($log->log_type) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $log->user->name ?? '-' }}</td>
                                    <td>
                                        <small>{{ $log->executed_at ? $log->executed_at->format('Y-m-d H:i:s') : '-' }}</small>
                                    </td>
                                    <td>
                                        <button class="btn btn-xs btn-info" data-toggle="modal"
                                                data-target="#detailsModal{{ $log->id }}" title="View Details">
                                            <i class="icon-eye"></i> Details
                                        </button>
                                    </td>
                                </tr>

                                <!-- Details Modal -->
                                <div class="modal fade" id="detailsModal{{ $log->id }}" tabindex="-1" role="dialog">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal"
                                                        aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                                <h5 class="modal-title">Log Details - {{ $log->tracking_code }}</h5>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p><strong>Track ID:</strong> {{ $log->track_id }}</p>
                                                        <p><strong>Tracking Code:</strong> {{ $log->tracking_code }}</p>
                                                        <p><strong>Status:</strong> {{ $statuses[$log->status] ?? '-' }}
                                                        </p>
                                                        <p><strong>Status
                                                                String:</strong> {{ $log->status_string ?? '-' }}</p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p><strong>Place:</strong> {{ $log->place ?? '-' }}</p>
                                                        <p><strong>Event Code:</strong> {{ $log->event_code ?? '-' }}
                                                        </p>
                                                        <p><strong>HTTP Code:</strong> {{ $log->http_code ?? '-' }}</p>
                                                        <p><strong>Log Type:</strong> <span
                                                                    class="badge badge-{{ $log->log_type === 'success' ? 'success' : ($log->log_type === 'error' ? 'danger' : 'info') }}">{{ ucfirst($log->log_type) }}</span>
                                                        </p>
                                                    </div>
                                                </div>

                                                <hr>

                                                @if($log->error_message)
                                                    <div class="alert alert-danger">
                                                        <strong>Error Message:</strong>
                                                        <p>{{ $log->error_message }}</p>
                                                    </div>
                                                @endif

                                                @if($log->request_body)
                                                    <div class="form-group">
                                                        <label><strong>Request Body:</strong></label>
                                                        <pre class="bg-light p-3"
                                                             style="max-height: 300px; overflow-y: auto;">{{ json_encode(json_decode($log->request_body), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                    </div>
                                                @endif

                                                @if($log->response_body)
                                                    <div class="form-group">
                                                        <label><strong>Response Body:</strong></label>
                                                        <pre class="bg-light p-3"
                                                             style="max-height: 300px; overflow-y: auto;">{{ json_encode(json_decode($log->response_body), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                    </div>
                                                @endif

                                                <hr>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p><strong>Admin ID:</strong> {{ $log->user->name ?? '-' }}</p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p><strong>Executed
                                                                At:</strong> {{ $log->executed_at ? $log->executed_at->format('Y-m-d H:i:s') : '-' }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                    Close
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted">
                                        <em>No logs found yet</em>
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($logs->hasPages())
                        <div class="row">
                            <div class="col-md-12">
                                {{ $logs->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function onlyOne(selected) {
            document.querySelectorAll('input[name="action_type"]').forEach(function (checkbox) {
                if (checkbox !== selected) checkbox.checked = false;
            });
        }
    </script>

@endsection
