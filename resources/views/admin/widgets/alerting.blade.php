<?php

use App\Services\Package\PackageService;

$alertText = null;
$alertType = 'success';
$alertSize = 'font-size:18px';
$alertText2 = null;
$alertType2 = 'success';
$alertText3 = null;
$alertType3 = 'danger';
$alertText4 = null;
$alertType4 = 'success';
$alertText5 = null;
$alertType5 = 'danger';
$alertSize2 = 'font-size:18px';
$sendFilial = false;
$sendStoreStatus = null;
$sendFilialText = null;
$serviceStatus = null;
$cities = isset(auth()->guard('admin')->user()->cities) ? auth()->guard('admin')->user()->cities->pluck('id')->all() : [];
$store_status = auth()->guard('admin')->user()->store_status;
if ($user) {
    if ($package->delivery_point) {
        $alertText = "FILIAL: <b>" . $package->delivery_point->description . "</b><br>address: " . $package->delivery_point->address;
        if ($package->scanned_at) {
            $alertText .= "<br> <b>DeliveryAT: " . $package->scanned_at . "</b>";
        }
        $alertType = 'success';
        $alertSize = 'font-size:20px';
        if ($package->store_status != $store_status) {
            $alertType = 'danger';
        } else if ($package->store_status != 2 && $store_status != 2) {
            $sendFilial = true;
            $sendStoreStatus = 2;
            $sendFilialText = 'Kobia';
        }

        if (!$package->paid) {
            $alertText3 .= "Bağlamanın çatdırılması ÖDƏNİLMƏYİB!";
            $alertType3 = 'danger';
        } else {
            $alertText4 .= "Bağlamanın çatdırılması ödənilib";
            $alertType4 = 'success';
        }

        if ($package->debt_price > 0 && !$package->paid_debt) {
            $alertText5 .= "Bağlamanın borc məbləği ÖDƏNİLMƏYİB!";
            $alertType5 = 'danger';
        }
        if ($package->store_status != 2 || $user->real_store_status != 2) {
            if ($package->paid == 1 || in_array($package->store_status, [1, 3, 4, 5, 6, 7, 8])) {
                $serviceStatus = (new  PackageService())->addPackageToContainer('precinct', $user->real_store_status, 'package', $package->custom_id);
            }
        }

    } else if ($package->is_unknown_office()) {
        $alertText = "FILIAL: <b>" . $package->unknown_office->description . "</b>";
        $alertType = 'danger';
        if ($package->scanned_at) {
            $alertText .= "<br> <b>DeliveryAT: " . $package->scanned_at . "</b>";
        }
        if ($store_status != 2) {
            $sendFilial = true;
            $sendStoreStatus = 2;
            $sendFilialText = 'Kobia';
        }
        if (!$package->paid) {
            $alertText3 .= "Bağlamanın çatdırılması ÖDƏNİLMƏYİB!";
            $alertType3 = 'danger';
        } else {
            $alertText4 .= "Bağlamanın çatdırılması ödənilib";
            $alertType4 = 'success';
        }
        if ($package->debt_price > 0 && !$package->paid_debt) {
            $alertText5 .= "Bağlamanın borc məbləği ÖDƏNİLMƏYİB!";
            $alertType5 = 'danger';
        }
    } else if ($user->real_azerpoct_send && $user->real_zip_code && $user->azerpost_office) {
        $alertText = "Azerpost: <b> Zip Code: " . strtoupper($user->azerpost_office->name) . "</b>";
        $alertType = 'danger';
        $alertSize = 'font-size:20px';
        if ($package->scanned_at) {
            $alertText .= "<br> <b>DeliveryAT: " . $package->scanned_at . "</b>";
        }
        if (!$package->paid) {
            $alertText3 .= "Bağlamanın çatdırılması ÖDƏNİLMƏYİB!";
            $alertType3 = 'danger';
        } else {
            $alertText4 .= "Bağlamanın çatdırılması ödənilib";
            $alertType4 = 'success';
        }
        if ($package->debt_price > 0 && !$package->paid_debt) {
            $alertText5 .= "Bağlamanın borc məbləği ÖDƏNİLMƏYİB!";
            $alertType5 = 'danger';
        }
        $package->azerpost_office_id = $user->azerpost_office->id;
        $package->azeri_express_office_id = NULL;
        $package->surat_office_id = NULL;
        $package->save();
        if ($package->paid) {
            $serviceStatus = (new  PackageService())->addPackageToContainer('azerpost', $user->azerpost_office->id, 'package', $package->custom_id);
        }
    } else if ($user->real_azeri_express_use && $user->real_azeri_express_office_id && $user->azeri_express_office) {
        $alertText = "Azeri Express: <b>" . $user->azeri_express_office->description . "</b><br>address: " . $user->azeri_express_office->address;
        if ($package->scanned_at) {
            $alertText .= "<br> <b>DeliveryAT: " . $package->scanned_at . "</b>";
        }
        $alertType = 'danger';
        $alertSize = 'font-size:20px';
        if (!$package->paid) {
            $alertText3 .= "Bağlamanın çatdırılması ÖDƏNİLMƏYİB!";
            $alertType3 = 'danger';
        } else {
            $alertText4 .= "Bağlamanın çatdırılması ödənilib";
            $alertType4 = 'success';
        }
        if ($package->debt_price > 0 && !$package->paid_debt) {
            $alertText5 .= "Bağlamanın borc məbləği ÖDƏNİLMƏYİB!";
            $alertType5 = 'danger';
        }
        $package->azeri_express_office_id = $user->real_azeri_express_office_id;
        $package->surat_office_id = NULL;
        $package->azerpost_office_id = NULL;

        $package->save();
        if ($package->paid) {
            $serviceStatus = (new  PackageService())->addPackageToContainer('azeriexpress', $user->real_azeri_express_office_id, 'package', $package->custom_id);
        }
    } else if ($user->real_surat_use && $user->real_surat_office_id && $user->surat_office) {
        $alertText = "Surat Kargo: <b>" . $user->surat_office->description . "</b><br>address: " . $user->surat_office->address;
        if ($package->scanned_at) {
            $alertText .= "<br> <b>DeliveryAT: " . $package->scanned_at . "</b>";
        }
        $alertType = 'danger';
        $alertSize = 'font-size:20px';
        if (!$package->paid) {
            $alertText3 .= "Bağlamanın çatdırılması ÖDƏNİLMƏYİB!";
            $alertType3 = 'danger';
        } else {
            $alertText4 .= "Bağlamanın çatdırılması ödənilib";
            $alertType4 = 'success';
        }
        if ($package->debt_price > 0 && !$package->paid_debt) {
            $alertText5 .= "Bağlamanın borc məbləği ÖDƏNİLMƏYİB!";
            $alertType5 = 'danger';
        }
        $package->surat_office_id = $user->real_surat_office_id;
        $package->azeri_express_office_id = NULL;
        $package->azerpost_office_id = NULL;
        $package->save();
        if ($package->paid) {
            $serviceStatus = (new  PackageService())->addPackageToContainer('surat', $user->real_surat_office_id, 'package', $package->custom_id);
        }
    } else if ($user->real_yenipoct_use && $user->real_yenipoct_office_id && $user->yenipoct_office) {
        $alertText = "YeniPoct: <b>" . $user->yenipoct_office->description . "</b><br>address: " . $user->yenipoct_office->address;
        if ($package->scanned_at) {
            $alertText .= "<br> <b>DeliveryAT: " . $package->scanned_at . "</b>";
        }
        $alertType = 'danger';
        $alertSize = 'font-size:20px';
        if (!$package->paid) {
            $alertText3 .= "Bağlamanın çatdırılması ÖDƏNİLMƏYİB!";
            $alertType3 = 'danger';
        } else {
            $alertText4 .= "Bağlamanın çatdırılması ödənilib";
            $alertType4 = 'success';
        }
        if ($package->debt_price > 0 && !$package->paid_debt) {
            $alertText5 .= "Bağlamanın borc məbləği ÖDƏNİLMƏYİB!";
            $alertType5 = 'danger';
        }
        $package->yenipoct_office_id = $user->real_yenipoct_office_id;
        $package->azeri_express_office_id = NULL;
        $package->surat_office_id = NULL;
        $package->azerpost_office_id = NULL;
        $package->save();
        if ($package->paid) {
            $serviceStatus = (new  PackageService())->addPackageToContainer('yenipoct', $user->real_yenipoct_office_id, 'package', $package->custom_id);
        }
    } else if ($user->real_kargomat_use && $user->real_kargomat_office_id && $user->kargomat_office) {
        $alertText = "Kargomat: <b>" . $user->kargomat_office->description . "</b><br>address: " . $user->kargomat_office->address;
        if ($package->scanned_at) {
            $alertText .= "<br> <b>DeliveryAT: " . $package->scanned_at . "</b>";
        }
        $alertType = 'danger';
        $alertSize = 'font-size:20px';
        if (!$package->paid) {
            $alertText3 .= "Bağlamanın çatdırılması ÖDƏNİLMƏYİB!";
            $alertType3 = 'danger';
        } else {
            $alertText4 .= "Bağlamanın çatdırılması ödənilib";
            $alertType4 = 'success';
        }
        if ($package->debt_price > 0 && !$package->paid_debt) {
            $alertText5 .= "Bağlamanın borc məbləği ÖDƏNİLMƏYİB!";
            $alertType5 = 'danger';
        }
        $package->kargomat_office_id = $user->real_kargomat_office_id;
        $package->azeri_express_office_id = NULL;
        $package->surat_office_id = NULL;
        $package->azerpost_office_id = NULL;
        $package->save();
        if ($package->paid) {
            $serviceStatus = (new  PackageService())->addPackageToContainer('kargomat', $user->real_kargomat_office_id, 'package', $package->custom_id);
        }
    } else if ($store_status != $user->real_store_status /*user filial differs from worker */) {
        if (
            ($user->real_store_status == 1 || $user->real_store_status == 2 /* user In Baku or In Kobia */)
            && ($store_status == 1 || $store_status == 2 /* worker In Baku or In Kobia */)
            && (isset($user->real_city_id) && $cities && !in_array($user->real_city_id, $cities))
        ) {
            $alertText = "This package belongs to <b>" . $user->real_city_name . "</b> city. Please set this package aside.";
            if ($package->scanned_at) {
                $alertText .= "<br> <b>DeliveryAT: " . $package->scanned_at . "</b>";
            }
            $alertType = 'danger';
            if (!$package->paid) {
                $alertText3 .= "Bağlamanın çatdırılması ÖDƏNİLMƏYİB!";
                $alertType3 = 'danger';
            } else {
                $alertText4 .= "Bağlamanın çatdırılması ödənilib";
                $alertType4 = 'success';
            }
            if ($package->debt_price > 0 && !$package->paid_debt) {
                $alertText5 .= "Bağlamanın borc məbləği ÖDƏNİLMƏYİB!";
                $alertType5 = 'danger';
            }
            if ($user->real_city_id == 3 || $user->real_city_id == 6) {
                $package->city_id = $user->real_city_id;
                $package->save();
            }
        } else {
            if ($user->delivery_point) {
                $alertText = "FILIAL: <b>" . $user->delivery_point->description . "</b><br>address: " . $user->delivery_point->address;
                if ($package->scanned_at) {
                    $alertText .= "<br> <b>DeliveryAT: " . $package->scanned_at . "</b>";
                }
                if ($user->real_store_status == 1 || $store_status == 2 /* worker In Kobia user Sahil */) {
                    $alertType = 'success';
                } else {
                    $alertType = 'danger';
                }
                $alertSize = 'font-size:20px';
                if (!$package->paid) {
                    $alertText3 .= "Bağlamanın çatdırılması ÖDƏNİLMƏYİB!";
                    $alertType3 = 'danger';
                } else {
                    $alertText4 .= "Bağlamanın çatdırılması ödənilib";
                    $alertType4 = 'success';
                }
                if ($package->debt_price > 0 && !$package->paid_debt) {
                    $alertText5 .= "Bağlamanın borc məbləği ÖDƏNİLMƏYİB!";
                    $alertType5 = 'danger';
                }
                if (
                    ($store_status == 1 || $store_status == 2 /* worker In Baku or In Kobia */)
                    && ($user->real_store_status != 2 /* user Not In Baku or In Kobia */)
                ) {
                    if ($package->paid == 1 || in_array($user->real_store_status, [1, 3, 4, 5, 6, 7, 8])) {
                        $serviceStatus = (new  PackageService())->addPackageToContainer('precinct', $user->real_store_status, 'package', $package->custom_id);
                    }
                }
                $package->store_status = $user->real_store_status;
                $package->azeri_express_office_id = NULL;
                $package->azerpost_office_id = NULL;
                $package->surat_office_id = NULL;
                $package->save();
            } else {
                $alertText = "FILIAL: <b>UNKNOWN " . $user->real_store_status . "</b>";
                $alertType = 'danger';
                $alertSize = 'font-size:20px';
                if (!$package->paid) {
                    $alertText3 .= "Bağlamanın çatdırılması ÖDƏNİLMƏYİB!";
                    $alertType3 = 'danger';
                } else {
                    $alertText4 .= "Bağlamanın çatdırılması ödənilib";
                    $alertType4 = 'success';
                }
                if ($package->debt_price > 0 && !$package->paid_debt) {
                    $alertText5 .= "Bağlamanın borc məbləği ÖDƏNİLMƏYİB!";
                    $alertType5 = 'danger';
                }
            }
        }
    } else if (isset($user->real_city_id) && $cities && !in_array($user->real_city_id, $cities)) {
        $alertText = "This package belongs to <b>" . $user->real_city_name . "</b> city. Please set this package aside.";
        $alertType = 'danger';
        if ($user->real_city_id == 3 || $user->real_city_id == 6) {
            $package->city_id = $user->real_city_id;
            $package->save();
        }
        if (!$package->paid) {
            $alertText3 .= "Bağlamanın çatdırılması ÖDƏNİLMƏYİB!";
            $alertType3 = 'danger';
        } else {
            $alertText4 .= "Bağlamanın çatdırılması ödənilib";
            $alertType4 = 'success';
        }
        if ($package->debt_price > 0 && !$package->paid_debt) {
            $alertText5 .= "Bağlamanın borc məbləği ÖDƏNİLMƏYİB!";
            $alertType5 = 'danger';
        }
    } else {
        if ($user->delivery_point) {
            $alertText = "FILIAL: <b>" . $user->delivery_point->description . "</b><br>address: " . $user->delivery_point->address;
            if ($package->scanned_at) {
                $alertText .= "<br> <b>DeliveryAT: " . $package->scanned_at . "</b>";
            }
            $alertType = 'success';
            $alertSize = 'font-size:20px';
            $package->store_status = $user->real_store_status;
            $package->azeri_express_office_id = NULL;
            $package->azerpost_office_id = NULL;
            $package->surat_office_id = NULL;
            $package->save();
            if (!$package->paid) {
                $alertText3 .= "Bağlamanın çatdırılması ÖDƏNİLMƏYİB!";
                $alertType3 = 'danger';
            } else {
                $alertText4 .= "Bağlamanın çatdırılması ödənilib";
                $alertType4 = 'success';
            }
            if ($package->debt_price > 0 && !$package->paid_debt) {
                $alertText5 .= "Bağlamanın borc məbləği ÖDƏNİLMƏYİB!";
                $alertType5 = 'danger';
            }
        } else {
            $alertText = "FILIAL: <b>UNKNOWN " . $user->real_store_status . "</b>";
            $alertType = 'danger';
            $alertSize = 'font-size:20px';
            if (!$package->paid) {
                $alertText3 .= "Bağlamanın çatdırılması ÖDƏNİLMƏYİB!";
                $alertType3 = 'danger';
            } else {
                $alertText4 .= "Bağlamanın çatdırılması ödənilib";
                $alertType4 = 'success';
            }
            if ($package->debt_price > 0 && !$package->paid_debt) {
                $alertText5 .= "Bağlamanın borc məbləği ÖDƏNİLMƏYİB!";
                $alertType5 = 'danger';
            }
        }
    }


    if ($nearBy) {
        if ($dealer) {
            $alertText2 = "Dealer (" . $dealer->full_name . ") ( " . $dealer->city_name . " city ) has " . $nearByCount . " packages in <b>" . $nearBy . "</b>. Put it there.";
            $alertType2 = 'info';
        } else {
            $alertText2 = $user->full_name . " ( " . $user->city_name . " city ) has " . $nearByCount . " packages in <b>" . $nearBy . "</b>. Put it there.";
            $alertType2 = 'info';
        }
    } else {
        if ($dealer) {
            $alertText2 = "Dealer (" . $dealer->full_name . ")  ( " . $dealer->city_name . " city )";
            $alertType2 = 'info';
        } else {
            $alertText2 = $user->full_name . " ( " . $user->city_name . " city )";
            $alertType2 = 'info';
        }
    }
    $cd = $package->courier_delivery;
    if ($cd && $cd->courier) {
        $alertText2 .= " Kuryer: " . $cd->courier->name;
    }
}


if ($track) {
    if ($track->delivery_point) {
        $alertText = "FILIAL: <b>" . $track->delivery_point->description;
        if ($track->scanned_at) {
            $alertText .= "<br> <b> DeliveryAT: " . $track->scanned_at . "</b>";
        }
        if (!$track->paid) {
            $alertText3 .= "Bağlamanın çatdırılması ÖDƏNİLMƏYİB!";
            $alertType3 = 'danger';
        } else {
            $alertText4 .= "Bağlamanın çatdırılması ödənilib";
            $alertType4 = 'success';
        }
        if ($track->debt_price > 0 && !$track->paid_debt) {
            $alertText5 .= "Bağlamanın borc məbləği ÖDƏNİLMƏYİB!";
            $alertType5 = 'danger';
        }
        $alertType = 'success';
        $alertSize = 'font-size:20px';
        if (($track->store_status && $track->store_status != $store_status) || $store_status == 2) {
            $alertType = 'danger';
            if ($track->store_status != 2 && ($track->paid || ($track->partner_id == 9 && $track->status == 20))) {
                if (!($track->partner_id == 9 && !$track->paid)) {
                    $serviceStatus = (new  PackageService())->addPackageToContainer('precinct', $track->store_status, 'track', $track->tracking_code);
                }
            }
        } else if ((!$track->store_status || $track->store_status != 2) && $store_status != 2) {
            $sendFilial = true;
            $sendStoreStatus = 2;
            $sendFilialText = 'Kobia';
        }
    } else if ($track->azerpost_office) {
        $alertText = "Azerpost: <b> Zip Code: " . strtoupper($track->azerpost_office->name) . "</b>";
        if ($track->scanned_at) {
            $alertText .= "<br> <b> DeliveryAT: " . $track->scanned_at . "</b>";
        }
        if (!$track->paid) {
            $alertText3 .= "Bağlamanın çatdırılması ÖDƏNİLMƏYİB!";
            $alertType3 = 'danger';
        } else {
            $alertText4 .= "Bağlamanın çatdırılması ödənilib";
            $alertType4 = 'success';
        }
        if ($track->debt_price > 0 && !$track->paid_debt) {
            $alertText5 .= "Bağlamanın borc məbləği ÖDƏNİLMƏYİB!";
            $alertType5 = 'danger';
        }
        $alertType = 'danger';
        $alertSize = 'font-size:20px';
        if (!($track->partner_id == 9 && !$track->paid)) {
            $serviceStatus = (new  PackageService())->addPackageToContainer('azerpost', $track->azerpost_office->id, 'track', $track->tracking_code);
        }
    } else if ($track->azeriexpress_office) {
        $alertText = "Azeri Express: <b>" . $track->azeriexpress_office->description . "</b><br>address: " . $track->azeriexpress_office->address;
        if ($track->scanned_at) {
            $alertText .= "<br> <b> DeliveryAT: " . $track->scanned_at . "</b>";
        }
        if (!$track->paid) {
            $alertText3 .= "Bağlamanın çatdırılması ÖDƏNİLMƏYİB!";
            $alertType3 = 'danger';
        } else {
            $alertText4 .= "Bağlamanın çatdırılması ödənilib";
            $alertType4 = 'success';
        }
        if ($track->debt_price > 0 && !$track->paid_debt) {
            $alertText5 .= "Bağlamanın borc məbləği ÖDƏNİLMƏYİB!";
            $alertType5 = 'danger';
        }
        $alertType = 'danger';
        $alertSize = 'font-size:20px';
        if (!($track->partner_id == 9 && !$track->paid)) {
            $serviceStatus = (new  PackageService())->addPackageToContainer('azeriexpress', $track->azeriexpress_office_id, 'track', $track->tracking_code);
        }
    } else if ($track->surat_office) {
        $alertText = "Surat Kargo: <b>" . $track->surat_office->description . "</b><br>address: " . $track->surat_office->address;
        if ($track->scanned_at) {
            $alertText .= "<br> <b> DeliveryAT: " . $track->scanned_at . "</b>";
        }
        if (!$track->paid) {
            $alertText3 .= "Bağlamanın çatdırılması ÖDƏNİLMƏYİB!";
            $alertType3 = 'danger';
        } else {
            $alertText4 .= "Bağlamanın çatdırılması ödənilib";
            $alertType4 = 'success';
        }
        if ($track->debt_price > 0 && !$track->paid_debt) {
            $alertText5 .= "Bağlamanın borc məbləği ÖDƏNİLMƏYİB!";
            $alertType5 = 'danger';
        }
        $alertType = 'danger';
        $alertSize = 'font-size:20px';
        if (!($track->partner_id == 9 && !$track->paid)) {
            $serviceStatus = (new  PackageService())->addPackageToContainer('surat', $track->surat_office_id, 'track', $track->tracking_code);
        }
    } else if ($track->kargomat_office) {
        $alertText = "Kargomat : <b>" . $track->kargomat_office->description . "</b><br>address: " . $track->kargomat_office->address;
        if ($track->scanned_at) {
            $alertText .= "<br> <b> DeliveryAT: " . $track->scanned_at . "</b>";
        }
        if (!$track->paid) {
            $alertText3 .= "Bağlamanın çatdırılması ÖDƏNİLMƏYİB!";
            $alertType3 = 'danger';
        } else {
            $alertText4 .= "Bağlamanın çatdırılması ödənilib";
            $alertType4 = 'success';
        }
        if ($track->debt_price > 0 && !$track->paid_debt) {
            $alertText5 .= "Bağlamanın borc məbləği ÖDƏNİLMƏYİB!";
            $alertType5 = 'danger';
        }
        $alertType = 'danger';
        $alertSize = 'font-size:20px';
        if (!($track->partner_id == 9 && !$track->paid)) {
            $serviceStatus = (new  PackageService())->addPackageToContainer('kargomat', $track->kargomat_office_id, 'track', $track->tracking_code);
        }
    } else if ($track->yenipoct_office) {
        $alertText = "Yenipoct : <b>" . $track->yenipoct_office->description . "</b><br>address: " . $track->yenipoct_office->address;
        if ($track->scanned_at) {
            $alertText .= "<br> <b> DeliveryAT: " . $track->scanned_at . "</b>";
        }
        if (!$track->paid) {
            $alertText3 .= "Bağlamanın çatdırılması ÖDƏNİLMƏYİB!";
            $alertType3 = 'danger';
        } else {
            $alertText4 .= "Bağlamanın çatdırılması ödənilib";
            $alertType4 = 'success';
        }
        if ($track->debt_price > 0 && !$track->paid_debt) {
            $alertText5 .= "Bağlamanın borc məbləği ÖDƏNİLMƏYİB!";
            $alertType5 = 'danger';
        }
        $alertType = 'danger';
        $alertSize = 'font-size:20px';
        if (!($track->partner_id == 9 && !$track->paid)) {
            $serviceStatus = (new  PackageService())->addPackageToContainer('yenipoct', $track->yenipoct_office_id, 'track', $track->tracking_code);
        }
    } else if ($track->unknown_office) {
        $alertText = "Unknown: <b>" . $track->unknown_office->description . "</b>";
        if ($track->scanned_at) {
            $alertText .= "<br> <b> DeliveryAT: " . $track->scanned_at . "</b>";
        }
        if (!$track->paid) {
            $alertText3 .= "Bağlamanın çatdırılması ÖDƏNİLMƏYİB!";
            $alertType3 = 'danger';
        } else {
            $alertText4 .= "Bağlamanın çatdırılması ödənilib";
            $alertType4 = 'success';
        }
        if ($track->debt_price > 0 && !$track->paid_debt) {
            $alertText5 .= "Bağlamanın borc məbləği ÖDƏNİLMƏYİB!";
            $alertType5 = 'danger';
        }
        $alertType = 'danger';
        $alertSize = 'font-size:20px';
    }
    else if ($track->partner_id == 9 && $track->status == 20) {
        $alertText = '';
        if (!$track->paid) {
            $alertText3 .= "Bağlamanın çatdırılması ÖDƏNİLMƏYİB!";
            $alertType3 = 'danger';
        } else {
            $alertText4 .= "Bağlamanın çatdırılması ödənilib";
            $alertType4 = 'success';
        }
        if ($track->debt_price > 0 && !$track->paid_debt) {
            $alertText5 .= "Bağlamanın borc məbləği ÖDƏNİLMƏYİB!";
            $alertType5 = 'danger';
        }
        $alertType = 'danger';
        $alertSize = 'font-size:20px';
    }
    else {
        if ($track->partner_id != 1 && !$track->courier_delivery) {
            $alertText = "NO FILIAL</b>";
            if ($track->scanned_at) {
                $alertText .= "<br> <b> DeliveryAT: " . $track->scanned_at . "</b>";
            }
            if (!$track->paid) {
                $alertText3 .= "Bağlamanın çatdırılması ÖDƏNİLMƏYİB!";
                $alertType3 = 'danger';
            } else {
                $alertText4 .= "Bağlamanın çatdırılması ödənilib";
                $alertType4 = 'success';
            }
            if ($track->debt_price > 0 && !$track->paid_debt) {
                $alertText5 .= "Bağlamanın borc məbləği ÖDƏNİLMƏYİB!";
                $alertType5 = 'danger';
            }
            $alertType = 'danger';
            $alertSize = 'font-size:20px';
        }
        if ($store_status != 2) {
            $sendFilial = true;
            $sendStoreStatus = 2;
            $sendFilialText = 'Kobia';
        }
    }
    $alertText2 = $track->fullname . " ( " . $track->phone . " ) ";
    $alertType2 = 'info';
    $wcomm = $track->worker_comments;
    $cd = null;
    if (
        ($track->courier_delivery && !isOfficeWord($wcomm))
        || ($wcomm && !empty($wcomm) && !isOfficeWord($wcomm))
    ) {
        $alertText2 .= "<b>" . $wcomm . "</b>";
        $cd = $track->courier_delivery;
        if ($cd && $cd->courier) {
            $alertText2 .= ' KURYER: <b>' . $cd->courier->name . "</b>";
        }
        $alertType2 = 'danger';
    } else {
        if ($nearBy && $nearByCount)
            $alertText2 .= " has " . $nearByCount . " tracks in <b>" . $nearBy . "</b>. Put it there.";
    }
}

?>

    @if($sendFilial)
    @if($track)
        <a href="{{ route('cells.edit', $track->id) }}?action=send_filial&store_status={{$sendStoreStatus}}&track=1"
           onclick="return confirm('Send track to {{ $sendFilialText }}?')" class="btn btn-warning legitRipple"
           style="margin-top: 15px;">Send to {{ $sendFilialText }}</a>
    @else
        <a href="{{ route('cells.edit', $package->id) }}?action=send_filial&store_status={{$sendStoreStatus}}"
           onclick="return confirm('Send package to {{ $sendFilialText }}?')" class="btn btn-warning legitRipple"
           style="margin-top: 15px;">Send to {{ $sendFilialText }}</a>
    @endif
@endif
@if($alertText)
    <div class="alert alert-{{ $alertType }}" style="margin-top: 20px; {{ $alertSize }}" id="alert1">
        {!! $alertText !!}
    </div>
    <script>
        var audio = new Audio('/sounds/scan_{{$alertType}}.mp3');
        audio.play();
    </script>
@endif
@if($track)
    <div class="alert alert-danger" style="margin-top: 20px;">
        DeliveryAT: {{ $track->scanned_at }}
    </div>
@endif
@if($alertText2)
    <div class="alert alert-{{ $alertType2 }}" style="margin-top: 20px; {{ $alertSize2 }}" id="alert2">
        {!! $alertText2 !!}
    </div>
@endif

@if($alertText3)
    <div class="alert alert-{{ $alertType3 }}" style="margin-top: 20px; {{ $alertSize2 }}" id="alert3">
        {!! $alertText3 !!}
    </div>
@endif

@if($alertText4)
    <div class="alert alert-{{ $alertType4 }}" style="margin-top: 20px; {{ $alertSize2 }}" id="alert4">
        {!! $alertText4 !!}
    </div>
@endif

@if($alertText5)
    <div class="alert alert-{{ $alertType5 }}" style="margin-top: 20px; {{ $alertSize2 }}" id="alert4">
        {!! $alertText5 !!}
    </div>
@endif

<script>
    document.addEventListener("DOMContentLoaded", function () {
        let alertIds = ['alert1', 'alert2', 'alert3', 'alert4'];

        for (let i = 0; i < alertIds.length; i++) {
            let alertElement = document.getElementById(alertIds[i]);
            if (alertElement) {
                alertElement.scrollIntoView({behavior: 'smooth', block: 'center'});
                break;
            }
        }
    });
</script>


@if($serviceStatus != null &&  $serviceStatus['status'] ?? false == false)
    <div class="alert alert-danger" style="margin-top: 20px; {{ $alertSize2 }}">
        {!! $serviceStatus['message'] !!}
    </div>
@endif
