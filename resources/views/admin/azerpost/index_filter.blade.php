<form method="get" action="{{ route('azerpost.index') }}">
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
                                <input type="text" name="phone" value="{{ Request::input('phone') }}"
                                       class="form-control">
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-3">
                            <div class="form-group">
                                <label>Rayon</label>
                                <select name="region" class="form-control">
                                    <option value="">Hamısı</option>
                                    <option value="0" {{ request()->filled('region') && request()->get('region') == 0 ? 'selected' : '' }}> Yasamal </option>
                                    <option value="1" {{ request()->filled('region') && request()->get('region') == 1 ? 'selected' : '' }}> Nesimi </option>
                                </select>
                            </div>
                        </div>

                        <div class="col-xs-12 col-md-3">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="">Hamısı</option>
                                    @foreach($statuses as $key => $status)
                                        <option value="{{ $status }}" {{ request()->filled('status') && request()->get('status') == $status ? 'selected' : '' }}>
                                            {{ __('admin.azerpost_warehouse_package_status_'.$status) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-xs-12 col-md-3">
                            <div class="form-group">
                                <label>Başlanğıç arix</label>
                                <input type="date" name="start_date"
                                       value="{{ Request::input('start_date') }}"
                                       class="form-control">
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-3">
                            <div class="form-group">
                                <label>Son tarix</label>
                                <input type="date" name="end_date"
                                       value="{{ Request::input('end_date') }}"
                                       class="form-control">
                            </div>
                        </div>

                        <div class="col-xs-12 col-sm-2">
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

