
<script>
    var categories = JSON.parse({!!json_encode(  App\Models\CustomsType::select('id','parent_id','name_'.\App::getLocale())->orderBy('name_'.\App::getLocale())->where('parent_id','!=',0)->get()->toJSon()  ) !!});
</script>
<script src="{{ asset('front/js/ctypes.js') }}?v=1.0.1.4"></script>
<div class="declaration">

    <div id="battery_invisible" style="display: none">
    </div>
    <div id="otp_invisible" style="display: none">
    </div>

    <div class="row">
        <div class="col-lg-12">

            {{ Form::open(['class' => 'loan-eligibility-form', 'files' => true]) }}
            <div class="input-grid-esi">
                <div >
                    <div style="position: relative;" class=" has-feedback has-feedback-left {{ $errors->has('country_id') ? 'has-error' : '' }}">
                        <label for="country_id" style="position: absolute; top: 16px; left: 24px; font-size: 12px; color: #000; font-weight: 600;  z-index: 1;">{{trans('front.warehouse')}}</label>
                        <select title="country_id" name="country_id" id="country_id"
                                style="border-radius: 8px; width: 100%; height: 86px; padding: 32px 0 16px 24px;  margin: 0 !important; appearance: none; border:1px solid #dedede;outline: none; box-shadow: none"
                                class="form-control" required>
                            <option value="">
                                {{trans('front.warehouse')}}

                            </option>
                            @foreach($countries as $key=> $country)


                                <option @if (old('country') == $key) selected
                                        @endif value="{{ $key }}">
                                    {{$country}}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control-feedback">
                        <i class="icon-location4 text-muted"></i>
                    </div>
                    @if ($errors->has('country_id'))
                        <label id="country_id-error" class="validation-error-label"
                               for="country_id">{{ $errors->first('country_id') }}</label>
                    @endif
                </div>
                <div style="position: relative;" class=" has-feedback has-feedback-left {{ $errors->has('shipping_amount_cur') ? 'has-error' : '' }}">
                    <label for="shipping_amount_cur" style="position: absolute; top: 16px; left: 24px; font-size: 12px; color: #000; font-weight: 600;  z-index: 1;">{{trans('front.shipping_amount_cur')}}</label>
                    <select title="shipping_amount_cur" name="shipping_amount_cur" id="shipping_amount_cur"
                            style="border-radius: 8px; width: 100%; height: 86px; padding: 32px 0 16px 24px;  margin: 0 !important; appearance: none; border:1px solid #dedede;outline: none; box-shadow: none"
                            class="form-control" required>
                        <option value="">
                            {{trans('front.shipping_amount_cur')}}
                            {{--                              @php  echo '<pre> 1' ;print_r(config('ase.attributes.currencies')) @endphp--}}
                        </option>

                        @foreach(config('ase.attributes.currencies') as $key=> $val)
                            <option @if (old('val') == $key) selected
                                    @endif value="{{ $key }}">
                                {{$val}}
                            </option>
                        @endforeach
                    </select>
                </div>



                {{--                @include('front.form.group', ['type' => 'select', 'key' => 'shipping_amount_cur',  'selects' => config('ase.attributes.currencies'), 'options' => ['class' => 'form-control']])--}}
                {{--                @include('front.form.group', ['type' => 'select', 'key' => 'country_id', 'selects' => $countries, 'options' => ['class' => 'form-control','data-validation' => "required"]])--}}
            </div>
            <div id="show_dec_message">
                <div class="alert alert-danger">{{ trans('front.no_need_declaration') }}</div>
            </div>
            <div id="dec_content">
                @include('front.form.group', ['key' => 'tracking_code', 'label' => trans('front.tracking_code'), 'options' => ['class' => 'form-control', 'data-validation' => 'required length custom', 'data-validation-length' => "min6", 'data-validation-regexp' => "^[A-Za-z0-9-]+$"]])
                @include('front.form.group', ['key' => 'website_name', 'label' => trans('front.website_name'), 'options' => ['class' => 'form-control', 'placeholder' => trans('front.website_example'), 'data-validation' => 'required length', 'data-validation-length' => "min3"]])
                <div id="package_goods">
                    <div id="package_good"  class="col-sm-12 col-lg-12" >

                        <div id="other_type" style="margin-top: -30px; margin-botom:10px; display: none">
                            @include('front.form.group', ['key' => 'other_type', 'label' => '&nbsp;', 'options' => ['class' => 'form-control', 'placeholder' => trans('front.other_placeholder'), 'data-validation' => "alphanumeric", 'data-validation-allowing' => " "]])
                        </div>
                        <div id="pkg_goods" style="margin-top: -30px; margin-botom:10px; display: none">
                            @include('front.form.group', ['key' => 'pkg_goods', 'label' => '&nbsp;','value'=>'1', 'options' => ['class' => 'form-control', 'placeholder' => trans('front.other_placeholder'), 'data-validation' => "alphanumeric", 'data-validation-allowing' => " "]])
                        </div>

                    </div>
                </div>
                <div>
                    <div style="background: #fff; min-height: 120px" class="div-input-login4 {{ $errors->has(str_replace(['[', ']'], ['.', ''], 'user_comment')) ? ' has-error' : '' }}">
                        <label for="user_comment">{{ trans('front.note') }}</label>
                        <textarea
                                name="user_comment"
                                id="user_comment"
                                rows="2"
                                class="form-control"
                                style="background: white; outline: none !important; box-shadow: none !important; padding: 0"
                        >{{ old('user_comment') ?: (\Request::has('user_comment') ? \Request::get('user_comment') : (isset($item) ? $item->user_comment : '')) }}</textarea>
                    </div>
                    <div class="form-control-feedback">
                        <i class="icon-user text-muted"></i>
                    </div>
                    @if ($errors->has('user_comment'))
                        <label style="font-weight: 500; font-size: 16px; color: #6f6b6b; " id="user-error"
                               for="user_comment">{{ $errors->first('user_comment')}}</label>
                    @endif
                </div>
                <div style="margin-top: 15px" id="package_goods_ru">
                    <div id="package_good_ru"  class="col-sm-12 col-lg-12 box-shadow-esi rounded-esi3" style=" margin-block: 10px; padding-block: 16px">
                        <div class="row">
                            <div class="col-sm-12 col-lg-12 ru_types_item pkg_type_name" id="pkg_type_name-1">
                            </div>
                            <div class="col-sm-12 col-lg-12 ase_types_item" id="ase_types_item">
                                <select id="firstselect" class="firstselect select2 form-control select3" name="customs_type_parents[]" required>
                                    <option value="">-</option>
                                    @foreach ($customs_categories as $category)
                                        @if(empty($category['parent_id']))
                                            <option value="{{ $category['id'] }}"
                                                    @if(old('customs_type_parents') == $category['id']) selected @endif>
                                                {{ $category->{'name_' . app()->getLocale()} }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>

                                <select id="secondselect" class="secondselect select2 form-control select3" name="customs_types[]" required>
                                    <option value="">-</option>
                                    @foreach ($customs_categories as $category)
                                        @if(!empty($category['parent_id']))
                                            <option value="{{ $category['id'] }}"
                                                    @if(old('customs_types') == $category['id']) selected @endif>
                                                {{ $category->{'name_' . app()->getLocale()} }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>

                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12 col-lg-6">
                                <div>
                                    <div for="ru_shipping_amounts[]"  class="div-input-login4" >
                                        <label>{{ __('front.shipping_amount') }}</label>
                                        <input name="ru_shipping_amounts[]"
                                               type="text"
                                               data-validation="required number"
                                               data-validation-allowing="float"
                                               value="{{ old('ru_shipping_amounts.0') }}"
                                        />
                                    </div>
                                    <div >
                                        @if ($errors->has('ru_shipping_amounts.*'))
                                            <span class="help-block">{!! $errors->first('ru_shipping_amounts.*') !!}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 col-lg-6">
                                {{--                                @include('front.form.group',--}}
                                {{-- ['key' => 'ru_items[]', --}}
                                {{-- 'label' => trans('front.number_items'),--}}
                                {{--  'options' => ['class' => 'form-control', --}}
                                {{--  'data-validation' => 'required number', --}}
                                {{--  'data-validation-allowing' => "range[1;10000]"]])--}}
                                <div>
                                    <div class="div-input-login4">
                                        <label for="ru_items[]">{{ __('front.number_items') }}</label>
                                        <input name="ru_items[]"
                                               type="number"
                                               class="form-control"
                                               required
                                               data-validation="required number"
                                               data-validation-allowing="range[1;10000]"
                                               min="1"
                                               max="10000"
                                               value="{{ old('ru_items.0') }}" />
                                    </div>
                                    <div>
                                        @if ($errors->has('ru_items.*'))
                                            <span class="help-block text-danger">
                                            {!! $errors->first('ru_items.*') !!}
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div id="add_box" class="col-sm-12 col-lg-12">
                                <div id="add_type" class="btn btn-primary btn-icon rounded-esi1" style="margin-top: 28px; padding: 17px; width: 100%; font-size: 15px; font-weight: 600; text-transform: capitalize"><i class="fa fa-plus"></i> {{__('front.user.new_package')}}</div>
                            </div>
                            <div id="rem_box" class="col-sm-12 col-lg-12"  style="display:none;">
                                <span id="rem_type" class="btn btn-danger btn-icon btn_minus rounded-esi1" style="margin-top: 28px; padding: 17px; width: 100%; font-size: 15px; font-weight: 600; text-transform: capitalize"><i class="fa fa-minus"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group" id="invoice_item" style="margin-top: 10px">
                    <label>{{ __('front.invoice') }}</label>
                    <input name="invoice" type="file" class="form-control-file" data-validation="required mime size"
                           data-validation-allowing="jpg, png, pdf, doc, docx, jpeg"
                           data-validation-max-size="3M"/>
                    @if ($errors->has('invoice'))
                        <span class="help-block">
                            <strong>{!! $errors->first('invoice') !!}</strong>
                        </span>
                    @endif
                </div>
                <div id="otp_visible">
                    <div class="row" id="otp_code1">
                        <div class="col-sm-12 col-lg-4">
                            @include('front.form.group', ['key' => 'otp_code', 'label' => trans('front.otp_code'), 'options' => ['class' => 'form-control']])
                        </div>
                    </div>
                </div>
                <div id="battery_visible">
                    <div class="row" id="has_battery1">
                        @foreach($decPages as $page)
                            <div class="col-sm-12 col-lg-12">
                                <li class="list-group-item attention">
                                    {{ $page->translateOrDefault($_lang)->title }}
                                </li>
                            </div>
                        @endforeach
                        <div class="col-sm-12 col-lg-12"  style="color: #f51f8a">
                            @include('front.form.group', ['type' => 'checkbox', 'value'=>1, 'key' => 'has_battery', 'title' => trans('front.has_battery'), 'options' => ['class' => 'form-control']])
                        </div>
                    </div>
                </div>

                <div class="form-group" >
                    <div class="col-sm-12 text-center">
                        <button type="submit" class="btn button-blue-esi rounded-esi1" style="width: 100%; background: #F51F8A; color: #fff ">{{ __('front.save') }}</button>
                    </div>
                </div>
            </div>

            {{ Form::close() }}
        </div>
    </div>
    {!! Form::close() !!}
</div>

@if(in_array(app()->getLocale(), ['az', 'ru']))
    <script src="{{ asset('langs/form/' . app()->getLocale() . '.js') }}?v=1.0.0"></script>
@endif
<script src="{{ asset('front/js/types.js') }}?v=1.0.1.1"></script>
<script>
    $(document).ready(function () {
        $('select[name="country_id"]').on("change", function () {
            var val = $("option:selected", this).val();
            var noDec = [<?= implode(",", $noDecCountries)?>];

            if (noDec.includes(parseInt(val))) {
                $("#show_dec_message").show();
                $("#dec_content").hide();
            } else {
                $("#show_dec_message").hide();
                $("#dec_content").show();
            }
        });

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
        modules: 'file'
        @if(in_array(app()->getLocale(), ['az', 'ru']))
        , language: myLanguage
        @endif
    });
</script>
