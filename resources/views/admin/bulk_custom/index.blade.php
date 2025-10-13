@extends(config('saysay.crud.layout'))

@section('title', 'Bulk Custom Requests')


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
                    <h6 class="panel-title">Bulk Custom Request</h6>
                </div>

                <div class="panel-body">
                    <form action="{{route('bulk_customs.store')}}" method="POST">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        <div class="col-md-6">
                            <label><b>Enter Request Text</b></label>
                            <div class="input-group">
                                <textarea style="width: 700px; !important;" name="requestText" id="requestText" rows="10" class="form-control" required></textarea>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label><b>Select Action</b></label>
                            <div class="input-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="action_type" value="reset" onclick="onlyOne(this)"> Custom reset
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="action_type" value="delete" onclick="onlyOne(this)"> Custom delete
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="action_type" value="check" onclick="onlyOne(this)"> Custom check
                                    </label>
                                </div>
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
