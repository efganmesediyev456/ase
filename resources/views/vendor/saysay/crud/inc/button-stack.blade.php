@if((! isset($item->no_action) || (isset($item->no_action) && ! $item->no_action)))
    <div class="list-icons">
        <div class="dropdown">
            <a href="#" class="list-icons-item" data-toggle="dropdown">
                <i class="icon-menu9"></i>
            </a>

            <div class="dropdown-menu dropdown-menu-{{ isset($_menu_left) ? 'left' : 'right' }}">
                @if($extraActions)
                    @foreach($extraActions as $extraAction)
                        @if(!isset($extraAction['role']) || Auth::user()->can($extraAction['role']))
                            @if(view()->exists($_viewDir . '.inc.' . (isset($extraAction['button']) ? $extraAction['button'] : 'extra_button')) && isset($extraAction['custom']))
                                @include($_viewDir . '.inc.'  . (isset($extraAction['button']) ? $extraAction['button'] : 'extra_button'))
                            @else
                                @if($item->{$extraAction['key']} && (!isset($extraAction['country_code']) || ($item->{'country_code'}==$extraAction['country_code'])))
                                    <a @if(isset($extraAction['target'])) target="{{ $extraAction['target'] }}" @endif
                                        title="{{ $extraAction['label'] }}"
                                        href="{{ isset($extraAction['route']) ? route($extraAction['route'], $item->id) : $item->{$extraAction['key']} }}{{ isset($extraAction['query']) ? ('?' . http_build_query($extraAction['query'])) : null }}"
                                       @if(isset($extraAction['confirm'])) onclick="return confirm('Are you sure to {{$extraAction['confirm']}}?')" @endif
                                       @if(isset($extraAction['id'])) id="{{$extraAction['id']}}" @endif
                                       @if(isset($item['name'])) data-name="{{$item['name']}}" @endif
                                       @if(isset($extraAction['data-id']) && isset($item['id'])) data-id="{{$item['id']}}" @endif
                                       @if(isset($extraAction['data-toggle'])) data-toggle="{{$extraAction['data-toggle']}}" @endif
                                       @if(isset($extraAction['data-target'])) data-target="{{$extraAction['data-target']}}" @endif
                                       @if(isset($extraAction['data-url'])) data-url="{{route($extraAction['data-url'], $item['id'])}}" @endif
                                       class="dropdown-item legitRipple"><i
                                                class="icon-{{ $extraAction['icon'] }}"></i> {{ $extraAction['label'] }}
                                    </a>
                                @endif
                            @endif
                        @endif
                    @endforeach
                @endif

                @if( (!isset($item->sent) || !$item->sent) && ! isset($noNeed))
                    @if(Route::has($crud['route'] . '.edit'))
                        @checking('update-' . $crud['route'])
                        @if($crud['translatable'])
                            <div class="dropdown-divider"></div>
                            <div class="dropdown-submenu dropdown-submenu-left">
                                <a href="#" class="dropdown-item"><i class="icon-pencil"></i> Edit</a>
                                <div class="dropdown-menu">
                                    @foreach(config('translatable.locales_name') as $lang => $langName)
                                        <a class="dropdown-item"
                                           href="{{ route($crud['route'] . '.edit', array_merge($crud['routeParams'], ['id' => $item->id])) }}?lang={{ $lang }} {{(isset($item->track) && $item->track)?'&track=1':''}}">
                                            - {{ $langName }}</a>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <a href="{{ route($crud['route'] . '.edit', array_merge($crud['routeParams'], ['id' => $item->id])) }}{{(isset($item->track) && $item->track)?'?track=1':''}}"
                               type="button"
                               class="dropdown-item" data-spinner-color="#fff"
                               data-style="slide-left"><i class="icon-pencil"></i> Edit</a>
                        @endif
                        @endchecking
                    @endif
                    @if(Route::has($crud['route'] . '.destroy'))
                        @checking('delete-' . $crud['route'])
                        @if((! isset($item->dont_delete) || (isset($item->dont_delete) && ! $item->dont_delete)))
                            {{ Form::open(['class' => 'sure-that no-padding', 'url' => route($crud['route'] . '.destroy', array_merge($crud['routeParams'], ['id' => $item->id])), 'method' => 'delete']) }}
                            <button class="{{ (isset($class) ? $class : '') . "dropdown-item" }}"
                                    data-spinner-color="#fff"
                                    data-style="slide-left" type="submit">
                                <i class="icon-trash"></i> Delete
                            </button>
                            {{ Form::close() }}
                        @endif
                        @endchecking
                    @endif
                @endif


            </div>
        </div>
    </div>
@endif
