<div class="section-space20 bg-white lender-logo-section">
    <div class="container">
        <div class="row">
            @foreach($stores as $key => $store)
                @if($key < 6)
                    <div class="col-lg-2 col-md-2 col-sm-4 col-xs-4">
                        <div class="lender-box"><img alt="{{ $store->translateOrDefault($_lang)->name }}" src="{{ $store->logo }}"></div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>