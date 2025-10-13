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
                                @if(! isset($extraAction['condition']) || (isset($extraAction['condition']) && $_logged->{$extraAction['condition']}))
                                    <a @if(isset($extraAction['target'])) target="{{ $extraAction['target'] }}"
                                       @endif title="{{ $extraAction['label'] }}"
                                       href="{{ route($extraAction['route']) }}"
                                       type="button" class="btn btn-{{ $extraAction['color'] }} btn-icon legitRipple"><i
                                                class="icon-{{ $extraAction['icon'] }}"></i> {{ $extraAction['label'] }}
                                    </a>
                                @endif
                            @endforeach
                        @endif

			@if(Route::has($crud['route'] . '.create'))
                        @checking('create-' . $crud['route'])
                        <a href="{{ route($crud['route'] . '.create', $crud['routeParams']) }}"
                           class="btn btn-info btn-sm legitRipple">
                            <i class="icon-plus2 position-left"></i>
                            {{ __('saysay::crud.create_button', ['title' => lcfirst($_view['name'])]) }}
                        </a>
                        @endchecking
			@endif

                    </div>
                </div>

                <div class="panel-body">
                    @include('crud::inc.filter-stack')
                    <div class="table-responsive"
                         @if(isset($items) && $items->count() <= 3) style="padding-bottom: 360px" @else style="padding-bottom: 140px" @endif>
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
                                    <th class="{{ $_view['name'] . "_" . $key }}">
                                        @if(isset($head['order']))
                                            @php
                                                $sort = str_contains(request()->get('sort', 'created_at__desc'), $head['order']) ? (str_replace($head['order'] . "__", "", request()->get('sort', 'created_at__desc')) == 'asc' ? 'desc' : 'asc') : 'desc';
                                                $icon = str_contains(request()->get('sort', 'created_at__desc'), $head['order']) ? "-amount-" . str_replace($head['order'] . "__", "", request()->get('sort', 'created_at__desc')) : '';
                                            @endphp
                                            <a class="sort_id show_loading" href="#!">
                                                <i data-key="{{ $head['order'] }}" data-sort="{{ $sort }}"
                                                   class="icon-sort{{ $icon }}"></i>
                                            </a>

                                        @endif
                                        {{ is_array($head) ? (array_key_exists('label', $head) ? $head['label'] : ucfirst(str_replace("_", " ", $key))) : ucfirst(str_replace("_", " ", $head)) }}
                                    </th>
                                @endforeach
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @if(isset($_view['sum']))
                                @foreach($_view['sum'] as $sumKey)
                                    <?php $_total_[$sumKey['key']] = 0;?>
                                @endforeach
                            @endif

                            @forelse($items as $row => $item)
                                @if(isset($_view['sum']))
                                    @foreach($_view['sum'] as $sumKey)
                                        <?php $_total_[$sumKey['key']] += $item->{$sumKey['key']}?>
                                    @endforeach
                                @endif
                                <tr @if(isset($_view['colorCondition']) && is_array($_view['colorCondition']) && $item->{$_view['colorCondition']['key']} == $_view['colorCondition']['value']) style="background: #ffcccc" @endif>
                                    @if (isset($_view['checklist']))
                                        <td>
                                            <input title="check" type="checkbox" class="styled check_all"
                                                   name="items[]" value="{{ $item->id }}"/>
                                        </td>
                                    @endif

                                    <td>
                                        @include('crud::inc.button-stack', ['_menu_left' => true])
                                    </td>

                                    @foreach($_list as $key => $head)
                                        <td @if(isset($head['title']) && $item->{$head['title']}) title="{{ $item->{$head['title']} }}" @endif >
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
                                        @include('crud::inc.button-stack')
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ 2 + intval(isset($_view['checklist'])) + count($_list) }}">
                                        @include('crud::components.alert')
                                    </td>
                                </tr>
                            @endforelse
                            <tr>
                                @if(isset($_view['sum']))
                                    @foreach($_view['sum'] as $sumKey)
                                        @if($_total_[$sumKey['key']])

                                            @if($sumKey['skip'] )
                                                <td colspan="{{ $sumKey['skip'] }}" style="text-align: center"> Total page</td>
                                            @endif
                                            <td> 
                                                <b>{{ $_total_[$sumKey['key']] }} {{ isset($sumKey['add']) ? $sumKey['add'] : null }}</b>
                                            </td>
                                        @endif
                                    @endforeach
                                @endif
                            </tr>
                            <tr>
                                @if(isset($_view['total_sum']) && $items_all)
                                    @foreach($_view['total_sum'] as $sumKey)
                                        @if($items_all->sum($sumKey['key']))

                                            @if($sumKey['skip'] )
                                                <td colspan="{{ $sumKey['skip'] }}" style="text-align: center"> Total all</td>
                                            @endif
                                            <td> 
                                                <b>{{ $items_all->sum($sumKey['key']) }} {{ isset($sumKey['add']) ? $sumKey['add'] : null }}</b>
                                            </td>
                                        @endif
                                    @endforeach
                                @endif
                            </tr>

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
