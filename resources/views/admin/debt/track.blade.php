@extends(config('saysay.crud.layout'))

@section('content')
    <div class="col-lg-12 col-lg-offset-0 col-md-12 col-xs-12">
        <div class="panel panel-flat">

            <div class="panel-body">
                <div>
                    <h5><span>OZON/IHERB/TAOBAO</span></h5>
                </div>
                <form method="GET" action="{{ route('debt.track.index') }}" accept-charset="UTF-8" class="mr-15 mb-20" id="search_form" onsubmit="putDate(this)">

                    <div class="row">
                        <div class="col-lg-10">
                            <div class="row">

                                <!-- text input -->
                                <div class="col-lg-2">

                                    <input type="text" name="parcel" value="@if($request->parcel) {{ $request->parcel }} @endif" placeholder="Parcel id" class="form-control">

                                </div>                                    <!-- text input -->
                                <div class="col-lg-2">

                                    <input type="text" name="q" value="@if($request->q) {{ $request->q }} @endif" placeholder="Search..." class="form-control">

                                </div>                                    <!-- textarea -->
                                <div class="col-lg-2">

                                    <textarea name="tl" placeholder="Package # List..." class="form-control">@if($request->tl) {{ $request->tl }} @endif</textarea>

                                </div>
                                <!-- select2 -->
                                <div class="col-lg-2">
                                    <select name="status" class="form-control">
                                        <option value="" {{ request('status') === null || request('status') === '' ? 'selected' : '' }}>All Status</option>
                                        @foreach($statusLabels as $status)
                                            <option value="{{ $status['value'] }}" {{ request('status') !== null && request('status') !== '' && (string)request('status') === (string)$status['value'] ? 'selected' : '' }}>
                                                {{ $status['text'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-1">
                                    <select name="paid_debt" class="form-control">

                                        <option value="">Paid Debt</option>

                                        <option value="1" {{request()->paid_debt == 1 ? 'selected' : '' }}>Borcu ödənmişlər</option>
                                        <option value="2" {{request()->paid_debt == 2 ? 'selected' : '' }}>Borcu ödənməmişlər</option>
                                    </select>

                                </div>
{{--                                <div class="col-lg-1">--}}
{{--                                    <label for="alldone">All with done</label>--}}
{{--                                    <input type="checkbox" id="alldone" name="status" @if($request->status && $request->status == 17) checked @endif value="17">--}}
{{--                                </div>--}}
                                <div class="col-lg-2">
                                    <input class="datepicker-range-start" type="hidden" name="start_date"
                                           value="{{ request('start_date') }}">
                                    <input class="datepicker-range-end" type="hidden" name="end_date"
                                           value="{{ request('end_date') }}">

                                    <div class="input-group date">
                                        <button type="button" class="btn btn-danger filter-daterange-ranges legitRipple">
                                            <i class="icon-calendar22 position-left"></i>
                                            <span>
                @if(request('start_date') && request('end_date'))
                                                    {{ request('start_date') }} - {{ request('end_date') }}
                                                @else
                                                    All time
                                                @endif
            </span>
                                            <b class="caret"></b>
                                        </button>
                                    </div>
                                </div>


                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="row">
                                <div class="col-lg-7">
                                    <div class="btn-group">
                                        <a href="?" class="btn btn-warning btn-icon legitRipple"><i class="icon-close2"></i></a>
                                        <button name="search" class="btn btn-primary btn-icon legitRipple"><i class="icon-search4"></i></button>
                                        <a href="{{ route('debt.tracks.export') }}?items={{ $results->pluck('id')->implode(',') }}" class="btn btn-success btn-icon legitRipple"><i class="icon-file-download"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-hover responsive table-striped">
                        <thead>
                        <tr>
                            <th>No</th>
                            <th>Partner</th>
                            <th>CWB #</th>
                            <th>Track #</th>
                            <th>Parcel</th>
                            <th>Bag</th>
                            <th>User</th>
                            <th>Country</th>
                            <th>Weight</th>
                            <th>Delivery Price</th>
                            <th>Status</th>
                            <th>Kargo odenishi</th>
                            <th>Saxlanc məbləği</th>
                            <th>Saxlanc odenishi</th>
                            <th>Saxlanc STOP</th>
                            <th>Filial</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($results as $key => $track)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $track->partner_with_label }}</td>
                                <td>{{ $track->custom_id }}</td>
                                <td>{{ $track->tracking_code }}</td>
                                <td>{{ $track->container_name }}</td>
                                <td>{{ $track->airbox_name }}</td>
                                <td>{{ $track->fullname }}</td>
                                <td>{{ $track->from_country }}</td>
                                <td>{{ $track->weight }} kg</td>
                                <td>{{ $track->delivery_price }} $</td>
                                <td>{{ $track->status_with_label }} </td>
                                <td>{{ $track->paid == 1 ? 'Yes' : 'No' }}</td>
                                <td>{{ $track->debt_price }} ₼</td>
                                <td>{{ $track->paid_debt_att_with_label }}</td>
                                <td>{{ $track->stop_debt_att_with_label }}</td>
                                <td>{{ $track->filial_name }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot style="font-weight: 900;">
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>{{ $results->sum('weight') }} kg</td>
                            <td>{{ $results->sum('delivery_price') }} $</td>
                            <td></td>
                            <td></td>
                            <td>{{ $results->sum('debt_price') }} ₼</td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection

@push('js')
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-form-validator/2.3.26/jquery.form-validator.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
@endpush
