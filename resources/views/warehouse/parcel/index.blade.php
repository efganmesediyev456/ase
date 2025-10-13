@extends(config('saysay.crud.layout'))

@section('content')

    <div class="row">
        <div class="col-lg-{{ $_view['listColumns'] }} col-lg-offset-{{ (12 - $_view['listColumns'])/2 }} col-md-12 col-xs-12">
            <div class="panel panel-flat">
                <div class="panel-heading">
                    <h6>
                        {{ isset($_view['name']) ? str_plural($_view['name']) : null }}
                        <small class="display-block"> Showing {{ $items->firstItem() }} to {{ $items->lastItem() }}
                            of {{ number_format($items->total()) }} {{ $_view['sub_title'] or lcfirst(str_plural($_view['name'])) }}</small>
                    </h6>
                    <div class="heading-elements">

                        @if($extraButtons)
                            @foreach($extraButtons as $extraAction)
                                <a @if(isset($extraAction['target'])) target="{{ $extraAction['target'] }}"
                                   @endif title="{{ $extraAction['label'] }}" href="{{ route($extraAction['route']) }}"
                                   type="button" class="btn btn-{{ $extraAction['color'] }} btn-icon legitRipple"><i
                                            class="icon-{{ $extraAction['icon'] }}"></i> {{ $extraAction['label'] }}</a>
                            @endforeach
                        @endif

                        @checking('create-' . $crud['route'])
                        <a href="{{ route($crud['route'] . '.create') }}"
                           class="btn btn-info btn-sm legitRipple">
                            <i class="icon-plus2 position-left"></i>
                            {{ __('saysay::crud.create_button', ['title' => 'bag']) }}
                        </a>
                        @endchecking

                    </div>
                </div>

                <div class="panel-body">
                    @include('crud::inc.filter-stack')
                    <div class="table-responsive overflow-visible">
                        <table class="table table-hover responsive table-bordered">
                            <thead>
                            <tr>
                                @if (isset($_view['checklist']))
                                    <th>
                                        <input title="check" type="checkbox" class="styled" id="check_all"/>
                                    </th>
                                @endif
                                <th>#</th>
                                @foreach($_list as $key => $head)
                                    <th>{{ is_array($head) ? (array_key_exists('label', $head) ? $head['label'] : ucfirst(str_replace("_", " ", $key))) : ucfirst(str_replace("_", " ", $head)) }}</th>
                                @endforeach
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($items as $row => $item)
                                <?php $parcelId = $item->id; ?>
                                <tr @if($item->warehouse_id == env('LOGIC_ID')) style="background: {{ $item->inserted_color }};" @endif  >
                                    <td  class="tab_it closed"  data-bags="parcel_{{ $parcelId }}" data-packages="parcel2_{{ $parcelId }}" data-tabs="tab_{{ $parcelId }}">
                                        <i class="icon-minus2 minus"></i>
                                        <i class="icon-plus2 plus"></i>
                                        {{ $item->custom_id }}
                                    </td>
                                    <td colspan="{{  intval(isset($_view['checklist'])) + count($_list) - 8 }}"></td>
				    <td>@if($item->packages->sum('has_battery') > 0) <img title="" src="{{ asset('admin/images/battery-11-32.png') }}" > @endif</td>
                                    <td>{{ $item->packages->sum('shipping_converted_price') }} USD</td>
                                    <td>{{ $item->packages->sum('weight') }} kg</td>
                                    <td>{{ $item->packages_count }} @if(! $item->sent) /<a href="{{ route('w-packages.index') }}?status=0&incustoms=2&incustomscheck=yes&dec=1&limit=25" class="waiting_packages" style="background: red;color: #fff;padding: 5px;border-radius: 5px;">{{ $ready_packages }}</a>@endif</td>
                                    <td>
                                                {{ $item->packages_count }} /
                                                <div class="cr_b_prcl_ct">
                                                     @if($item->packagecarriers_count)
                                                       <div class="cr_b_prcl_ok">{{ $item->packagecarriers_count }}</div>
                                                     @else
                                                       <div class="cr_b_prcl_no"> &nbsp&nbsp&nbsp&nbsp</div>
                                                     @endif
                                                </div>
                                                <div class="cr_b_prcl_ct">
                                                     @if($item->packagecarriersreg_count)
                                                       <div class="cr_b_prcl_ok">{{ $item->packagecarriersreg_count }}</div>
                                                     @else
                                                       <div class="cr_b_prcl_no"> &nbsp&nbsp&nbsp&nbsp</div>
                                                     @endif
                                                </div>
                                                <div class="cr_b_prcl_ct">
                                                     @if($item->packagecarriersdepesh_count)
                                                       <div class="cr_b_prcl_ok">{{ $item->packagecarriersdepesh_count }}</div>
                                                     @else
                                                       <div class="cr_b_prcl_no"> &nbsp&nbsp&nbsp&nbsp</div>
                                                     @endif
                                                </div>
                                    </td>
                                    <td>{{ $item->sent_with_label }}</td>
                                    <td></td>
                                    <td>{{ $item->created_at->diffForHumans() }}</td>
                                    <td>
                                        @include('crud::inc.button-stack',['noNeed' => true])
                                    </td>
                                </tr>
				@foreach($item->bags as $item)
				@if($item->packages_count > 0)
                                <?php $bagId = $item->id; ?>
                                <tr class="sub-child  parcel_{{ $parcelId }}">
                                    <td  class="tab_it2 closed tab_{{ $parcelId }}" data-packages="bag_{{ $bagId }}">
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;	
                                        <i class="icon-minus2 minus"></i>
                                        <i class="icon-plus2 plus"></i>
                                        {{$item->custom_id}}
                                    </td>
				    <td colspan="{{  intval(isset($_view['checklist'])) + count($_list) - 8 }}"></td>
				    <td>@if($item->packages->sum('has_battery') > 0) <img title="" src="{{ asset('admin/images/battery-11-32.png') }}" > @endif</td>
                                    <td>{{ $item->packages->sum('shipping_converted_price') }} USD</td>
                                    <td>{{ $item->packages->sum('weight') }} kg</td>
                                    <td>{{ $item->packages_count }}</td>
                                    <td>
                                                {{ $item->packages_count }} /
                                                <div class="cr_b_prcl_ct">
                                                     @if($item->packagecarriers_count)
                                                       <div class="cr_b_prcl_ok">{{ $item->packagecarriers_count }}</div>
                                                     @else
                                                       <div class="cr_b_prcl_no"> &nbsp&nbsp&nbsp&nbsp</div>
                                                     @endif
                                                </div>
                                                <div class="cr_b_prcl_ct">
                                                     @if($item->packagecarriersreg_count)
                                                       <div class="cr_b_prcl_ok">{{ $item->packagecarriersreg_count }}</div>
                                                     @else
                                                       <div class="cr_b_prcl_no"> &nbsp&nbsp&nbsp&nbsp</div>
                                                     @endif
                                                </div>
                                                <div class="cr_b_prcl_ct">
                                                     @if($item->packagecarriersdepesh_count)
                                                       <div class="cr_b_prcl_ok">{{ $item->packagecarriersdepesh_count }}</div>
                                                     @else
                                                       <div class="cr_b_prcl_no"> &nbsp&nbsp&nbsp&nbsp</div>
                                                     @endif
                                                </div>
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td>{{ $item->created_at->diffForHumans() }}</td>
                                    <td>
                                        @include('crud::inc.button-stack', ['extraActions' => $extraActionsForBag])
                                    </td>
                                @foreach($item->packages as $item)
                                <tr class="sub-child  parcel2_{{ $parcelId }} bag_{{ $bagId }}">
                                    <td></td>
                                        @foreach($_list as $key => $head)
                                            <td>
                                                @php
                                                    $key = is_array($head) ? $key : $head;
                                                    $type = isset($head['type']) ? $head['type'] : 'text';
                                                    $entry = parseRelation($item, $key);
                                                @endphp

                                                @if(view()->exists('admin.crud.columns.' . $type))
                                                    @include('admin.crud.columns.' . $type)
                                                @else
                                                    @include('crud::components.columns.' . $type )
                                                @endif
                                            </td>
                                        @endforeach
                                    <td>
                                        @include('crud::inc.button-stack', ['extraActions' => $extraActionsForPackage, 'noNeed' => true])
                                    </td>
                                </tr>
                                @endforeach
				@endif
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="{{ 2 + intval(isset($_view['checklist'])) + count($_list) }}">
                                        @include('crud::components.alert')
                                    </td>
                                </tr>
                            @endforelse
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
                            <div>{!! $items->appends(Request::except('page'))->links() !!}</div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
@endsection
