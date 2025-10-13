@extends('front.layout')
<script>
    var wArr={{$warehousesArr}};
</script>

@section('content')
    @include('front.sections.page-header')
    @include('front.widgets.modal', ['modalId' => 'ajaxModal', 'noClick' => true])
    <div class="my-packages">
        <!-- content start -->
        <div class="custom-container section-space80">
            <div class="row">
                <div class="col-md-12">
                    <div class="wrapper-content bg-white box-shadow-esi rounded-esi padding-esi">
                        <div class="st-tabs">
                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs nav-justified" role="tablist">
                                <li @if($id == 6) class="active" @endif>
                                    <a class="list-3" href="{{ route('my-packages', ['id' => 6]) }}">
                                      <span class="list-3-icon" style="background: @if($id == 6) #fff @else #dedede @endif;padding-left: 4px; border-radius: 100%; display: flex; justify-content: center; align-items: center">
                                          <i  class="fa fa-pencil"></i>
                                      </span>
                                        <span style="color: #000; font-size:16px ">{{ __('front.early_declaration') }}  <span  class="badge">{{ $counts[6] or null }}</span></span>

                                    </a>
                                </li>
                                <li @if($id == 0) class="active" @endif>
                                    <a class="list-3" href="{{ route('my-packages') }}">
                                          <span class="list-3-icon" style="background: @if($id == 0) #fff @else #dedede @endif; padding-left: 4px;  border-radius: 100%; display: flex; justify-content: center; align-items: center">
                                            <i class="fa fa-globe"></i>
                                          </span>
                                        <span style="color: #000; font-size:16px ">{{ __('front.in_warehouse') }}</span>
                                        <span class="badge">{{ $counts[0] or null }}</span>
                                    </a>
                                </li>
                                @if( isset($counts[7]) && $counts[7]>0 )
                                    <li @if($id == 7) class="active" @endif>
                                        <a class="list-3" href="{{ route('my-packages', ['id' => 7]) }}">
                                          <span class="list-3-icon" style="background: @if($id == 7) #fff @else #dedede @endif; padding-left: 4px;  border-radius: 100%; display: flex; justify-content: center; align-items: center">
                                            <i  class="fa fa-truck"></i>
                                          </span>
                                            <span style="color: #000; font-size:16px ">{{ __('front.departed') }}</span>
                                            <span class="badge">{{ $counts[7] or null }}</span>
                                        </a>
                                    </li>
                                @endif
                                <li @if($id == 1) class="active" @endif>
                                    <a class="list-3" href="{{ route('my-packages', ['id' => 1]) }}">
                                        <span class="list-3-icon" style="background: @if($id == 1) #fff @else #dedede @endif; padding-left: 4px;  border-radius: 100%; display: flex; justify-content: center; align-items: center">
                                            <i  class="fa fa-plane"></i>
                                          </span>
                                        <span style="color: #000; font-size:16px ">{{ __('front.was_sent') }}</span>

                                        <span class="badge">{{ $counts[1] or null }}</span>
                                    </a>
                                </li>
                                @if( isset($counts[8]) && $counts[8]>0 )
                                    <li @if($id == 8) class="active" @endif>
                                        <a class="list-3" href="{{ route('my-packages', ['id' => 8]) }}">
                                        <span class="list-3-icon" style="background: @if($id == 8) #fff @else #dedede @endif;padding-left: 4px;  border-radius: 100%; display: flex; justify-content: center; align-items: center">
                                            <i  class="fa fa-map-marker"></i>
                                          </span>
                                            @if( !$user->azeri_express_use && !$user->azerpoct_send && $user->store_status == 2)
                                                <span style="color: #000; font-size:16px ">{{ __('front.in_store') }}</span>
                                            @else
                                                <span style="color: #000; font-size:16px ">{{ __('front.in_store2') }}</span>
                                            @endif
                                            <span class="badge">{{ $counts[8] or null }}</span>
                                        </a>
                                    </li>
                                @endif
                                <li @if($id == 2) class="active" @endif>
                                    <a class="list-3" href="{{ route('my-packages', ['id' => 2]) }}">
                                         <span class="list-3-icon" style="background: @if($id == 2) #fff @else #dedede @endif;padding-left: 4px;  border-radius: 100%; display: flex; justify-content: center; align-items: center">
                                            <i  class="fa fa-map-marker"></i>
                                          </span>
                                        <span style="color: #000; font-size:16px ">{{ __('front.in_baku') }} <span class="badge">{{ $counts[2] or null }}</span></span>

                                    </a>
                                </li>
                                <li @if($id == 3) class="active" @endif>
                                    <a class="list-3" href="{{ route('my-packages', ['id' => 3]) }}">
                                         <span class="list-3-icon" style="background: @if($id == 3) #fff @else #dedede @endif; padding-left: 4px; border-radius: 100%; display: flex; justify-content: center; align-items: center">
                                            <i class="fa fa-clock-o"></i>
                                          </span>
                                        <span style="color: #000; font-size:16px ">{{ __('front.done_packages') }}</span>
                                        <span class="badge">{{ $counts[3] or null }}</span>
                                    </a>
                                </li>
                            </ul>
                            <!-- Tab panes -->
                            <div style="margin-top: 40px; border: none; " class="tab-content">
                                <div role="tabpanel" class="tab-pane fade in active">
                                    <div class="row mb60">
                                        <div  class="col-md-6">
                                            @if ( request()->has('success'))
                                                <div class="alert-esi2"
                                                     role="alert">{{ __('front.was_paid') }}</div>
                                            @endif
                                            @if ( request()->has('error'))
                                                <div class="alert-esi3"
                                                     role="alert">{{ request()->get('error') }}</div>
                                            @endif
                                            @if (session('success'))
                                                <div class="alert-esi2"
                                                     role="alert">{{ __('front.declaration_updated') }}</div>
                                            @else
                                                @if($packages->count() and 3 != $id)
                                                    <div class="alert-esi3"
                                                         role="alert">{{ __('front.user_packages_info') }}</div>
                                                @endif
                                            @endif
                                        </div>
                                        <div  class="m-esi col-md-6">
                                            @if(6 == $id)
                                                <div style="padding: 0"  class="text-center comapre-action ">
                                                    <a style="width: 100%" href="{{ route('declaration.create') }}"
                                                       id="declaration-button"
                                                       data-toggle="modal" data-target="#ajaxModal"
                                                       data-remote="false"
                                                       class="rounded-esi1 button-blue-esi">{{ __('front.early_declaration') }}</a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    @forelse($packages as $package)

                                        <div  class="compare-block mb40 ">
                                            <div  style=" padding: 24px; background: #F5F5F5;  border-top-left-radius: 16px; border-top-right-radius: 16px;" class=" cards-flex-boxs-box">
                                                <span  class="flag cards-flex-box">
                                                    <div style="font-weight: 500; font-size: 14px; color: #3f3f3f"> {{ __('front.user.country') }}</div>
                                                    <img style="width: 32px" src="{{ $package->country_flag }}">
                                                </span>

                                                <span  class="tracking-code cards-flex-box">
                                                    <div style="font-weight: 500; font-size: 14px; color: #3f3f3f">{{ __('front.tracking_code') }}</div>
                                                    <span style="font-size: 14px; font-weight: 400; color: #3f3f3f" >
                                                     {{ $package->tracking_code }}
                                                    </span>
                                                </span>
                                                <span  class="tracking-code cards-flex-box">
                                                    <div style="font-weight: 500; font-size: 14px; color: #3f3f3f">AWB</div>
                                                   <span style="font-size: 14px; font-weight: 400; color: #3f3f3f" >
                                                    {{ $package->custom_id }}
                                                   </span>
                                                </span>

                                                <div  class="tracking-code cards-flex-box">
                                                    <div style="font-weight: 500; font-size: 14px; color: #3f3f3f"> {{ __('front.user.product') }}</div>
                                                    <p style="font-size: 14px; font-weight: 400; color: #3f3f3f" >
                                                        {{($package->type_id && $package->type ?
                                                          ($package->type->name . ($package->other_type ?
                                                          "(" . $package->other_type .")" : null))  :
                                                           ($package->detailed_type ?
                                                           str_limit($package->detailed_type, 40) : '-'))}}
                                                    </p>
                                                </div>
                                                @if($package->azerpoct_send && $package->azerpoct)
                                                    <button class="btn btn-info btn-sm"
                                                            style="padding: 10px 15px;"
                                                            disabled>
                                                        {{ trans('front.azerpoct_status_'.$package->azerpoct->status,['zip_code'=>$package->zip_code]) }}
                                                    </button>
                                                @endif
                                                @if( $package->warehouse_id==11 && in_array($package->status,[0,7,1,2,4,8]) )
                                                    <span class="tracking-code" >
                                                       <a href="{{ route('photo', $package->id) }}" target="_blank" >
                                                            <img  style="width: 20px; height: 19px;" src="data:image/png;base64,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">
                                                       </a>
                                                    </span>
                                                @endif
                                                @if($package->warehouse && ! $package->warehouse->allow_make_fake_invoice)
                                                    <div class="cards-flex-box" >
                                                        <div style="font-weight: 500; font-size: 14px; color: #3f3f3f">{{ __('front.user.watch') }}</div>

                                                        <a href="http://parcelsapp.com/{{ app()->getLocale() }}/tracking/{{ $package->tracking_code }}"
                                                           data-id="{{ $package->tracking_code }}" target="_blank"
                                                           style="border-bottom: 1px solid #15549A; color: #15549A; font-weight: 500; font-size: 14px ; padding-bottom: 2px"> {{ __('front.user.where_is_package') }}
                                                        </a>
                                                    </div>
                                                @endif
                                                @if($package->status == 1 || $package->status == 2 || $package->status == 7 || $package->status == 8)
                                                    <span>
                                                        @if($package->invoice)
                                                            <a href="{{ $package->invoice }}"
                                                               class="btn btn-primary btn-sm"
                                                               data-id="" target="_blank"
                                                               style="padding: 10px 15px;">{{ __('front.user.invoice') }}
                                                           </a>
                                                        @endif
                                                    </span>

                                                @endif
                                            </div>
                                            <div   style=" padding: 24px; background: #F5F5F5;  border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;" class="cards-flex-boxs-box">

                                                <div  class="tracking-code cards-flex-box">
                                                    <div style="font-weight: 500; font-size: 14px; color: #3f3f3f">{{ __('front.user.store') }}</div>
                                                    <img style="width: 32px" class="web_logo" src="{{ $package->web_site_logo }}" alt="{{ $package->website_name }}">

                                                    {{--                                                             <span style="font-size: 14px; font-weight: 400; color: #3f3f3f" >--}}
                                                    {{--                                                             {{ $package->tracking_code }}--}}
                                                    {{--                                                            </span>--}}
                                                </div>
                                                @if ($package->seller_name)
                                                <div class="cards-flex-box" >
                                                        <div style="font-weight: 500; font-size: 14px; color: #3f3f3f">{{ __('front.user.seller') }}</div>
                                                        <span style="font-size: 14px; font-weight: 400; color: #3f3f3f" >
                                                                {{ $package->seller_name }}
                                                                </span>
                                                </div>
                                                @endif
                                                <span  class="date cards-flex-box">
                                                    <div style="font-weight: 500; font-size: 14px; color: #3f3f3f"> {{ __('front.user.date') }}</div>
                                                    <span style="font-size: 14px; font-weight: 400; color: #3f3f3f" >
                                                    {{ $package->created_at->format('d.m.y') }}
                                                    </span>
                                                </span>
                                                @if($package->user_id != $user->id)
                                                    <span  class="tracking-code cards-flex-box">
                                                        <div style="font-weight: 500; font-size: 14px; color: #3f3f3f"> {{ __('front.user.user') }}</div>
                                                         <b style="font-size: 14px; font-weight: 600; color: #3f3f3f" >{{ $package->user->full_name }} ({{ $package->user->customer_id }})</b>
                                                    </span>
                                                @endif
                                                @if($package->shipping_amount || $package->shipping_amount_goods)
                                                    <div class="cards-flex-box" >
                                                        <div style="font-weight: 500; font-size: 14px; color: #3f3f3f">{{ __('front.user.seller') }}</div>
                                                        <div style="font-size: 14px; font-weight: 400; color: #3f3f3f" >{{ $package->shipping_org_price }}</div>
                                                    </div>
                                                @endif
                                                <div class="cards-flex-box" >
                                                    <div style="font-weight: 500; font-size: 14px; color: #3f3f3f">{{ __('front.smart_customs_declcaration_title') }}</div>
                                                    @if($package->carrier && $package->carrier->status>0)
                                                        <span style="font-size: 14px; font-weight: 500; color: #11B42F; border-bottom: 1px solid #11B42F; padding-bottom:2px">{{  __('front.smart_customs_declcaration_yes') }}</span>
                                                    @else
                                                        <span style="font-size: 14px; font-weight: 500; color: #ff0000; border-bottom: 1px solid #ff0000; padding-bottom:2px">{{  __('front.smart_customs_declcaration_no') }}</span>
                                                    @endif
                                                </div>
                                                <span class="cards-flex-box">
                                                    <div style="font-weight: 500; font-size: 14px; color: #3f3f3f">{{ $package->weight_unit }}</div>
                                                    <span style="font-size: 14px; font-weight: 400; color: #3f3f3f" >
                                                   {{ ($package->weight_goods ? $package->weight_goods : $package->weight) ?? '-' }}
                                                    </span>
                                                </span>
                                                @if(!empty($package->size))
                                                    <span class="cards-flex-box" >
                                                        <div style="font-weight: 500; font-size: 14px; color: #3f3f3f">{{ $package->size_unit }}</div>
                                                        <span style="font-size: 14px; font-weight: 400; color: #3f3f3f" >
                                                      {{ $package->size }}
                                                        </span>
                                                    </span>
                                                @endif
                                                <div class="cards-flex-box">
                                                    <div style="font-weight: 500; font-size: 14px; color: #3f3f3f">{{ __('front.number_items') }}</div>
                                                    <span style="font-size: 14px; font-weight: 400; color: #3f3f3f" >
                                                              {{ $package->number_items_goods or '-' }}
                                                            </span>
                                                </div>
                                                @if($package->status != 4 && $package->status != 48 && $package->debt_price > 0 && $package->paid_debt == 0)
                                                    <div class="text-center mt-2">
                                                        {!! (new App\Models\Payments\PortManat())->generateFormPackageDebt(NULL,$package) !!}
                                                    </div>
                                                @endif

                                                <div class="cards-flex-box">
                                                    <div style="font-weight: 500; font-size: 14px; color: #3f3f3f">
                                                        {{ __('front.delivery_price') }} @if($package->discount_percent) ({{ $package->discount_percent_with_label }} {{ __('front.shop.discount') }})@endif
                                                    </div>
                                                    <span style="font-size: 14px; font-weight: 400; color: #3f3f3f" >
                                                      {{ $package->delivery_price ? $package->delivery_price_discount_with_label: '-' }} {{ $package->delivery_manat_price ? ' ('.$package->delivery_manat_price_discount.' AZN)': '' }}
                                                    </span>
                                                </div>

                                                @if($package->status == 4)
                                                    <div class="text-center comapre-action">
                                                        <button class="btn btn-warning btn-sm"
                                                                disabled>{{ trans('front.incustoms') }}
                                                        </button>
                                                    </div>
                                                @endif
                                                @if($package->paid && ($id==1 || $id == 2 || $id == 8 || $id == 4))
                                                    <div class="text-center comapre-action">
                                                        <button class="btn btn-success btn-sm"
                                                                disabled>{{ trans('front.paid') }}
                                                        </button>
                                                    </div>
                                                @elseif(($id == 6 || $id == 0) && ($package->warehouse && $package->warehouse->country && ($package->warehouse->country->allow_declaration ||  $package->warehouse->country->id == 2)))
                                                    <div style=" gap: 20px; justify-content: space-between; padding-top: 0; align-items: center" class="cards-flex-box comapre-action">
                                                        <div style="font-weight: 500; font-size: 14px; color: #3f3f3f">{{ __('front.user.actions') }}</div>
                                                        <div>
                                                            <a href="{{ route('declaration.edit', $package->id) }}"
                                                               data-toggle="modal" data-target="#ajaxModal"
                                                               data-remote="false">
                                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path d="M14.06 9.02L14.98 9.94L5.92 19H5V18.08L14.06 9.02ZM17.66 3C17.41 3 17.15 3.1 16.96 3.29L15.13 5.12L18.88 8.87L20.71 7.04C21.1 6.65 21.1 6.02 20.71 5.63L18.37 3.29C18.17 3.09 17.92 3 17.66 3ZM14.06 6.19L3 17.25V21H6.75L17.81 9.94L14.06 6.19Z" fill="#2EE572"/>
                                                                </svg>
                                                            </a>
                                                            @if(! $package->weight && $id == 6 && !$package->ukr_express_id)
                                                                <a href="{{ route('declaration.delete', $package->id) }}"
                                                                   class=" show_loading">
                                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                        <path d="M6 19C6 20.1 6.9 21 8 21H16C17.1 21 18 20.1 18 19V7H6V19ZM19 4H15.5L14.5 3H9.5L8.5 4H5V6H19V4Z" fill="#ff0000">
                                                                            <animate attributeName="opacity" values="1;0.7;1" dur="2s" repeatCount="indefinite"/>
                                                                        </path>
                                                                    </svg>
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif

                                                @if(!$package->paid && ($id == 1 || $id == 2 || $id == 8 || $id == 4))
                                                    <div class="text-center comapre-action">
                                                        <input type="checkbox" class="checkbox" value="{{$package->delivery_manat_price_discount}}" disabled id="pkg_pay_check-{{$package->id}}" name="pkg_pay_check" onclick="setPkgPayIds()">
                                                        <span class="checkboxtext"> {{ trans('front.select_to_pay') }}</span>
                                                    </div>
                                                @endif
                                                @if($package->paid  && ($id == 2 || $id == 8)
                                                                && !$user->azeri_express_use && !$package->azeri_express_office_id
                                                                && !$user->surat_use && !$package->surat_office_id
                                                                && !$user->azerpoct_send && !$package->azerpost_office_id
                                                                && (!$user->store_status || $user->store_status == 1 ||  $user->store_status == 2)
                                                                && (!$package->store_status || $package->store_status == 1 ||  $package->store_status == 2)
                                                                )
                                                    @if(!$package->courier_delivery || ($package->courier_delivery && $package->courier_delivery->deleted_at))
                                                        @if(($package->debt_price > 0 && $package->paid_debt == 1) || $package->debt_price == 0)
                                                            <a href="{{ route('cds.create', 'pid='.$package->id) }}" class="show_loading">
                                                                {{ trans('front.create_courier_delivery_title') }}
                                                            </a>
                                                        @endif

                                                    @else
                                                        @if($package->courier_delivery->status<=0 && !$package->courier_delivery->paid)
                                                            <a href="{{ route('cds.edit', $package->courier_delivery) }}"
                                                               class="show_loading">{{ trans('front.courier_delivery') }} {{ trans('front.courier_delivery_status_'.$package->courier_delivery->status) }}</a>
                                                        @else
                                                            <a href="{{ route('cds.show', $package->courier_delivery) }}"
                                                               class="show_loading">{{ trans('front.courier_delivery') }} {{ trans('front.courier_delivery_status_'.$package->courier_delivery->status) }}</a>
                                                        @endif
                                                    @endif
                                                @endif


                                            </div>
                                        </div>
                                    @empty
                                        @if (! session('success'))
                                            <div class="alert-esi"
                                                 role="alert">{{ __('front.no_any_package') }}</div>
                                        @endif
                                    @endforelse
                                    @if ($id == 1 || $id == 2 || $id == 8 || $id==4)
                                        <form class="form-inline">
                                            <div class="text-center comapre-action">
                                                {{--     <input type=text class="form-control form-control-sm" name=pr_code id=pr_code  onchange="promoChange()" onkeydown="promoChange()" onpaste="promoChange()" oninput="promoChange()" style="display:inline; text-align: center; padding: 5px 5px 5px 5px; font-size:120%;" maxlength="8" size="10" placeholder="{{ trans('front.promo_code') }}">--}}
                                                <input type=hidden name=pr_percent id=pr_percent value="" >
                                                <input type=hidden name=pr_amount id=pr_amount value="0">
                                                <input type=hidden name=pr_weight id=pr_weight value="0">
                                                <input type=hidden name=pr_id id=pr_id value="">
                                                <input type=hidden name=pr_user_percent id=pr_user_percent value="0">
                                                <input type=hidden name=pr_user_amount id=pr_user_amount value="0">
                                                <input type=hidden name=pr_user_weight id=pr_user_weight value="0">
                                                <input type=hidden name=pr_user_id id=pr_user_id value="">
                                                <h3 style="display:inline; padding: 10px 5px 10px 5px" id="pr_discount_lbl"></h3>
                                                <h3 style="display:inline; color:blue; padding: 10px 5px 10px 5px" id="pr_user_discount_lbl"></h3>
                                                <input type=hidden name=pr_user id=pr_user value="0">
                                                <h3 style="display:inline; color:red; padding: 10px 5px 10px 5px" id="pr_error_lbl"></h3>
                                                {{--     <button class="btn btn-info btn-sm" disabled  style="display:inline;  margin: 5px 5px 15px 5px; margin-right:20px;" type='button' id="pr_btn" onclick="getPromo()" disabled>{{ trans('front.promo_get') }}</button>--}}

                                                {{--     <input type=text class="form-control form-control-sm" name=ul_code id=ul_code onchange="ulduzumChange()" onkeydown="ulduzumChange()" onpaste="ulduzumChange()" oninput="ulduzumChange()" style="display:inline; text-align: center; padding: 5px 5px 5px 5px; font-size:120%;" maxlength="4" size="9" placeholder="{{ trans('front.ulduzum_code') }}">--}}
                                                <input type=hidden name=ul_discount id=ul_discount value="0">
                                                <input type=hidden name=ul_id id=ul_id value="">
                                                <h3 style="display:inline; padding: 10px 5px 10px 5px" id="ul_discount_lbl"></h3>
                                                {{--     <button class="btn btn-danger btn-sm"  style="display:inline;  margin: 5px 5px 15px 5px" type='button' id="ul_btn" onclick="getUlduzum()" disabled>{{ trans('front.ulduzum_get') }}</button>--}}
                                            </div>
                                        </form>
                                        <div  class="text-center comapre-action">
                                            {{--     @if(Auth::user()->id == '1901' || Auth::user()->id == '30869')--}}
                                            {{--         {!! (new App\Models\Payments\PortManat())->generateFormKapital() !!}--}}
                                            {{--     @else--}}
                                            {{--         {!! (new App\Models\Payments\PortManat())->generateForm2() !!}--}}
                                            {{--     @endif--}}
                                            {!! (new App\Models\Payments\PortManat())->generateFormKapital() !!}

                                            {{--     {!! (new App\Models\Payments\PortManat())->generateFormNew() !!}--}}
                                        </div>
                                    @endif
                                    <div class="mt-20 text-center">
                                        {!! $packages->render() !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('js')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-form-validator/2.3.26/jquery.form-validator.min.js"
            defer></script>
    <script>
        @if(request()->has('declaration') && request()->get('declaration') == 'on')
        $(document).ready(function () {
            $("#declaration-button").click();
        });
        @endif

        $("#portmanat_payment_form").on("submit", function(){
            let amount=parseFloat(this.amount.value);
            if(amount>0) {
                this.method='POST';
                this.action=$(this).data('action-portmanat');
                console.log($(this).data('action-portmanat'));
            } else {
                this.method='GET';
                this.action=$(this).data('action-local');
                console.log($(this).data('action-local'));
            }
            return true;
        });

    </script>
@endpush
