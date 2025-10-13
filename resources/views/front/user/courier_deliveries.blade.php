@extends('front.layout')

@section('content')
    @include('front.sections.page-header')
    <div class="my-packages">
        <!-- content start -->
        <div class="custom-container section-space80">
            <div class="row">
                <div class="col-md-12">
                    <div style="padding: 24px 30px ;min-height: 400px" class="wrapper-content bg-white box-shadow-esi rounded-esi">
                        <div class="st-tabs">
                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs nav-justified" role="tablist">
                                <li @if($id == 0) class="active" @endif>
                                    <a class="list-3" href="{{ route('cds', ['id' => 0]) }}">
                                        <span style="background: @if($id == 0) #fff @else #dedede @endif;width: 48px; padding-left: 4px; height: 48px; border-radius: 100%; display: flex; justify-content: center; align-items: center">
                                          <i style="font-size: 24px; color: #15549A" class="fa fa-pencil"></i>
                                      </span>
                                        <span style="color: #000; font-size:16px ">{{ __('front.courier_delivery_status_0') }}</span>

                                        <span class="badge">{{ $counts[0] or null }}</span>
                                    </a>
                                </li>
                                <li @if($id == 1 || $id==2) class="active" @endif>
                                    <a class="list-3" href="{{ route('cds',['id' => 1]) }}">
                                         <span style="background: @if($id == 1) #fff @else #dedede @endif;width: 48px; padding-left: 4px; height: 48px; border-radius: 100%; display: flex; justify-content: center; align-items: center">
                                            <i style="font-size: 24px; color: #15549A" class="fa fa-globe"></i>
                                          </span>
                                        <span style="color: #000; font-size:16px ">{{ __('front.courier_delivery_status_1') }}</span>

                                        <span class="badge">{{ (isset($counts[1]) && isset($counts[2])) ? $counts[1]+$counts[2] : (isset($counts[1]) ? $counts[1]: (isset($counts[2]) ? $counts[2] : null ))}}</span>
                                    </a>
                                </li>
                                <li @if($id==3) class="active" @endif>
                                    <a class="list-3" href="{{ route('cds', ['id' => 3]) }}">
  <span style="background: @if($id == 3) #fff @else #dedede @endif;width: 48px; padding-left: 4px; height: 48px; border-radius: 100%; display: flex; justify-content: center; align-items: center">
                                            <i style="font-size: 24px; color: #15549A" class="fa fa-plane"></i>
                                          </span>
                                        <span style="color: #000; font-size:16px ">{{ __('front.courier_delivery_status_3') }}</span>

                                        <span class="badge">{{ $counts[3] or null }}</span>
                                    </a>
                                </li>
                                <li @if($id == 4) class="active" @endif>
                                    <a class="list-3" href="{{ route('cds', ['id' => 4]) }}">
                                         <span style="background: @if($id == 4) #fff @else #dedede @endif;width: 48px; padding-left: 4px; height: 48px; border-radius: 100%; display: flex; justify-content: center; align-items: center">
                                            <i style="font-size: 24px; color: #15549A" class="fa fa-clock-o"></i>
                                          </span>
                                        <span style="color: #000; font-size:16px ">{{ __('front.courier_delivery_status_4') }}</span>

                                        <span class="badge">{{ $counts[4] or null }}</span>
                                    </a>
                                </li>
                                <li @if($id == 5) class="active" @endif>
                                    <a class="list-3" href="{{ route('cds', ['id' => 5]) }}">
                                             <span style="background: @if($id == 5) #fff @else #dedede @endif;width: 48px; padding-left: 4px; height: 48px; border-radius: 100%; display: flex; justify-content: center; align-items: center">
                                            <i style="font-size: 24px; color: #15549A" class="fa fa-clock-o"></i>
                                          </span>
                                        <span style="color: #000; font-size:16px ">{{ __('front.courier_delivery_status_5') }}</span>

                                        <span class="badge">{{ $counts[5] or null }}</span>
                                    </a>
                                </li>
                            </ul>
                            <!-- Tab panes -->
                            <div style="border: none; margin-top: 40px" class="tab-content">
                                <div role="tabpanel" class="tab-pane fade in active">
                                    @if ( request()->has('success'))
                                        <div class="alert alert-success"
                                             role="alert">{{ __('front.was_paid') }}</div>
                                    @endif
                                    @if ( request()->has('error'))
                                        <div class="alert alert-danger"
                                             role="alert">{{ request()->get('error') }}</div>
                                    @endif
                                    <div class="row mb40">
                                        <div style="text-align: center" >
                                            <h1 style="font-size: 24px;" >{{ __('front.courier_deliveries_page_title') }}</h1>
                                            <p style="font-size: 18px; font-weight: 500; color: #aaa">{!! __('front.courier_delivery_page_description') !!}</p>
                                        </div>
                                    </div>

                                </div>
                                <div class="row flex-esi">
                                    @if(!$user->azeri_express_use && !$user->surat_use && ($user->store_status == 1 ||  $user->store_status == 2) && !$user->azerpoct_send)
                                        <div style="padding: 0; " class="col-md-6 ">
                                            <a style="width: 100% ; text-align: center; background: #F74CA1; font-size: 16px" href="{{ route('cds.create') }}"
                                               class="button-blue-esi rounded-esi">{{ __('front.create_courier_delivery_title') }}</a>
                                        </div>
                                    @endif
                                    @if (session('deleted'))
                                        <div class="alert alert-danger"
                                             role="alert">{{ __('front.courier_delivery_was_deleted') }}2</div>
                                    @endif

                                    @if (session('success'))
                                        @if (session('id'))
                                            <div class="alert alert-success"
                                                 role="alert">{{ __('front.courier_delivery_was_updated') }}3</div>
                                        @else
                                            <div class="alert alert-success"
                                                 role="alert">{{ __('front.courier_delivery_was_created') }}4</div>
                                        @endif
                                    @endif
                                    @forelse($cds as $cd)
                                        <div class="compare-block mb30">
                                            <div class="compare-title bg-primary pinside20">
                                                <span class="date">{{ $cd->created_at->format('d.m.y') }}</span>
                                                <span class="label label-{{ $cd->status_info['label'] }}">{{ $cd->status_info['text'] }}</span>
                                                <span class="tracking-code">{{ $cd->packages_str}}</span>
                                            </div>
                                            <div class="compare-row outline pinside30">
                                                <div class="row">
                                                    <div class="col-md-2 col-sm-6 col-xs-12">
                                                        <div class="text-center  compare-action">
                                                            <h3 class="compare-rate">{{ $cd->delivery_price or '-' }}</h3>
                                                            <small>{{ __('front.courier_delivery_price') }}</small>
                                                        </div>
                                                    </div>
                                                    @if($cd->paid)
                                                        <div class="col-md-2 col-sm-6 col-xs-12">
                                                            <div class="text-center compare-action">
                                                                <button class="btn btn-success btn-sm"
                                                                        disabled>{{ trans('front.paid') }}</button>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="col-md-2 col-sm-6 col-xs-12">
                                                        </div>
                                                    @endif
                                                    @if(false && $cd->status<=0 && !$cd->paid)
                                                        <div class="col-md-3 col-sm-6 col-xs-12">
                                                            <div class="text-center compare-action">
                                                                {!! Form::open(['id' => 'cd_' . $cd->id, 'method' => 'delete', 'route' => ['cds.delete', $cd->id]]) !!}
                                                                {!! Form::close() !!}
                                                                <a onclick="document.getElementById('cd_<?= $cd->id; ?>').submit();"
                                                                   class="btn btn-danger btn-sm">{{ __('front.delete') }}</a>
                                                                <a href="{{ route('cds.edit', $cd->id) }}"
                                                                   class="btn btn-primary btn-sm">{{ __('front.edit') }}</a>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="col-md-3 col-sm-6 col-xs-12">
                                                            <div class="text-center compare-action">
                                                                <a href="{{ route('cds.show', $cd->id) }}"
                                                                   class="btn btn-primary btn-sm">{{ __('front.detailed') }}</a>
                                                            </div>
                                                        </div>
                                                        @if(in_array($cd->status,[0,1,2]))
                                                            <div class="col-md-2 col-sm-6 col-xs-12">
                                                                <div class="text-center compare-action">
                                                                    {!! Form::open(['id' => 'cd_' . $cd->id, 'method' => 'post', 'route' => ['cds.cancel', $cd->id]]) !!}
                                                                    {!! Form::close() !!}
                                                                    <a onclick="document.getElementById('cd_<?= $cd->id; ?>').submit();"
                                                                       class="btn btn-danger btn-sm">{{ __('front.cancel') }}</a>
                                                                </div>
                                                            </div>
                                                        @endif
                                                        @if($cd->status == 5)
                                                            <div class="col-md-2 col-sm-6 col-xs-12">
                                                                <div class="text-center compare-action">
                                                                    {!! Form::open(['id' => 'cd_' . $cd->id, 'method' => 'post', 'route' => ['cds.restore', $cd->id]]) !!}
                                                                    {!! Form::close() !!}
                                                                    <a onclick="document.getElementById('cd_<?= $cd->id; ?>').submit();"
                                                                       class="btn btn-info btn-sm">{{ __('front.restore') }}</a>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="alert-esi col-md-6"
                                             role="alert">{{ __('front.no_any_courier_delivery') }}
                                        </div>
                                    @endforelse
                                </div>
                                <div class="mt-20 text-center">
                                    {!! $cds->render() !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
