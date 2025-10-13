<form method="get" action="{{ route('precinct.index') }}">
    <div class="box box-primary box-filter">
        <div class="box-body">
            <div class="row">
                <div class="col-xs-12">
                    <div class="row">
                        <div class="col-xs-12 col-md-3">
                            <div class="form-group">
                                <label>Bağlama barkodu</label>
                                <input type="text" name="code" value="{{ Request::input('code') }}"
                                       class="form-control">
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-3">
                            <div class="form-group">
                                <label>Ş/v-nin fini</label>
                                <input type="text" name="passport_fin"
                                       value="{{ Request::input('passport_fin') }}"
                                       class="form-control">
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-3">
                            <div class="form-group">
                                <label>Müştəri Nömrəsi</label>
                                <input type="text" name="c_code" value="{{ Request::input('c_code') }}"
                                       class="form-control">
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-3">
                            <div class="form-group">
                                <label for="office">Ofis</label>
                                <select name="office" id="office" class="form-control">
                                    <option value="">Hamısı</option>
                                    @foreach($offices as $office)
                                        <option value="{{ $office->id }}" {{ request()->filled('office') && request()->get('office') == $office->id ? 'selected' : '' }}>
                                            {{ $office->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-xs-12 col-md-3">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status[]" class="form-control" multiple>
                                    <option value="">Hamısı</option>
                                    @foreach($statuses as $key => $status)
                                        @if($key !== 'WAITING' && $key !== 'IN_PROCESS')
                                            <option value="{{ $status }}" {{ is_array(request()->get('status')) && in_array($status, request()->get('status')) ? 'selected' : '' }}>
                                                {{ __('admin.precinct_warehouse_package_status_'.$status) }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-xs-12 col-md-2">
                            <div class="form-group">
                                <label>Tarix</label>
                                <select name="by_accepted_at" id="by_accpeted_at" class="form-control">
                                    <option value="">-</option>
                                    <option value="1"  {{ request()->filled('by_accepted_at') ? 'selected' : '' }}>Məntəqəyə çatıb</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-xs-12 col-md-2">
                            <div class="form-group">
                                <label>Başlanğıç arix</label>
                                <input type="date" name="start_date"
                                       value="{{ Request::input('start_date') }}"
                                       class="form-control">
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-2">
                            <div class="form-group">
                                <label>Son tarix</label>
                                <input type="date" name="end_date"
                                       value="{{ Request::input('end_date') }}"
                                       class="form-control">
                            </div>
                        </div>

                        <div class="col-xs-12 col-md-3">
                            <div class="form-group" style="padding: 28px 0 0;">
                                <button type="submit" name="export" value="1" class="btn btn-primary mr-5">Export
                                </button>
                                <button class="btn btn-primary" type="submit"><i class="icon-search4"></i>
                                </button>
                                <a href="?action=reset" class="btn btn-danger">
                                    <i class="icon-trash"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

