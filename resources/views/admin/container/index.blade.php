@extends(config('saysay.crud.layout'))

@section('content')

    <style>
        .panel-heading{
            display:flex;
            align-items: center;
            justify-content: space-between;
        }
    </style>

    <div class="row">
        <div class="col-lg-{{ $_view['listColumns'] }} col-lg-offset-{{ (12 - $_view['listColumns'])/2 }} col-md-12 col-xs-12">
            <div class="panel panel-flat">
                <div class="panel-heading">
                    <h6>
                        {{ isset($_view['name']) ? str_plural($_view['name']) : null }}
                        <small class="display-block"> Showing... {{ $items->firstItem() }} to {{ $items->lastItem() }}
                            of {{ number_format($items->total()) }} {{ $_view['sub_title'] or lcfirst(str_plural($_view['name'])) }}</small>
                    </h6>

                    <p></p>
                    <div class="">
                        @if(optional(auth()->user()->role)->id == 1)
                        <a href="/containers/create" class="btn btn-success">Create Container</a>
                        @endif
                    </div>
                </div>




                <div class="panel-body">
                    @php $fromCountry = true; @endphp
                    @include('crud::inc.filter-stack', ['fromCountry' => $fromCountry])
                    <?php $noNeed = true; ?>
                    <div class="table-responsive">
                        <table class="table table-hover responsive table-bordered">
                            <thead>
                            <tr>
                                @if (isset($_view['checklist']))
                                    <th>
                                        <input title="check" type="checkbox" class="styled" id="check_all"/>
                                    </th>
                                @endif
                                <th>MAWB/BAG #</th>

                                @foreach($_list as $key => $head)
                                    <th>{{ is_array($head) ? (array_key_exists('label', $head) ? $head['label'] : ucfirst(str_replace("_", " ", $key))) : ucfirst(str_replace("_", " ", $head)) }}</th>
                                @endforeach
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($items as $row => $item)

                                    <?php

                                    ?>
                                    <?php $ctId = $item->id; ?>
                                    <?php $ctName = $item->name; ?>
                                <tr>
                                    <td class="tab_it closed" data-bags="parcel_{{ $ctId }}"
                                        data-packages="parcel2_{{ $ctId }}" data-tabs="tab_{{ $ctId }}" }}>
                                        <i class="icon-minus2 minus"></i>
                                        <i class="icon-plus2 plus"></i>
                                        {{$ctName}}
                                    </td>
                                    <td>{{ $item->partner_with_label }}</td>
                                    <td>
                                        @php
                                            $key = 'partner.country';
                                            $type = 'country';
                                            $entry = parseRelation($item, $key);
                                        @endphp
                                        @if(view()->exists('admin.crud.columns.' . $type))
                                            @include('admin.crud.columns.' . $type)
                                        @else
                                            @include('crud::components.columns.' . $type )
                                        @endif
                                    </td>
                                    <td colspan="2"></td>

                                    @if(!in_array(optional(auth()->user()->role)->id, [1, 4]) && $item->partner_id == 3)
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    @else


                                    <td>{{ $item->tracks->sum('weight') }} kg
                                        / {{ $item->tracks->sum('declared_weight_goods') }} kg
                                    </td>
                                    <td>    {{ $item->tracks_count }}  @if($item->track_not_completed_count)
                                            /<a
                                                    href="{{ route('tracks.index') }}?dec=2&partner_id={{ $item->partner_id }}&limit=25&parcel={{ $item->name }}"
                                                    class="waiting_packages"
                                                    style="background: red;color: #fff;padding: 5px;border-radius: 5px;">{{ $item->track_not_completed_count }}</a>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $item->tracks_count }} /
                                        <div class="cr_b_prcl_ct">
                                            @if($item->trackcarriers_count)
                                                <div class="cr_b_prcl_ok">{{ $item->trackcarriers_count }}</div>
                                            @else
                                                <div class="cr_b_prcl_no"> &nbsp&nbsp&nbsp&nbsp</div>
                                            @endif
                                        </div>
                                        <div class="cr_b_prcl_ct">
                                            @if($item->trackcarriersreg_count)
                                                <div class="cr_b_prcl_ok">{{ $item->trackcarriersreg_count }}</div>
                                            @else
                                                <div class="cr_b_prcl_no"> &nbsp&nbsp&nbsp&nbsp</div>
                                            @endif
                                        </div>
                                        <div class="cr_b_prcl_ct">
                                            @if($item->trackcarriersdepesh_count)
                                                <div class="cr_b_prcl_ok">{{ $item->trackcarriersdepesh_count }}</div>
                                            @else
                                                <div class="cr_b_prcl_no"> &nbsp&nbsp&nbsp&nbsp</div>
                                            @endif
                                        </div>
                                    </td>

                                    @endif


                                    <td>
                                        <font color=green>
                                            @if($item->first_scanned_at)
                                                {{ $item->first_scanned_at }}
                                            @endif
                                            @if($item->scanned_cnt)
                                                ({{ $item->scanned_cnt }})
                                            @endif
                                        </font>
                                    </td>
                                    <td>{{ $item->status_with_label }}</td>
                                    <td>{{ $item->from_country }}</td>
                                    <td>{{ $item->created_at->diffForHumans() }}</td>
                                    <td>
                                        @include('crud::inc.button-stack')
                                    </td>
                                </tr>
                                    <?php $p_item = $item; ?>
                                @foreach($item->tracks as $item)
                                    @if(!$item->airbox_id)
                                        <tr class="sub-child  parcel2_{{ $ctId }}   parcel_{{ $ctId }} bag_">
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
                                            <td> @include('crud::inc.button-stack',['extraActions' => $extraActionsForPackage, 'noNeed' => true])</td>
                                        </tr>
                                    @endif
                                @endforeach
                                @foreach($p_item->airboxes as $item)
                                    @if($item->tracks_count > 0)
                                        <tr class="sub-child  parcel_{{ $ctId }}">
                                                <?php $bagId = $item->id; ?>
                                            <td class="tab_it2 closed tab_{{ $ctId }}" data-packages="bag_{{ $bagId }}">
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                <i class="icon-minus2 minus"></i>
                                                <i class="icon-plus2 plus"></i>
                                                {{$item->name}}
                                            </td>
                                            <td>{{ $item->partner_with_label }}
                                            <td colspan="3"></td>

                                            @if(!in_array(optional(auth()->user()->role)->id, [1, 4]) && $item->partner_id == 3)
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                            @else
                                                <td>{{ $item->tracks->sum('weight') }} kg</td>
                                                <td>{{ $item->tracks_count }}  @if($item->track_not_completed_count)
                                                        /<a
                                                                href="{{ route('tracks.index') }}?dec=2&partner_id={{ $item->partner_id }}&limit=25&parcel={{ $p_item->name }}&bag={{ $item->name }}"
                                                                class="waiting_packages"
                                                                style="background: red;color: #fff;padding: 5px;border-radius: 5px;">{{ $item->track_not_completed_count }}</a>
                                                    @endif
                                                </td>
                                                <td>{{ $item->tracks_count }} /
                                                    <div class="cr_b_prcl_ct">
                                                        @if($item->trackcarriers_count)
                                                            <div class="cr_b_prcl_ok">{{ $item->trackcarriers_count }}</div>
                                                        @else
                                                            <div class="cr_b_prcl_no"> &nbsp&nbsp&nbsp&nbsp</div>
                                                        @endif
                                                    </div>
                                                    <div class="cr_b_prcl_ct">
                                                        @if($item->trackcarriersreg_count)
                                                            <div class="cr_b_prcl_ok">{{ $item->trackcarriersreg_count }}</div>
                                                        @else
                                                            <div class="cr_b_prcl_no"> &nbsp&nbsp&nbsp&nbsp</div>
                                                        @endif
                                                    </div>
                                                    <div class="cr_b_prcl_ct">
                                                        @if($item->trackcarriersdepesh_count)
                                                            <div class="cr_b_prcl_ok">{{ $item->trackcarriersdepesh_count }}</div>
                                                        @else
                                                            <div class="cr_b_prcl_no"> &nbsp&nbsp&nbsp&nbsp</div>
                                                        @endif
                                                    </div>
                                                </td>
                                            @endif

                                            <td>{{ $item->status_with_label }}</td>
                                            <td>{{ $item->created_at->diffForHumans() }}</td>
                                            <td> @include('crud::inc.button-stack',['extraActions' => $extraActionsForBag, 'noNeed' => true])</td>
                                        </tr>
                                        @foreach($item->tracks as $item)
                                            <tr class="sub-child  parcel2_{{ $ctId }} bag_{{ $bagId }}">
                                                <td></id>
                                                @foreach($_list as $key => $head)
                                                    <td>
                                                        @php
                                                            $key = is_array($head) ? $key : $head;
                                                            $type = isset($head['type']) ? $head['type'] : 'text';
                                                            $entry = parseRelation($item, $key);
                                                        @endphp


                                                        @if(!in_array(optional(auth()->user()->role)->id, [1, 4]) && $item->partner_id == 3 and in_array($key,['weight','number_items','carrier']))

                                                        @else
                                                            @if(view()->exists('admin.crud.columns.' . $type))
                                                                @include('admin.crud.columns.' . $type)
                                                            @else
                                                                @include('crud::components.columns.' . $type )
                                                            @endif
                                                        @endif


                                                    </td>
                                                @endforeach
                                                <td> @include('crud::inc.button-stack',['extraActions' => $extraActionsForPackage, 'noNeed' => true])</td>
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


    <div class="modal fade" id="updateModal" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateModalLabel">Update Container Name</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="updateContainerForm" action="" method="POST">
                        <div class="form-group">
                            <label for="containerName">Container Name</label>
                            <input type="text" class="form-control" id="containerName" name="containerName"
                                   placeholder="Enter new container name">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveContainerName">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="updateMawb" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateModalLabel">Update Container Name</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="updateMawbForm" action="" method="POST">
                        <div class="form-group">
                            <label for="bagName">Bag Name</label>
                            <input type="text" class="form-control" id="bagName" name="bagName"
                                   placeholder="Enter new bag name">
                        </div>
                        <div class="form-group">
                            <label for="trackings">Tracks</label>
                            <textarea class="form-control" id="trackings" name="trackings" rows="4"
                                      placeholder="Enter new trackings"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveMawb">Save changes</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('js')
    <script>
        $(document).ready(function () {
            $('#updateModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var url = button.data('url');
                var id = button.data('id');
                $('#containerName').val(button.data('name'));
                $('#saveContainerName').attr('data-url', url);
                $('#saveContainerName').attr('data-id', id);
            });

            $('#saveContainerName').on('click', function (e) {
                e.preventDefault();
                var url = $(this).data('url');
                var containerName = $('#containerName').val();

                var button = $(event.relatedTarget);
                var id = $(this).data('id');
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        containerName: containerName
                    },
                    success: function (response) {
                        alert('Container name updated successfully!');
                        var tdElement = $('table td[data-tabs="tab_' + id + '"]');
                        tdElement.contents().filter(function () {
                            return this.nodeType === 3;
                        }).remove();
                        tdElement.append(' ' + containerName);
                        $('#updateModal').modal('hide');
                    },
                    error: function (xhr, status, error) {
                        alert('Error updating container name: ' + error);
                    }
                });
            });

            $('#updateMawb').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var url = button.data('url');
                var id = button.data('id');
                $('#saveMawb').attr('data-url', url);
                $('#saveMawb').attr('data-id', id);
            });

            $('#saveMawb').on('click', function (e) {
                e.preventDefault();
                var url = $(this).data('url');
                var bagName = $('#bagName').val();
                var trackings = $('#trackings').val();

                var button = $(event.relatedTarget);
                var id = $(this).data('id');
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        bagName: bagName,
                        trackings: trackings
                    },
                    success: function (response) {
                        alert(response.message);
                        $('#updateModal').modal('hide');
                        if (response.success == true) {
                            setTimeout(function () {
                                location.reload();
                            }, 2000);
                        }
                    },
                    error: function (xhr, status, error) {
                        alert('Error adding tracking and bags: ' + error);
                    }
                });
            });
        });
    </script>
@endpush
