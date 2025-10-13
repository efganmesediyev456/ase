<div class="section-space80">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                {!! Form::open(['route' => 'tracking', 'method' => 'get' ]) !!}
                    <div class="form-group">
                        <label for="trackingNumber" class="sr-only">{{ __('front.search_tracking') }}</label>
                        <input type="text" name="tracking_code" id="tracking_code" class="form-control" placeholder="{{ __('front.tracking_code') }}" required>
                    </div>
                    <button type="submit" class="btn btn-primary">{{ __('front.search') }}</button>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
