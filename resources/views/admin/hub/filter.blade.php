<form method="get" action="{{ route('azeriexpress.index') }}">
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

                        <div class="col-xs-12 col-sm-2">
                            <div class="form-group" style="padding: 28px 0 0;">
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

