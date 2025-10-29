<?php

use App\Models\Container;
use App\Models\Extra\Notification;
use App\Models\Package;
use App\Models\Precinct\PrecinctPackage;
use App\Models\Track;
use App\Services\Package\PackageService;
use App\Services\Precinct\PrecinctService;

public function barcodeScan($code = null)
{

    if (!$code) {
        return response()->json([
            'error' => 'Empty barcode. Please scan a package!',
        ]);
    }

    if ($code == 'courier-page') {
        return response()->json([
            'redirect' => route('courier.shelf.add.product'),
        ]);
    }

    $scanPath = '';
    if (\Request::has('path'))
        $scanPath = \Request::get('path');
    // Check barcode
    $cell = findCell($code);


    if (!empty($cell)) {
        return response()->json([
            'cell' => $cell
        ]);
    }

    $admin = Auth::user();

    $track = Track::query()->where('tracking_code', $code)->first();
    $package = null;
    if (!$track) {
        $package = Package::whereTrackingCode($code)->orWhere('custom_id', $code)->first();
    }

//        if (isset($track) && in_array($track->status, [19, 27])) {
//            $track->scanned_at = Carbon::now();
//            $track->save();
//            //(new PackageService())->updateStatus($track, 19);
//            return response()->json([
//                'error' => 'Rejected statusunda olan bağlama',
//            ]);
//        }

    if (isset($track) && in_array($track->status, [45])) {
        $track->scanned_at = Carbon::now();
        $track->save();
        (new PackageService())->updateStatus($track, 44);
        return response()->json([
            'error' => 'Saxlanc statusunda olan bağlama',
        ]);
    }

    if ($package) {

        if($admin->check_declaration){

            $package->bot_comment = "Saxlanc hesabı tərəfindən scan edildi.";
            $package->save();
            if(!$package->is_in_customs){
                return response()->json([
                    'error' => 'NO DECLARATION IN CUSTOMS '. $package->custom_id,
                ]);

            }else{

                return response()->json([
                    'success' => 'DECLARED IN CUSTOMS '. $package->custom_id,
                ]);
            }

        }

        $user = $package->user;
        if (!app('laratrust')->can('update-cells') && app('laratrust')->can('update-paids')) {
            return response()->json([
                'redirect' => route('paids.index', ['cwb' => $package->custom_id]),
            ]);
        }
//            if ($admin->scan_check_only && !$admin->scan_no_alerts) {
//                $message = '';
//                if($package->debt_price && $package->paid_debt == 0){
//                    $message = "Baglamanin saxlanc odenisi var($package->debt_price) ve ÖDƏNİlMƏYİB!";
//                }else{
//                    $message = "Bağlamanın saxlanc ödənişi var($package->debt_price) ve ödənilib!";
//                }
//
//                return response()->json([
//                    'error' => $message,
//                ]);
//            }
        $status = $package->status;

//            if (!in_array($package->store_status, [1,3,4,7,8]) && $package->paid == 0){
//                $message = 'PACKAGE NOT PAID !';
//                return response()->json([
//                    'error' => $message,
//                ]);
//            }
        $notification = false;
        /* Send Notification */
        if ($admin->store_status == 2) { //In Kobia
            if ($status != 8) {
                $package->status = 8;
                $package->save();
                $notification = true;
                //Send notification only if user selected kobia filial
                if ($user && !$user->real_azeri_express_use && !$user->real_azerpoct_send && !$user->real_yenipoct_use && !$user->real_kargomat_use && ($user->real_store_status == $admin->store_status) && $user->delivery_point) {
                    Notification::sendPackage($package->id, 8);
                }
            }
        } else { // In Baku
            if ($status != 2) {
                if (($package->store_status && $package->delivery_point)
                    || ($package->azeri_express_office_id && $package->azeri_express_office)
                    || ($package->surat_office_id && $package->surat_office)
                    || ($package->yenipoct_office_id && $package->yenipoct_office)
                    || ($package->kargomat_office_id && $package->kargomat_office)
                    || ($package->azerpost_office_id && $package->azerpost_office)) {
                    if (($package->store_status && $package->delivery_point) && ($package->store_status != $admin->store_status)) {
                        $message = ' WRONG PACKAGE FILIAL ! ' . $package->delivery_point->description . ' Send to Kobia';
                        return response()->json([
                            'error' => $message,
                        ]);
                    }
                    if ($package->azeri_express_office_id && $package->azeri_express_office) {
                        $message = ' WRONG PACKAGE AZERI EXPRESS ! ' . $package->azeri_express_office->description . ' Send to Kobia';
                        return response()->json([
                            'error' => $message,
                        ]);
                    }
                    if ($package->yenipoct_office_id && $package->yenipoct_office) {
                        $message = ' WRONG PACKAGE YENI POCT ! ' . $package->yenipoct_office->description . ' Send to Kobia';
                        return response()->json([
                            'error' => $message,
                        ]);
                    }
                    if ($package->kargomat_office_id && $package->kargomat_office) {
                        $message = ' WRONG PACKAGE Kargomat ! ' . $package->kargomat_office->description . ' Send to Kobia';
                        return response()->json([
                            'error' => $message,
                        ]);
                    }
                    if ($package->surat_office_id && $package->surat_office) {
                        $message = ' WRONG PACKAGE SURAT CARGO ! ' . $package->surat_office->description . ' Send to Kobia';
                        return response()->json([
                            'error' => $message,
                        ]);
                    }
                    if ($package->azerpost_office_id && $package->azerpost_office) {
                        $message = ' WRONG USER AZERPOST ! ' . strtoupper($package->azerpost_office->name) . ' Send to Kobia';
                        return response()->json([
                            'error' => $message,
                        ]);
                    }
                } else {
                    if ($user && !$user->real_azeri_express_use && !$user->real_azerpoct_send && ($user->real_store_status != $admin->store_status) && $user->delivery_point) {
                        $message = ' WRONG UER FILIAL ! ' . $user->delivery_point->description . ' Send to Kobia';
                        return response()->json([
                            'error' => $message,
                        ]);
                    }
                    if ($user && $user->real_azeri_express_use && $user->real_azeri_express_office_id && $user->azeri_express_office) {
                        $message = ' WRONG USER AZERI EXPRESS ! ' . $user->azeri_express_office->description . ' Send to Kobia';
                        return response()->json([
                            'error' => $message,
                        ]);
                    }
                    if ($user && $user->real_surat_use && $user->real_surat_office_id && $user->surat_office) {
                        $message = ' WRONG USER SURAT CARGO ! ' . $user->surat_office->description . ' Send to Kobia';
                        return response()->json([
                            'error' => $message,
                        ]);
                    }
                    if ($user && $user->real_yenipoct_use && $user->real_yenipoct_office_id && $user->yenipoct_office) {
                        $message = ' WRONG USER YENIPOCT CARGO ! ' . $user->yenipoct_office->description . ' Send to Kobia';
                        return response()->json([
                            'error' => $message,
                        ]);
                    }
                    if ($user && $user->real_kargomat_use && $user->real_kargomat_office_id && $user->kargomat_office) {
                        $message = ' WRONG USER Kargomat CARGO ! ' . $user->kargomat_office->description . ' Send to Kobia';
                        return response()->json([
                            'error' => $message,
                        ]);
                    }
                    if ($user && $user->real_azerpoct_send && $user->real_zip_code && $user->azerpost_office) {
                        $message = ' WRONG USER AZERPOST ! ' . strtoupper($user->azerpost_office->name) . ' Send to Kobia';
                        return response()->json([
                            'error' => $message,
                        ]);
                    }
                }
                $package->status = 2;
                $package->save();
                $notification = true;
                Notification::sendPackage($package->id, 2);
            }
        }
        if (!$package->scanned_at) {
            $notification = true;
            $package->scanned_at = Carbon::now();
            $package->save();
            if ($package->parcel && $package->parcel->count()) {
                $parcel = $package->parcel->first();
                if (!$parcel->first_scanned_at)
                    $parcel->first_scanned_at = $package->scanned_at;
                $parcel->scanned_cnt++;
                $parcel->save();
            }
            if ($package->bag && $package->bag->count()) {
                $bag = $package->bag->first();
                if (!$bag->first_scanned_at)
                    $bag->first_scanned_at = $package->scanned_at;
                $bag->scanned_cnt++;
                $bag->save();
            }
        }
        if (app('laratrust')->can('update-cells') /*&& !$package->cell*/) {
            //Percint filial (store_status) is equal to admin's filial then it arrived filial and must be accepted
            if ($package->store_status && $package->store_status == $admin->store_status) {
                $precintContainerCheck = PrecinctPackage::where('barcode',$package->custom_id)->first();
                //if it is not sended by kobia workers can not scan
                if ($precintContainerCheck && $precintContainerCheck->status == PrecinctPackage::STATUSES['NOT_SENT']){
                    return response()->json([
                        'error' => 'Baglama gonderilib statusunda deyil',
                    ]);
                }
                if ($admin->role_id != 1) {
                    $pService = new PrecinctService();
                    $pService->acceptPackage($package->custom_id);
                    if ($package->delivery_point) {
                        $package->bot_comment = "Received at " . $package->delivery_point->description;
                        $package->save();
                    }
                }
            }
            //---------
            $message = NULL;
            $cd = $package->courier_delivery;
            if ($cd && $cd->courier && $admin->store_status == 2) {
                $message = ' Package KURYER: ' . $cd->courier->name;
                return response()->json([
                    'success' => $message,
                ]);
            } else {
                return response()->json([
                    'redirect' => route('cells.edit', $package->id),
                ]);
            }
        } else {
            /*if ($package->azerpoct_send) {
                return response()->json([
                    'success' => "This package has to be send to Azerpost. City: " . $user->city_name . ", Postal: " . strtoupper($user->zip_code),
                ]);
            }*/

            return response()->json([
                'redirect' => route('packages.index', ['q' => $package->custom_id]),
            ]);
            //return response()->json([
            //    'success' => $notification ? 'Notification was sent' : 'You have already scanned this package :-)',
            //]);
        }
    }

    if ($track) {
        if($admin->check_declaration){
            $track->bot_comment = "Saxlanc hesabı tərəfindən scan edildi.";
            $track->save();
            if($track->carrier && !$track->carrier->status && !$track->carrier->depesH_NUMBER){
                return response()->json([
                    'error' => 'NO DECLARATION IN CUSTOMS '. $track->tracking_code,
                ]);

            }else{

                return response()->json([
                    'success' => 'DECLARED IN CUSTOMS '. $track->tracking_code,
                ]);
            }

        }

//            if($track->tracking_code = 'TEST1232142'){
//                $track->status = 20;
//                $track->save();
//            };
//            if (!in_array($track->store_status, [1,3,4,7,8]) && $track->paid == 0){
//                $message = 'PACKAGE NOT PAID !';
//                return response()->json([
//                    'error' => $message,
//                ]);
//            }

        $add_message = "";
        if ($track->container_id)
            $add_message .= " \nMAWB: " . $track->container_name;
        if ($scanPath == 'tracks') {
            return response()->json([
                'redirect' => route('tracks.index', ['q' => $track->tracking_code]),
            ]);
        }
        if (!app('laratrust')->can('update-cells') && app('laratrust')->can('update-paids')) {
            return response()->json([
                'redirect' => route('paids.index', ['cwb' => $track->tracking_code]),
            ]);
        }
        if (!$track->scanned_at) {
            $track->scanned_at = Carbon::now();
            $track->save();
            if ($track->container_id) {
                $container = Container::find($track->container_id);
                if ($container && $container->first_scanned_at == null){
                    $container->first_scanned_at = $track->scanned_at;
                    $container->status = 16;
                }
                $container->scanned_cnt++;
                $container->save();
            }

        }

        if ($admin->store_status == 2 && in_array($track->partner_id, [3, 8, 9]) && !$track->scan_no_check) { //InKOBIA admin GFS & Ozon check
            if ($track->in_customs_status) {
                if ($track->status != 18 && (!$admin->scan_check_only || !$admin->scan_no_alerts)) {
                    $track->status = 18;
                    $track->bot_comment = "Scanned but Different price";
                    $track->save();
                    (new PackageService())->updateStatus($track, 18);
                    Notification::sendTrack($track->id, 'track_scan_diff_price');
                }
                if (!$admin->scan_no_alerts) {
                    $message = "DIFFERENT PRICE" . $add_message;
                    return response()->json([
                        'error' => $message,
                    ]);
                }
            }
            if (!$track->carrier) {
                if ($track->status != 18 && (!$admin->scan_check_only || !$admin->scan_no_alerts)) {
                    $track->status = 18;
                    $track->bot_comment = "Scanned but not IN  Customs";
                    $track->save();
                    (new PackageService())->updateStatus($track, 18);
                    Notification::sendTrack($track->id, $track->status);
                }

                if (!$admin->scan_no_alerts) {
                    $message = "NOT IN CUSTOMS" . $add_message;
                    return response()->json([
                        'error' => $message,
                    ]);
                }
            }
            if ($track->carrier && !$track->carrier->status && !$track->carrier->depesH_NUMBER) {
                if ($track->status != 18 && (!$admin->scan_check_only || !$admin->scan_no_alerts)) {
                    $track->status = 18;
                    $track->bot_comment = "Scanned but no declaration in Customs";
                    $track->save();
                    (new PackageService())->updateStatus($track, 18);
                    Notification::sendTrack($track->id, 'track_scan_no_dec');
                }

                if (!$admin->scan_no_alerts) {
                    $message = "NO DECLARATION IN CUSTOMS" . $add_message;
                    return response()->json([
                        'error' => $message,
                    ]);
                }
            }
            if ($track->carrier && !$track->carrier->depesH_NUMBER) {
                if ($track->status != 18 && (!$admin->scan_check_only || !$admin->scan_no_alerts)) {
                    $track->status = 18;
                    $track->bot_comment = "Scanned but no Depesh in Customs";
                    $track->save();
                    (new PackageService())->updateStatus($track, 18);
                    //Notification::sendTrack($track->id, $track->status);
                }

                if (!$admin->scan_no_alerts) {
                    $message = "NO DEPESH IN CUSTOMS" . $add_message;
                    return response()->json([
                        'warning' => $message,
                    ]);
                }
            }
            /*if(!$track->carrier->depesH_NUMBER) {
                $message="NO DEPESH IN CUSTOMS";
                        return response()->json([
                            'error' => $message,
                        ]);
            }*/
        }
        if ($admin->scan_check_only && !$admin->scan_no_alerts) {
            $message = $track->partner_with_label . ' Track ' . $track->worker_comments . $add_message;
            $cd = $track->courier_delivery;
            if ($cd && $cd->courier /*&& $admin->store_status == 2*/) {
                $message .= ' KURYER: ' . $cd->courier->name . $add_message;
                $message .= ' ....';
                //$cd->status = 2;
                //$cd->save();
            }
            return response()->json([
                'success' => $message,
            ]);
        }

        $notification = false;
        $status = $track->status;
        if ($admin->store_status == 2 && !($track->store_status == 2 && in_array($track->partner_id, [3, 8, 9]))) { //In Kobia
            if (!($track->partner_id == 3 && in_array($status, [27, 28]))) {
                if ($status <= 16 || in_array($status, [18, 19, 21, 22, 23, 25, 44])) {
                    $track->status = 20;
                    $notification = true;
                }
            }
        } else { // In Baku
            //check for wrong filial
            if (($track->store_status && $track->delivery_point) && ($track->store_status != $admin->store_status)) {
                $message = ' WRONG TRACK FILIAL ! ' . $track->delivery_point->description . ' Send to Kobia';
                return response()->json([
                    'error' => $message,
                ]);
            }
            if ($track->azeri_express_office_id && $track->azeri_express_office) {
                $message = ' WRONG TRACK AZERI EXPRESS ! ' . $track->azeri_express_office->description . ' Send to Kobia';
                return response()->json([
                    'error' => $message,
                ]);
            }
            if ($track->surat_office_id && $track->surat_office) {
                $message = ' WRONG TRACK SURAT CARGO ! ' . $track->surat_office->description . ' Send to Kobia';
                return response()->json([
                    'error' => $message,
                ]);
            }
            if ($track->yenipoct_office_id && $track->yenipoct_office) {
                $message = ' WRONG TRACK YENIPOCT CARGO ! ' . $track->yenipoct_office->description . ' Send to Kobia';
                return response()->json([
                    'error' => $message,
                ]);
            }
            if ($track->kargomat_office_id && $track->kargomat_office) {
                $message = ' WRONG TRACK KARGOMAT CARGO ! ' . $track->kargomat_office->description . ' Send to Kobia';
                return response()->json([
                    'error' => $message,
                ]);
            }
            if ($track->azerpost_office_id && $track->azerpost_office) {
                $message = ' WRONG TRACK AZERPOST ! ' . strtoupper($track->azerpost_office->name) . ' Send to Kobia';
                return response()->json([
                    'error' => $message,
                ]);
            }
            //-----------
            if (!($track->partner_id == 3 && in_array($status, [27, 28]))) {
                if ($status < 16 || in_array($status, [18, 19, 20, 21, 22, 23, 25, 44])) {
                    $track->status = 16;
                    $notification = true;
                }
            }
        }
        $track->comment_txt = $track->comment_txt . '|' . "Scanned: " . now() . ', ' . $status . '-' . $track->status;
        $track->save();
        if ($status != $track->status) {
            (new PackageService())->updateStatus($track, $track->status);
        }
        if ($admin->store_status == 2 && $track->status == 20 && in_array($track->partner_id, [9]) && !$track->paid) { //If IN Kobia and TAOBAO and not PAID
            Notification::sendTrack($track->id, $track->status);
            $message = " TAOBAO NOT PAID." . $add_message;
            $track->bot_comment = "Scanned In Kobia but TAOBAO Not Paid " . now();
            $track->save();
            return response()->json([
                'redirect' => route('cells.edit', ['id' => $track->id, 'track' => 1]),
//                    'warning' => $message,
            ]);
        }
        if (app('laratrust')->can('update-cells') /*&& !$track->cell*/) {
            if ($track->status == 16 || $track->status == 20) { //In Store or In Baku
                $wcomm = $track->worker_comments;
                $message = NULL;
                $cd = null;
                if ((
                        ($track->courier_delivery && !isOfficeWord($wcomm))
                        || ($wcomm && !empty($wcomm) && !isOfficeWord($wcomm) && !in_array($track->partner_id, [8]))
                    ) && $admin->store_status == 2) {
                    $message = $track->partner_with_label . ' Track ' . $track->worker_comments . $add_message;
                    $cd = $track->courier_delivery;
                    if ($cd && $cd->courier && $admin->store_status == 2) {
                        $message .= ' KURYER: ' . $cd->courier->name . $add_message;
                        //if ($cd->courier->name == 'Azeriexpress')
                        //    $cd->status = 3;
                        //else
                        $cd->status = 2;
                        $cd->save();

                    }
                }
                if (!$cd || !$cd->courier) { // If no courier assigned send notification
                    if (!$track->notification_inbaku_at) {
                        $track->notification_inbaku_at = Carbon::now();
                        $track->save();
                    }
                    if ($notification) {
                        if ($track->partner_id != 5 && $track->partner_id != 6 /*&& $track->city_id != 3 && $track->city_id != 6*/) {
                            if ($track->status == 16) { // In Baku
                                $isPudo = false;
                                //if($track->delivery_type != 'HD' && ($track->store_status || $track->azeriexpress_office_id || $track->azerpost_office_id || $track->surat_office_id))
                                if ($track->store_status || $track->azeriexpress_office_id || $track->azerpost_office_id || $track->surat_office_id)
                                    $isPudo = true;
                                if (!in_array($track->partner_id, [8]) || $isPudo) { //If GFS then must be PUDO
                                    Notification::sendTrack($track->id, $track->status);
                                }
                            } else { //In Kobia
                                if (!in_array($track->partner_id, [9]) || !$track->paid) { //If TAOBAO then must not be PAID
                                    Notification::sendTrack($track->id, $track->status);
                                }
                            }
                        }
                    }
                } //-----
                //Percint filial (store_status) is equal to admin's filial then it arrived filial and must be accepted
                if ($track->store_status && $track->store_status == $admin->store_status) {
                    $precintContainerCheck = PrecinctPackage::where('barcode',$track->tracking_code)->first();
                    //if it is not sended by kobia workers can not scan
                    if ($precintContainerCheck && $precintContainerCheck->status == PrecinctPackage::STATUSES['NOT_SENT']){
                        return response()->json([
                            'error' => 'Baglama gonderilib statusunda deyil',
                        ]);
                    }
                    if ($admin->role_id != 1) {
                        $pService = new PrecinctService();
                        $pService->acceptPackage($track->tracking_code);
                        if ($track->delivery_point) {
                            $track->bot_comment = "Received at " . $track->delivery_point->description;
                            $track->save();
                        }
                    }
                }
                //--------
                if ($message) { //If message assigned just show message
                    return response()->json([
                        'success' => $message,
                    ]);
                } else { // if message is not assigned go to edit cell
                    return response()->json([
                        'redirect' => route('cells.edit', ['id' => $track->id, 'track' => 1]),
                    ]);
                }//-----
            } else {
                return response()->json([
                    'success' => $track->partner_with_label . ' Track status: ' . config('ase.attributes.track.statusShort')[$track->status] . $add_message.' DeliveryAT: '.$track->scanned_at,
                ]);
            }
        } else {
            return response()->json([
                'redirect' => route('tracks.index', ['q' => $track->tracking_code]),
            ]);
            //return response()->json([
            //    'success' => $notification ? 'Notification was sent' : 'You have already scanned this track :-)',
            //]);
        }
    }

    return response()->json([
        'error' => 'Package does not exist!',
    ]);
}
