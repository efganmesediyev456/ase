<div class="hero">
    <div class="container">
        <div class="row d-flex align-items-center" style="margin: 65px 0;">
            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">

            </div>
            <div class="offset-xl-1 col-xl-5 offset-lg-1 col-lg-6 col-md-6  col-sm-12 col-12">
                <div class="request-form">
                    <h2>{{ __('front.calculator.name') }}</h2>
                    <p>{{ __('front.calculator.title') }}</p>
                    <form id="calc_form" autocomplete="off">
                        <div class="row">
                            <div class="col-lg-8">
                                <!-- Select Basic -->
                                <div class="form-group">
                                    <label class="control-label sr-only"
                                           for="city">{{ __('front.calculator.choose_country') }}</label>
                                    <select id="country" name="country" class="form-control">
                                        @foreach($countries as $country)
                                            <option value="{{ $country->id }}">{{ $country->translateOrDefault($_lang)->name }}</option>
                                        @endforeach

                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <!-- Text input-->
                                <div class="form-group">
                                    <label class="control-label sr-only"
                                           for="weight">{{ __('front.calculator.weight') }}</label>
                                    <input id="weight" name="weight" type="text"
                                           placeholder="{{ __('front.calculator.weight') }}"
                                           class="form-control input-md" required="">
                                </div>
                            </div>
                        </div>
                        <div class="row hidden-sm hidden-xs">

                            <div class="col-lg-4">
                                <!-- Text input-->
                                <div class="form-group">
                                    <label class="control-label sr-only"
                                           for="height">{{ __('front.calculator.height') }}</label>
                                    <input id="height" name="height" type="text"
                                           placeholder="{{ __('front.calculator.height') }}"
                                           class="form-control input-md" required="">
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <!-- Text input-->
                                <div class="form-group">
                                    <label class="control-label sr-only"
                                           for="Lenght">{{ __('front.calculator.length') }}</label>
                                    <input id="Lenght" name="length" type="text"
                                           placeholder="{{ __('front.calculator.length') }}"
                                           class="form-control input-md" required="">
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <!-- Text input-->
                                <div class="form-group">
                                    <label class="control-label sr-only"
                                           for="width">{{ __('front.calculator.width') }}</label>
                                    <input id="width" name="width" type="text"
                                           placeholder="{{ __('front.calculator.width') }}"
                                           class="form-control input-md" required="">
                                </div>
                            </div>
                        </div>


                        <!-- Button -->
                        <div class="form-group mb-0">
                            <button type="submit"
                                    class="btn btn-secondary btn-block">{{ __('front.calculator.approximate_price') }}:
                                <span id="calc_price">$0.00</span></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
    <script>
        function calcPrice() {
            var formData = $("#calc_form").serialize();
            $.get("<?= route('calc_price'); ?>", formData, function (data) {
                $("#calc_price").text(data);
            });
        }

        $(document).ready(function () {
            $('#calc_form select').on('change', calcPrice);
            $('#calc_form input').on('keyup paste', calcPrice);

        });
    </script>
@endpush