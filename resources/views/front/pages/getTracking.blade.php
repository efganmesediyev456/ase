@extends('front.layout')

@section('content')
    @include('front.sections.page-header')
    @php
        const STATES = [
            'WaitingDomesticShipment' => 0,     // when package created on Unitrade
            'OnTransfer' => 0,     // when package on transfer to Unitrade
            'Received' => 1,     // when Unitrade accept package
            'WaitingForDeclaration' => 1,     // when Unitrade send package to ase
            'SentToCustoms' => 5,
            'ExternalStorage' => 7,     // when User(customer) declared package and ase send  /declarePackage api request
            'WaitingForAirTransport' => 8,     // when Unitrade adds package to box
            'OnWay' => 12,     // when Unitrade send package
            'StoppedInCustoms' => 18,    // when package is undergoing customs clearance procedures.
            'CustomsClearance' => 44,    // when package is undergoing customs clearance procedures.
            'CustomsCompleted' => 25,     //
            'Sorting' => 20,    // The package sorting in a Carrier's internal storage facility.
            'PudoAccepted' => 24,    // The package is on at Pudo(Carrier's internal storage facility).
            'AtPudo' => 16,    // The package is on at Pudo(Carrier's internal storage facility).
            'OutForDelivery' => 21,     // The package is out for delivery and should reach the destination soon.
            'Delivered' => 17,    // The package has been successfully delivered to its destination.
            'FailedAttempt' => 22,     // The package has been returned for some reason. Waiting for delivery at the Pudo. (courier packages)
            'Undelivered' => 23,    //
            'Deleted' => 4,     // The package information has been deleted from the system.
            'Rejected' => 19,     // The package information has been deleted from the system.
        ];


        $statuses = [
            trans('front.in_warehouse') => [1, 5, 7, 14, 8],
            trans('front.on_way') => [44, 25, 12],
            trans('front.sorting') => [20],
            trans('front.pudo') => [16],
            trans('front.out_for_delivery') => [21, 22, 23],
            trans('front.delivered') => [17],
            trans('front.incustoms') => [18],
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

    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-md-8">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                    {!! Form::open(['route' => 'tracking', 'method' => 'get' ]) !!}
                    <div class="form-group">
                        <label for="trackingNumber">Tracking code</label>
                        <input type="text" name="tracking_code" id="tracking_code" class="form-control" value="{{ old('tracking_code') }}" required>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3" style="margin-bottom: 20px;">{{trans('front.search')}}</button>
                    {!! Form::close() !!}
            </div>
        </div>

        @if (isset($track))
            <div class="row justify-content-center mt-5">
                <div class="col-md-8">
                    <div class="alert alert-info">
                        @if (isset($track))
                            <h5 style="color: white;">Track Details:</h5>
                            <p><strong>Tracking code:</strong> {{ $track->tracking_code }}</p>
                            <p><strong>Status:</strong>
                                @if($track->status == 16)
                                    @if($track->carrier_status_label_tracking == 'Məntəqəyə çatıb')
                                        {{ getStatusLabel($track->status,$statuses) }}
                                    @else
                                        {{ getStatusLabel(20,$statuses) }}
                                    @endif
                                @else
                                    {{ getStatusLabel($track->status,$statuses) }}
                                @endif
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

@endsection
