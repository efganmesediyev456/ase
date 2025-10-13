@extends(config('saysay.crud.layout'))

@section('title', 'Precinct - Yeni Menteqe')

@section('content')
    <div class="card">
        <div class="card_body">
            @if($offices->count() > 0)
                <div class="box box-primary mt-15">
                    <div class="box-body table-responsive no-padding">
                        <form method="post" action="{{ route('precinct.index') }}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="_method" value="delete">
                            <table class="table table-hover table-striped">
                                <tr>
                                    <th>Id</th>
                                    <th>Ad</th>
                                    <th>Qeyd</th>
                                    <th>Unvan</th>
                                    <th>Yaradılıb</th>
                                    <th></th>
                                </tr>
                                @foreach($offices as $row)
                                    <tr>
                                        <td>{{ $row->id }}</td>
                                        <td>{{ $row->name }}</td>
                                        <td>{{ $row->description }}</td>
                                        <td>{{ $row->address }}</td>
                                        <td>{{ $row->created_at }}</td>
                                        <td>
                                            <button class="btn btn-primary" type="button" disabled>
                                                <i class="icon-pencil"></i>
                                            </button>
                                            <button class="btn btn-danger" type="button" disabled>
                                                <i class="icon-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </form>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="text-center pull-left">
                                    {{$offices->links()}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
