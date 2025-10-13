@php
    const STATES = [
        'WaitingDomesticShipment' => 0,
        'OnTransfer' => 0,
        'Received' => 1,
        'WaitingForDeclaration' => 1,
        'SentToCustoms' => 5,
        'ExternalStorage' => 7,
        'WaitingForAirTransport' => 8,
        'OnWay' => 12,
        'StoppedInCustoms' => 18,
        'CustomsClearance' => 44,
        'CustomsCompleted' => 25,
        'Sorting' => 20,
        'PudoAccepted' => 24,
        'AtPudo' => 16,
        'OutForDelivery' => 21,
        'Delivered' => 17,
        'FailedAttempt' => 22,
        'Undelivered' => 23,
        'Deleted' => 4,
        'Rejected' => 19,
    ];


    $statuses = [
        trans('front.in_warehouse') => [1, 5, 7, 14, 8],
        trans('front.on_way') => [44, 25, 12,46],
        trans('front.sorting') => [20],
        trans('front.pudo') => [16],
        trans('front.out_for_delivery') => [21, 22, 23],
        trans('front.delivered') => [17],
        trans('front.incustoms') => [18],
        trans('front.scdwp') => [45],
        trans('front.not_arrived') => [26],
    ];

    function getStatusLabel($id, $statuses) {
        foreach ($statuses as $key => $statusArray) {
            if (in_array($id, $statusArray)) {
                return $key;
            }
        }
        return null;
    }
@endphp


<div class="section-space80">
    <div class="custom-container">
        <div style="position: relative" class=" bg-blue-esi">
            <div>
                <div class="flex-esi">
                    <div class="flex-esi-inside">
                        <h1>{{ __('front.tracking.title') }}</h1>
                        <p>{{ __('front.tracking.sub_title') }}</p>
                        {!! Form::open(['route' => 'tracking', 'method' => 'get', 'class' => 'text-center']) !!}
                        <div class="inputs">
                            <input autocomplete="off" onclick="showTrackingModal()" type="text" name="tracking_code"
                                   id="tracking_code"
                                   style="color: #15549A; font-size: 16px; font-weight: 400; outline: none"
                                   placeholder="{{ __('front.tracking_code') }}" required/>
                            <button type="submit">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15.5 14H14.71L14.43 13.73C15.41 12.59 16 11.11 16 9.5C16 5.91 13.09 3 9.5 3C5.91 3 3 5.91 3 9.5C3 13.09 5.91 16 9.5 16C11.11 16 12.59 15.41 13.73 14.43L14 14.71V15.5L19 20.49L20.49 19L15.5 14ZM9.5 14C7.01 14 5 11.99 5 9.5C5 7.01 7.01 5 9.5 5C11.99 5 14 7.01 14 9.5C14 11.99 11.99 14 9.5 14Z"
                                          fill="#fff"/>
                                </svg>
                            </button>
                        </div>
                        {!! Form::close() !!}
                    </div>
                    <div class="flex-header">
                        <img class="img1" src="{{ asset('front/new/headerflight.png') }}" alt="fly"/>
                        <img class="img2" src="{{ asset('front/new/glob.png') }}" alt="fly"/>
                    </div>
                    {{--                    <div class="bg-globe"></div>--}}
                </div>
            </div>
            <div id="trackingModal"
                 style="@if(!$errors->any() && !isset($track)) display: none; @endif  background: rgba(255,255,255,0.62); position: absolute; top: -20px; left: -10px; right: -10px; bottom: -20px;  padding: 36px 40px;backdrop-filter: blur(8px);-webkit-backdrop-filter: blur(8px); box-shadow: 0 0 20px 20px rgba(255,255,255,0.47)">

                <div style="max-width: 420px">
                    <h4 style="color: #15549A; font-weight: 500; font-size: 36px">{{ __('front.tracking.addtrek') }}</h4>
                    {!! Form::open(['route' => 'tracking', 'method' => 'get', 'class' => 'text-center']) !!}
                    <div style="max-width: 420px" class="inputs">
                        <input oninput="" type="text" name="tracking_code" id="tracking_code"
                               style="color: #15549A; font-size: 16px; font-weight: 400; outline: none;@if($errors->any()) border: 1px solid #FF0000; @else border: 1px solid #15549A; @endif  width: 85%"
                               placeholder="{{ __('front.tracking_code') }}" required/>
                        <button type="submit" style="background: #15549A;margin: 0">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path d="M15.5 14H14.71L14.43 13.73C15.41 12.59 16 11.11 16 9.5C16 5.91 13.09 3 9.5 3C5.91 3 3 5.91 3 9.5C3 13.09 5.91 16 9.5 16C11.11 16 12.59 15.41 13.73 14.43L14 14.71V15.5L19 20.49L20.49 19L15.5 14ZM9.5 14C7.01 14 5 11.99 5 9.5C5 7.01 7.01 5 9.5 5C11.99 5 14 7.01 14 9.5C14 11.99 11.99 14 9.5 14Z"
                                      fill="#fff"/>
                            </svg>
                        </button>
                    </div>
                    {!! Form::close() !!}
                </div>

                @if ($errors->any())
                    <div style="margin-top: 14px; max-width: 420px">
                        <ul style="list-style: none ; max-width: 420px">
                            @foreach ($errors->all() as $error)
                                <li style="border: 1px solid #ff0000; background: #fff;padding:8px 12px; color: #ff0000;border-radius: 8px; font-size: 18px ">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>

                @elseif (isset($track))
                    <div class="" style="max-width: 420px">
                        <div style="background: #fff; border: 1px solid #15549A; padding: 24px; margin-top: 14px; max-width: 420px"
                             class="rounded-esi3">
                            @if (isset($track))
                                <h5 style="color: #888; font-size: 18px; font-weight: 500">{{ __('front.tracking.trakdetail') }}
                                    :</h5>
                                <p style="color: #15549A; font-size: 18px; padding-bottom: 10px"><strong
                                            style="color: #15549A;">{{ __('front.tracking.traccode') }}
                                        :</strong> {{ $track->tracking_code }}</p>
                                <p style="padding: 0; margin: 0; color: #15549A">
                                {{--                                    <span style="color: #15549A; font-size: 18px; font-weight: 400">{{ __('front.tracking.trakpay') }}:</span>--}}
                                @if($track->status == 16)
                                    @if($track->carrier_status_label_tracking == 'Məntəqəyə çatıb')
                                        {{ getStatusLabel($track->status,$statuses) }}
                                    @elseif($track->store_status == 2)
                                        {{ getStatusLabel($track->status,$statuses) }}
                                    @else
                                        {{ getStatusLabel(20,$statuses) }}
                                    @endif
                                @else
                                    {{ getStatusLabel($track->status,$statuses) }}
                                @endif
                                @if($track->paid == 0 and $track->partner_id==9 and !in_array($track->status, [0,1,5,7,14,8]))
                                    <p><strong><a target="_blank"
                                                  href="https://aseshop.az/track/pay/{{ $track->custom_id }}"
                                                  class="btn btn-warning"> {{ __('front.tracking.title') }} <i
                                                        class="icon-arrow-right14"></i></a> </strong></p>
                                    @endif
                                    </p>
                                @endif
                        </div>
                    </div>
                @else
                    <div style="margin-top: 14px; ">
                        <span style="font-size: 18px; font-weight: 400;">{{ __('front.tracking.trakexample') }}:</span>
                        <div style="font-size: 20px; color: #15549A; font-weight: 500; margin-top: 16px">UK123456789
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    function showTrackingModal() {
        document.getElementById('trackingModal').style.display = 'block';
        document.getElementById('trackingdiv').style.display = 'none';
    }

    document.getElementById('trackingModal').addEventListener('click', function (e) {
        if (e.target === this) {
            document.getElementById('trackingModal').style.display = 'none';
            document.getElementById('trackingdiv').style.display = 'block';
        }
    });
    // Close modal when clicking outside of it
    document.addEventListener('click', function (event) {
        const modal = document.getElementById('trackingModal');
        const input = document.getElementById('tracking_code');

        if (event.target !== input && !modal.contains(event.target)) {
            modal.style.display = 'none';
        }
    });
</script>
