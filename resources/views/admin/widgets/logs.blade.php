@extends(config('saysay.crud.layout'))
{{--<style>--}}
{{--    .wrap-text {--}}
{{--        display: inline-block; /* vacibdir */--}}
{{--        max-width: 100%; /* və ya konkret ölçü */--}}
{{--        white-space: normal;--}}
{{--        word-wrap: break-word;--}}
{{--        word-break: break-all; /* tam bölünsün deyə */--}}
{{--    }--}}
{{--</style>--}}
@section('content')
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1 col-md-12 col-xs-12">
            <div class="panel panel-flat">
                <div class="panel-heading">
                    <h6>
                        Package #{{ $id }}'s logs
                    </h6>
                </div>

                <div class="panel-body">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Date</th>
                            <th>Data</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if ($logs->count())
                            @foreach($logs as $log)
                                <tr>
                                    <td>
                                        @if($log->admin)
                                            <b>Admin</b> : {{ $log->admin->name }} <br/>
                                        @endif
                                        @if($log->user)
                                            <b>User</b> : {{ $log->user->full_name }} <br/>
                                        @endif
                                        @if($log->worker)
                                            <b>Worker</b> : {{ $log->worker->name }} <br/>
                                        @endif
                                        @if($log->courier)
                                            <b>Courier</b> : {{ $log->courier->name }} <br/>
                                        @endif
                                        <b>Date</b> : {{ $log->created_at }}
                                    </td>
                                    <td>{!! $log->data !!} </td>
                                </tr>
                            @endforeach

                        @else
                            <tr>
                                <td colspan="4">
                                    @include('crud::components.alert')
                                </td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>

                @if(isset($packageLogs) && !empty($packageLogs))
                    <div class="panel-heading">
                        <h6>
                            Carrier #{{ $id }}'s logs
                        </h6>
                    </div>
                    <div class="panel-body">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>Date</th>
                                <th>Data</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($packageLogs as $log)
                                <tr>
                                    <td>
                                        @if($log->admin)
                                            <b>Admin</b> : {{ $log->admin->name }}  <br/>
                                        @endif
                                        <b>Date</b> : {{ $log->created_at }}
                                    </td>
                                    <td>{!! $log->data !!}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endisset

                @if(isset($CourierShelfLog))
                    <div class="panel-heading">
                        <h6>
                            Courier shelf #{{ $id }}'s logs
                        </h6>
                    </div>
                    <div class="panel-body">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>Admin</th>
                                <th>Content</th>
                                <th>Date</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($CourierShelfLog as $log)
                                <tr>
                                    <td>{{ @$log->admin->name ?? @$log->courier->email }}</td>
                                    <td>{{ @$log->comment }}</td>
                                    <td>{{ @$log->created_at }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <div class="panel-footer">
                </div>
            </div>
        </div>
    </div>
@endsection
