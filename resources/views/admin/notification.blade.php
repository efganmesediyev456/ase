@extends(config('saysay.crud.layout'))

@section('title', 'Azerpost - Notification Queue')

@section('content')
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1 col-md-12 col-xs-12">
            <div class="panel panel-flat">
                <div class="panel-heading">
                    <h6>
                        Notification Queue
                        <small class="display-block"> Showing {{ $queues->firstItem() }} to {{ $queues->lastItem() }}
                            of {{ number_format($queues->total()) }} </small>

                    </h6>
                </div>

                {!! Form::open(['method' => 'GET', 'route' => 'notifications.index', 'class' => 'mb-4']) !!}
                <div class="panel-body">
                    <div class="row g-3">

                        <div class="col-md-3">
                            <label for="sent" class="form-label">Sent Status</label>
                            <select name="sent" id="sent" class="form-control">
                                <option value="">All</option>
                                <option value="1" {{ request('sent') == '1' ? 'selected' : '' }}>Göndərilib</option>
                                <option value="0" {{ request('sent') == '0' ? 'selected' : '' }}>Göndərilməyib</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="type" class="form-label">Type</label>
                            <select name="type" id="type" class="form-control">
                                <option value="">All</option>
                                <option value="SMS" {{ request('type') == 'SMS' ? 'selected' : '' }}>SMS</option>
                                <option value="EMAIL" {{ request('type') == 'EMAIL' ? 'selected' : '' }}>EMAIL</option>
                                <option value="MOBILE" {{ request('type') == 'MOBILE' ? 'selected' : '' }}>MOBILE</option>
                                <option value="WHATSAPP" {{ request('type') == 'WHATSAPP' ? 'selected' : '' }}>WHATSAPP</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="number" class="form-label">Number (To)</label>
                            <input type="text" name="number" id="number" class="form-control" value="{{ request('number') }}">
                        </div>
                        <div class="col-md-3" style="margin-bottom: 40px;margin-top: 30px">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('notifications.index') }}" class="btn btn-secondary">Clear</a>
                        </div>
            {{--            <div class="col-md-3">--}}
            {{--                <label for="search" class="form-label">Search</label>--}}
            {{--                <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}">--}}
            {{--            </div>--}}
                    </div>
{{--                    <div class="mt-3" style="margin-bottom: 20px;">--}}
{{--                        <button type="submit" class="btn btn-primary">Filter</button>--}}
{{--                        <a href="{{ route('notifications.index') }}" class="btn btn-secondary">Clear</a>--}}
{{--                    </div>--}}
                </div>
                {!! Form::close() !!}
                <div class="panel-body">
                    @include('crud::inc.filter-stack')

                    <div class="table-responsive" style="padding-bottom: 140px">
                        <table class="table table-hover responsive table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User ID</th>
                                    <th>Type</th>
                                    <th>To</th>
                                    <th>Sent</th>
                                    <th>Send For</th>
                                    <th>Created At</th>
                                    <th>Content</th>
                                    <th>Error Message</th>
                                </tr>
                                </thead>
                    <tbody>
                    @foreach($queues as $queue)
                        <tr>
                            <td>{{ $loop->iteration + ($queues->currentPage() - 1) * $queues->perPage() }}</td>
                            <td>{{ $queue->user_id }}</td>
                            <td>{{ $queue->type }}</td>
                            <td>{{ $queue->to }}</td>

                            <td>{{ $queue->sent ? 'Göndərilib' : 'Göndərilməyib' }}</td>
                            <td>{{ $queue->send_for }}</td>
                            <td>{{ $queue->created_at }}</td>
                            <td style=" word-wrap: break-word">
                                @if($queue->type == 'WHATSAPP')
                                    @php
                                        $content = json_decode($queue->content);
                                    @endphp
                                    {{ $content->whatsapp }}
                                @else
                                    {{ $queue->content }}
                                @endif
                            </td>
                            <td>{{ $queue->error_message }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                    </div>
                </div>
                <div class="panel-footer">

                    <!-- Pagination Links -->
                    <div class="d-flex justify-content-center">
                        {{ $queues->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
