@extends(config('saysay.crud.layout'))

@section('title', 'Bulk Resend Status Requests')

@section('content')
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
                                <textarea style="width: 700px; !important;" name="requestText" id="requestText" rows="10" class="form-control" required></textarea>
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
@endsection
