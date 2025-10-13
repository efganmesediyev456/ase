@if (! $item)
    <div class="alert alert-warning">
        {{ __('front.package_not_found') }}
    </div>
@else
    <div class="declaration">

        <div class="row">
            <div class="col-lg-12">
                {{ Form::open(['class' => 'loan-eligibility-form', 'files' => true]) }}

                @include('front.form.group', ['key' => 'tracking_code', 'label' => trans('front.tracking_code'), 'options' => ['class' => 'form-control', 'data-validation' => 'required length custom', 'data-validation-length' => "min9", 'data-validation-regexp' => "^[A-Za-z0-9-]+$"]])
                @include('front.form.group', ['key' => 'website_name', 'label' => trans('front.website_name'), 'options' => ['class' => 'form-control', 'placeholder' => trans('front.website_example'), 'data-validation' => 'required']])
                @include('front.form.group', ['type' => 'select', 'key' => 'type_id', 'label' => trans('front.product_category'), 'selects' => $categories, 'options' => ['id' => 'type_id', 'empty' => true, 'class' => 'form-control select']])
                <div id="other_type" style="margin-top: -30px; @if($item->other_type == null) display: none; @endif">
                    @include('front.form.group', ['key' => 'other_type', 'label' => '&nbsp;', 'options' => ['class' => 'form-control', 'placeholder' => trans('front.other_placeholder'), 'data-validation' => "alphanumeric", 'data-validation-allowing' => " "]])
                </div>
                <div class="row">
                    <div class="col-sm-12 col-lg-5">
                        @include('front.form.group', ['key' => 'shipping_amount', 'label' => trans('front.shipping_amount'), 'options' => ['class' => 'form-control', 'data-validation' => 'required number', 'data-validation-allowing' => "float"]])
                    </div>
                    <div class="col-sm-12 col-lg-3">
                        @include('front.form.group', ['type' => 'select', 'key' => 'shipping_amount_cur', 'label' => trans('front.shipping_amount_cur'), 'selects' => config('ase.attributes.currencies'), 'options' => ['class' => 'form-control']])
                    </div>
                    <div class="col-sm-12 col-lg-4">
                        @include('front.form.group', ['key' => 'number_items', 'label' => trans('front.number_items'), 'options' => ['class' => 'form-control', 'data-validation' => 'required number', 'data-validation-allowing' => "range[1;10000]"]])
                    </div>
                </div>

                <div class="form-group">
                    @if ($item->invoice)
                        <p>{{ __('front.already_invoice_uploaded') }}
                            <a target="_blank" href="{{ $item->invoice }}">{{ __('front.click_here') }}</a>
                        </p>
                    @endif
                    <label>{{ __('front.invoice') }}</label>
                    <input name="invoice" type="file" class="form-control-file"
                           data-validation="@if(! $item->invoice)required @endif mime size"
                           data-validation-allowing="jpg, png, pdf, doc, docx, jpeg"
                           data-validation-max-size="3M"/>
                    @if ($errors->has('invoice'))
                        <span class="help-block">
                            <strong>{!! $errors->first('invoice') !!}</strong>
                        </span>
                    @endif
                </div>

                <div class="form-group mt30">
                    <div class="col-sm-12 text-center">
                        <button type="submit" class="btn btn-default">{{ __('front.save') }}</button>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
        {!! Form::close() !!}
    </div>

    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-form-validator/2.3.26/jquery.form-validator.min.js"
            defer></script>
    @if(in_array(app()->getLocale(), ['az', 'ru']))
        <script src="{{ asset('langs/form/' . app()->getLocale() . '.js') }}?v=1.0.0"></script>
    @endif
    <script>
        $(document).ready(function () {
            $('.select').select2();

            var other_type = $("#other_type");
            $("#type_id").on('change', function () {
                var id = $(this).val();
                if (id == <?= env('OTHER_ID', 10) ?>) {
                    other_type.show();
                } else {
                    other_type.hide();
                }
            })

        });
        $.validate({
            modules : 'file'
            @if(in_array(app()->getLocale(), ['az', 'ru']))
            , language: myLanguage
            @endif
        });
    </script>
@endif