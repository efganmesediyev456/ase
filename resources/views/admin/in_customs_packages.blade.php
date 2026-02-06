@extends(config('saysay.crud.layout'))

@section('content')
@if(!empty($alertText))
<div class="alert alert-{{$alertType}}" role="alert">
  {{$alertText}}
</div>
@endif
    <div class="row">
        <div class="col-lg-12 col-lg-offset-0 col-md-12 col-xs-12">
            <div class="panel panel-flat" >

                <div class="panel-heading">
                    <h6>
			{{ isset($_view['name']) ? str_plural($_view['name']) : null }}
			<small class="display-block"> Showing {{ $packages->firstItem() }} to {{ $packages->lastItem() }}
                            of {{ number_format($packages->total()) }} {{ $_view['sub_title'] or lcfirst(str_plural($_view['name'])) }}</small>
			@if($set_count) <br><b>{{$set_count}} Packages are set </b>@endif
			@if($unset_count) <br><b>{{$unset_count}} Packages are unset </b>@endif
                    </h6>
                </div>

                <div class="panel-body">
                    @include('crud::inc.filter-stack')


                    @permission('update-tracks')
                    {!! Form::open(['id' => 'in_customs_tracks_packages', 'method' => 'post', 'route' => $_view['mod_name']."s.set" ]) !!}

                    <div class="col-md-4">
                        <label><b style='color:red'>SET</b> Check Customs Package List:</label>
                        <div class="input-group">
        <textarea id="set_tracks" rows=10 name="set_tracks" style='width: 300px;'
                  class="form-control">{{$set_count?'':$set_tracks}}</textarea>
                        </div>

                        <!-- Yeni: Tick Box'lar -->
                        <div style="margin-top: 15px; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                            <label><b>Select Type:</b></label>
                            <div style="margin-top: 10px;">
                                <div class="checkbox">
                                    <label>
                                        <input type="radio" name="note_type" value="smart" class="note-type-radio">
                                        <span style="color: #0066cc;"><b>Smart</b></span>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="radio" name="note_type" value="say" class="note-type-radio">
                                        <span style="color: #0066cc;"><b>Say</b></span>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="radio" name="note_type" value="mutemadi" class="note-type-radio">
                                        <span style="color: #0066cc;"><b>Mutemadi</b></span>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="radio" name="note_type" value="price" class="note-type-radio">
                                        <span style="color: #0066cc;"><b>Price</b></span>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="radio" name="note_type" value="sehv beyan" class="note-type-radio">
                                        <span style="color: #0066cc;"><b>Səhv Bəyan</b></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label><b style='color:green'>UNSET</b> Check Customs Package List:</label>
                        <div class="input-group">
        <textarea id="unset_tracks" rows=10 name="unset_tracks"  style='width: 300px;'
                  class="form-control">{{$unset_count?'':$unset_tracks}} </textarea>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <button type="submit" title="Update" id="in_customs_tracks_form_button" class="btn btn-primary btn-icon" style="margin-top: 28px;">
                            <i class="icon-loop"></i>
                        </button>
                    </div>

                    {!! Form::close() !!}
                    @endpermission


                    <div class="table-responsive overflow-visible" style="padding-top: 70px;">
                        <table class="table table-hover responsive table-striped">
                            <thead>
                            <tr>
                                @foreach($_list as $key => $head)
                                    <th>{{ is_array($head) ? (array_key_exists('label', $head) ? $head['label'] : ucfirst(str_replace("_", " ", $key))) : ucfirst(str_replace("_", " ", $head)) }}</th>
                                @endforeach
                                <th></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody id="scanned" >
                            @foreach($packages as $track)
                                @include('admin.widgets.track', ['item' => $track])
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="heading-elements">
                        @if (isset($_view['checklist']) and is_array($_view['checklist']))
                            <div class="btn-group">
                                @foreach($_view['checklist'] as $button)
                                    <button data-route="{{ route($button['route']) }}"
                                            data-value="{{ $button['value'] }}"
                                            data-key="{{ $button['key'] }}" type="button"
                                            data-loading-text="<i class='icon-spinner4 spinner position-left'></i> Loading"
                                            class="btn btn-{{ isset($button['type']) ? $button['type'] : 'info' }} btn-loading do-list-action">
                                        <i class="icon-{{ isset($button['icon']) ? $button['icon'] : 'spinner4' }} position-left"></i>
                                        {{ $button['label'] }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                        <div class="pull-right">
                            <div>{!! $packages->appends(Request::except('page'))->links() !!}</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection






@push('js')
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-form-validator/2.3.26/jquery.form-validator.min.js"></script>
    <script>
        $.validate();

        $('#in_customs_tracks_form').on('submit', function(e) {
            var setTracks = $('#set_tracks').val().trim();
            var noteType = $('input[name="note_type"]:checked').val();

            if (setTracks && !noteType) {
                e.preventDefault();
                alert('Please select a type (Smart, Say, Mutemadi, or Price)');
                return false;
            }
        });

        $('.note-type-radio').on('change', function() {
        });
    </script>
@endpush

