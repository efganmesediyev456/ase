<?php

namespace App\Models\Extra;

use App\Models\NotificationQueue;
use App\Models\Order;
use App\Models\Package;
use App\Models\Track;
use App\Models\User;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\StreamInterface;

/**
 * Class Notification
 *
 * @package App\Models\Extra
 * @mixin Eloquent
 * @method static Builder|Notification newModelQuery()
 * @method static Builder|Notification newQuery()
 * @method static Builder|Notification query()
 */
class Notification extends Model
{
    /**
     * @param $number
     * @param $data
     */
    public static function verify($number, $data)
    {
        SMS::verifyNumber($number, $data);
    }

    /**
     * @param $data
     * @param string $templateKey
     * @return bool
     */
    public static function sendToAllUsers($data, $templateKey = 'registration')
    {
        if (env('EMAIL_NOTIFICATION')) {
            Email::sendToAllUsers($data, $templateKey);
        }
        if (env('SMS_NOTIFICATION')) {
            SMS::sendToAllUsers($data, $templateKey);
        }

        return true;
    }

    /**
     * @param $packageId
     * @param $status
     * @return bool
     */
    public static function sendPackage($packageId, $status)
    {
        $package = Package::find($packageId);

        if (!$package || !$package->user) {
            return false;
        }

        if ($status == '2') { // In Baku
            if ($package->user->city_id == 6) { //Sheki city
                return false;
            }
        }

        $azeri_express_name = null;
        $azeri_express_address = null;
        $filial_url = null;
        $filial_work_time = null;
        $user = $package->user;
        if ($user && $user->real_azeri_express_use && $user->azeri_express_office) {
            if ($user->azeri_express_office->description)
                $azeri_express_name = $user->azeri_express_office->description;
            if ($user->azeri_express_office->address)
                $azeri_express_address = $user->azeri_express_office->address;
            $filial_url = locationUrl($user->azeri_express_office->latitude, $user->azeri_express_office->longitude);
            $filial_work_time = $user->azeri_express_office->work_time;
        }
        if ($user && $user->real_surat_use && $user->surat_office) {
            if ($user->surat_office->description)
                $azeri_express_name = $user->surat_office->description;
            if ($user->surat_office->address)
                $azeri_express_address = $user->surat_office->address;
            $filial_url = locationUrl($user->surat_office->latitude, $user->surat_office->longitude);
            $filial_work_time = $user->surat_office->work_time;
        }
        if ($user && $user->real_yenipoct_use && $user->yenipoct_office) {
            if ($user->yenipoct_office->description)
                $azeri_express_name = $user->yenipoct_office->description;
            if ($user->yenipoct_office->address)
                $azeri_express_address = $user->yenipoct_office->address;
            $filial_url = locationUrl($user->yenipoct_office->latitude, $user->yenipoct_office->longitude);
            $filial_work_time = $user->yenipoct_office->work_time;
        }
        if ($user && $user->real_kargomat_use && $user->kargomat_office) {
            if ($user->kargomat_office->description)
                $azeri_express_name = $user->kargomat_office->description;
            if ($user->kargomat_office->address)
                $azeri_express_address = $user->kargomat_office->address;
            $filial_url = locationUrl($user->kargomat_office->latitude, $user->kargomat_office->longitude);
            $filial_work_time = $user->kargomat_office->work_time;
        }
        if ($user && !$user->real_azerpoct_send && !$user->real_yenipoct_use && !$user->real_kargomat_use && !$user->real_azeri_express_use && !$user->real_surat_use && $user->delivery_point && $user->real_store_status != 2) {
            if ($user->delivery_point->description)
                $azeri_express_name = $user->delivery_point->description;
            if ($user->delivery_point->address)
                $azeri_express_address = $user->delivery_point->address;
            $filial_url = locationUrl($user->delivery_point->latitude, $user->delivery_point->longitude);
            $filial_work_time = $user->delivery_point->work_time;
        }
        if ($user && $user->real_azerpoct_send && $user->real_zip_code && $user->azerpost_office) {
            if ($user->azerpost_office->description)
                $azeri_express_name = $user->azerpost_office->description;
            if ($user->azerpost_office->address)
                $azeri_express_address = $user->azerpost_office->address;
            $filial_url = locationUrl($user->azerpost_office->latitude, $user->azerpost_office->longitude);
            $filial_work_time = $user->azerpost_office->work_time;
        }


        $package_azeri_express_name = null;
        $package_azeri_express_address = null;
        $package_filial_contact_name = null;
        $package_filial_contact_phone = null;
        $package_filial_url = null;
        $package_filial_work_time = null;
        $package_filial_lunch_time = null;
        if ($package->azerpost_office) {
            if ($package->azerpost_office->description)
                $package_azeri_express_name = $package->azerpost_office->description;
            if ($package->azerpost_office->address)
                $package_azeri_express_address = $package->azerpost_office->address;
            if (isset($package->azerpost_office->contact_phone) && $package->azerpost_office->contact_phone) {
                $package_filial_contact_phone = $package->azerpost_office->contact_phone;
            }
            if (isset($package->azerpost_office->contact_name) && $package->azerpost_office->contact_name) {
                $package_filial_contact_name = $package->azerpost_office->contact_name;
            }
            $package_filial_url = locationUrl($package->azerpost_office->latitude, $package->azerpost_office->longitude);
            $package_filial_work_time = $package->azerpost_office->work_time;
            $package_filial_lunch_time = $package->azerpost_office->lunch_time;
        } else if ($package->azeri_express_office) {
            if ($package->azeri_express_office->description)
                $package_azeri_express_name = $package->azeri_express_office->description;
            if ($package->azeri_express_office->address)
                $package_azeri_express_address = $package->azeri_express_office->address;
            if (isset($package->azeri_express_office->contact_phone) && $package->azeri_express_office->contact_phone) {
                $package_filial_contact_phone = $package->azeri_express_office->contact_phone;
            }
            if (isset($package->azeri_express_office->contact_name) && $package->azeri_express_office->contact_name) {
                $package_filial_contact_name = $package->azeri_express_office->contact_name;
            }
            $package_filial_url = locationUrl($package->azeri_express_office->latitude, $package->azeri_express_office->longitude);
            $package_filial_work_time = $package->azeri_express_office->work_time;
            $package_filial_lunch_time = $package->azeri_express_office->lunch_time;
        } else if ($package->surat_office) {
            if ($package->surat_office->description)
                $package_azeri_express_name = $package->surat_office->description;
            if ($package->surat_office->address)
                $package_azeri_express_address = $package->surat_office->address;
            if (isset($package->surat_office->contact_phone) && $package->surat_office->contact_phone) {
                $package_filial_contact_phone = $package->surat_office->contact_phone;
            }
            if (isset($package->surat_office->contact_name) && $package->surat_office->contact_name) {
                $package_filial_contact_name = $package->surat_office->contact_name;
            }
            $package_filial_url = locationUrl($package->surat_office->latitude, $package->surat_office->longitude);
            $package_filial_work_time = $package->surat_office->work_time;
            $package_filial_lunch_time = $package->surat_office->lunch_time;
        } else if ($package->yenipoct) {
            if ($package->yenipoct->description)
                $package_azeri_express_name = $package->yenipoct->description;
            if ($package->yenipoct->address)
                $package_azeri_express_address = $package->yenipoct->address;
            if (isset($package->yenipoct->contact_phone) && $package->yenipoct->contact_phone) {
                $package_filial_contact_phone = $package->yenipoct->contact_phone;
            }
            if (isset($package->yenipoct->contact_name) && $package->yenipoct->contact_name) {
                $package_filial_contact_name = $package->yenipoct->contact_name;
            }
            $package_filial_url = locationUrl($package->yenipoct->latitude, $package->yenipoct->longitude);
            $package_filial_work_time = $package->yenipoct->work_time;
            $package_filial_lunch_time = $package->yenipoct->lunch_time;
        } else if ($package->kargomat) {
            if ($package->kargomat->description)
                $package_azeri_express_name = $package->kargomat->description;
            if ($package->kargomat->address)
                $package_azeri_express_address = $package->kargomat->address;
            if (isset($package->kargomat->contact_phone) && $package->kargomat->contact_phone) {
                $package_filial_contact_phone = $package->kargomat->contact_phone;
            }
            if (isset($package->kargomat->contact_name) && $package->kargomat->contact_name) {
                $package_filial_contact_name = $package->kargomat->contact_name;
            }
            $package_filial_url = locationUrl($package->kargomat->latitude, $package->kargomat->longitude);
            $package_filial_work_time = $package->kargomat->work_time;
            $package_filial_lunch_time = $package->kargomat->lunch_time;
        } else if ($package->delivery_point && !($package->store_status == 2 && $status == '8')) {
            if ($package->delivery_point->description)
                $package_azeri_express_name = $package->delivery_point->description;
            if ($package->delivery_point->address)
                $package_azeri_express_address = $package->delivery_point->address;
            if (isset($package->delivery_point->contact_phone) && $package->delivery_point->contact_phone) {
                $package_filial_contact_phone = $package->delivery_point->contact_phone;
            }
            if (isset($package->delivery_point->contact_name) && $package->delivery_point->contact_name) {
                $package_filial_contact_name = $package->delivery_point->contact_name;
            }
            $package_filial_url = locationUrl($package->delivery_point->latitude, $package->delivery_point->longitude);
            $package_filial_work_time = $package->delivery_point->work_time;
            $package_filial_lunch_time = $package->delivery_point->lunch_time;
        }

        $data = [
            'id' => $package->id,
            'cwb' => $package->custom_id,
            'track_code' => $package->tracking_code,
            'user' => $package->user->full_name,
            'code' => $package->user->customer_id,
            'city' => $package->user->city_name,
            'package_city' => $package->city_name,
            'price' => $package->merged_delivery_price,
            'web_site' => getOnlyDomain($package->website_name),
            'azeri_express_name' => $azeri_express_name,
            'azeri_express_address' => $azeri_express_address,
            'filial_name' => $azeri_express_name,
            'incustom_url' => 'www.aseshop.az',
            'incustom_price' => $package->debt_price,
            'broker_url' => str_replace('admin.', '', route('package-pay-broker', $package->custom_id)),
            'broker_fee' => (empty($package->user->voen)) ? 15 : 50,
            'filial_address' => $azeri_express_address,
            'filial_url' => $filial_url,
            'filial_work_time' => $filial_work_time,
            'package_filial_name' => $package_azeri_express_name,
            'package_filial_address' => $package_azeri_express_address,
            'package_filial_contact_name' => $package_filial_contact_name,
            'package_filial_contact_phone' => $package_filial_contact_phone,
            'package_filial_url' => $package_filial_url,
            'package_filial_work_time' => $package_filial_work_time,
            'package_filial_lunch_time' => $package_filial_lunch_time,
            'weight' => $package->weight_with_type,
            'country' => (isset($package->warehouse) && isset($package->warehouse->country)) ? $package->warehouse->country->name : 'xarici',
        ];
        $template = ($status == 'no_declaration' || $status == 'courier_picked_up' || $status == 'customs_storage_fee' || $status == 'Precint_notpaid' || $status == 'package_not_paid' || $status == 'PUDO_DELIVERED_STATUS_PACKAGES' || $status == 'customs_broker_fee') ? $status : ('package_status_' . $status);
        $template1 = null;
        if ($package->warehouse_id)
            $template1 = $template . '_' . $package->warehouse_id;

        return self::sendBoth($package->user_id, $data, $template, $template1);
    }

    /**
     * @param $userID
     * @param $data
     * @param $template
     * @return bool
     */

    public static function sendBoth($userID, $data, $template, $template1 = NULL)
    {
        $userID = self::determineUser($userID);
        @self::sendEmail($userID, $data, $template, $template1);
        @self::sendSMS($userID, $data, $template, $template1);
        @self::sendWhatsapp($userID, $data, $template, $template1);
        @self::sendMobile($userID, $data, $template, $template1);

        return true;
    }

    public static function determineUser($userId)
    {
        $user = User::find($userId);

        return ($user && $user->parent_id) ? $user->parent_id : $userId;
    }

    /**
     * @param $userID
     * @param $data
     * @param $template
     * @return bool|void
     */
    public static function sendEmail($userID, $data, $template, $template1 = NULL)
    {
        $userID = self::determineUser($userID);

        return env('EMAIL_NOTIFICATION') ? Email::sendByUser($userID, $data, $template, $template1) : false;
    }

    /**
     * @param $userID
     * @param $data
     * @param $template
     * @return bool|StreamInterface
     */
    public static function sendSMS($userID, $data, $template, $template1 = NULL)
    {
        $userID = self::determineUser($userID);

        return env('SMS_NOTIFICATION') ? SMS::sendByUser($userID, $data, $template, $template1) : false;
    }

    /**
     * @param $userID
     * @param $data
     * @param $template
     * @return bool|StreamInterface
     */
    public static function sendWhatsapp($userID, $data, $template, $template1 = NULL)
    {
        $userID = self::determineUser($userID);
        return env('SAAS_ACTIVE') ? Whatsapp::sendByUser($userID, $data, $template, $template1) : false;
    }

    /* ================== ORDERS ================== */

    public static function sendMobile($userID, $data, $template, $template1 = NULL)
    {
        $userID = self::determineUser($userID);

        return env('MOBILE_NOTIFICATION') ? Mobile::sendByUser($userID, $data, $template, $template1) : false;
    }

    public static function sendTrack($trackId, $status)
    {
        $track = Track::find($trackId);

        if (!$track) {
            return false;
        }
        if ($track->partner_id == 8 && !$track->container_id && in_array($status, [16, 20]) && !$track->scan_no_check) {
            //GFS no MAWB and inBaku/inKobia
            return false;
        }

        $track_filial_name = null;
        $track_filial_address = null;
        $track_filial_contact_name = null;
        $track_filial_contact_phone = null;
        $track_filial_url = null;
        $track_filial_work_time = null;
        $track_filial_lunch_time = null;
        $filial = $track->filial;
        if ($filial) {
            $track_filial_work_time = $filial->work_time;
            $track_filial_lunch_time = $filial->lunch_time;
        }
        if ($track->azerpost_office) {
            if ($track->azerpost_office->description) {
                $track_filial_name = $track->azerpost_office->description;
            }
            if ($track->azerpost_office->address) {
                $track_filial_address = $track->azerpost_office->address;
            }
            if (isset($track->azerpost_office->contact_phone) && $track->azerpost_office->contact_phone) {
                $track_filial_contact_phone = $track->azerpost_office->contact_phone;
            }
            if (isset($track->azerpost_office->contact_name) && $track->azerpost_office->contact_name) {
                $track_filial_contact_name = $track->azerpost_office->contact_name;
            }
            $track_filial_url = locationUrl($track->azerpost_office->latitude, $track->azerpost_office->longitude);
        } else if ($track->azeriexpress_office) {
            if ($track->azeriexpress_office->description) {
                $track_filial_name = $track->azeriexpress_office->description;
            }
            if ($track->azeriexpress_office->address) {
                $track_filial_address = $track->azeriexpress_office->address;
            }
            if (isset($track->azeriexpress_office->contact_phone) && $track->azeriexpress_office->contact_phone) {
                $track_filial_contact_phone = $track->azeriexpress_office->contact_phone;
            }
            if (isset($track->azeriexpress_office->contact_name) && $track->azeriexpress_office->contact_name) {
                $track_filial_contact_name = $track->azeriexpress_office->contact_name;
            }
            $track_filial_url = locationUrl($track->azeriexpress_office->latitude, $track->azeriexpress_office->longitude);
        } else if ($track->surat_office) {
            if ($track->surat_office->description) {
                $track_filial_name = $track->surat_office->description;
            }
            if ($track->surat_office->address) {
                $track_filial_address = $track->surat_office->address;
            }
            if (isset($track->surat_office->contact_phone) && $track->surat_office->contact_phone) {
                $track_filial_contact_phone = $track->surat_office->contact_phone;
            }
            if (isset($track->surat_office->contact_name) && $track->surat_office->contact_name) {
                $track_filial_contact_name = $track->surat_office->contact_name;
            }
            $track_filial_url = locationUrl($track->surat_office->latitude, $track->surat_office->longitude);
        } else if ($track->yenipoct_office) {
            if ($track->yenipoct_office->description) {
                $track_filial_name = $track->yenipoct_office->description;
            }
            if ($track->yenipoct_office->address) {
                $track_filial_address = $track->yenipoct_office->address;
            }
            if (isset($track->yenipoct_office->contact_phone) && $track->yenipoct_office->contact_phone) {
                $track_filial_contact_phone = $track->yenipoct_office->contact_phone;
            }
            if (isset($track->yenipoct_office->contact_name) && $track->yenipoct_office->contact_name) {
                $track_filial_contact_name = $track->yenipoct_office->contact_name;
            }
            $track_filial_url = locationUrl($track->yenipoct_office->latitude, $track->yenipoct_office->longitude);
        } else if ($track->kargomat_office) {
            if ($track->kargomat_office->description) {
                $track_filial_name = $track->kargomat_office->description;
            }
            if ($track->kargomat_office->address) {
                $track_filial_address = $track->kargomat_office->address;
            }
            if (isset($track->kargomat_office->contact_phone) && $track->kargomat_office->contact_phone) {
                $track_filial_contact_phone = $track->kargomat_office->contact_phone;
            }
            if (isset($track->kargomat_office->contact_name) && $track->kargomat_office->contact_name) {
                $track_filial_contact_name = $track->kargomat_office->contact_name;
            }
            $track_filial_url = locationUrl($track->kargomat_office->latitude, $track->kargomat_office->longitude);
        } else if ($track->delivery_point /*&& $track->store_status != 2*/) {
            if ($track->delivery_point->description) {
                $track_filial_name = $track->delivery_point->description;
            }
            if ($track->delivery_point->address) {
                $track_filial_address = $track->delivery_point->address;
            }
            if (isset($track->delivery_point->contact_phone) && $track->delivery_point->contact_phone) {
                $track_filial_contact_phone = $track->delivery_point->contact_phone;
            }
            if (isset($track->delivery_point->contact_name) && $track->delivery_point->contact_name) {
                $track_filial_contact_name = $track->delivery_point->contact_name;
            }
            $track_filial_url = locationUrl($track->delivery_point->latitude, $track->delivery_point->longitude);
        }

        $data = [
            'id' => $track->id,
            'cwb' => $track->tracking_code,
            'user' => $track->fullname,
            'city' => $track->city_name,
            'price' => $track->delivery_price_with_label,
            'code' => $track->fin ?? $track->customer->fin,
            'weight' => $track->weight,
            'label_pdf' => str_replace('admin.', '', route('track_label', $track->tracking_code)),
            'fin_url' => str_replace('admin.', '', route('track-fin', $track->custom_id)),
            'pay_url' => str_replace('admin.', '', route('track-pay', $track->custom_id)),
            'incustom_url' => str_replace('admin.', '', route('track-pay-debt', $track->custom_id)),
            'broker_url' => str_replace('admin.', '', route('track-pay-broker', $track->custom_id)),
            'broker_fee' => 15,
            'incustom_price' => $track->debt_price,
            'paid' => $track->paid,
            'track_filial_name' => $track_filial_name,
            'track_filial_address' => $track_filial_address,
            'track_filial_contact_name' => $track_filial_contact_name,
            'track_filial_contact_phone' => $track_filial_contact_phone,
            'track_filial_url' => $track_filial_url,
            'track_filial_work_time' => $track_filial_work_time,
            'track_filial_lunch_time' => $track_filial_lunch_time,
        ];

        $template = ($status == 'SCL_Stopped_in_customs_overlimit' || $status == 'package_not_paid' || $status == 'courier_picked_up' || $status == 'Precint_notpaid' || $status == 'customs_storage_fee' || $status == 'OZON_RUS_SMART' || $status == 'tracking_courier_delivery' || $status == 'IHERB_RUS_SMART' || $status == 'TAOBAO_SENT_PAYMENT' || $status == 'TAOBAO_SENT_UNDECLARED' || $status == 'transit_filial_added' || $status == 'PUDO_DELIVERED_STATUS' || $status == 'track_scan_diff_price' || $status == 'customs_broker_fee' ) ? $status : ('track_status_' . $status);

        $template1 = 'track_status_' . $status . '_' . $track->partner_id;

        if ($template1 === 'track_status_20_9' && !$track->paid) {
            return false;
        }

//        if($template == 'customs_broker_fee'){
//            return env('SMS_NOTIFICATION') ? SMS::sendByTrack($track, $data, $template,$template1) : false;
//        }

        return env('SAAS_ACTIVE') ? Whatsapp::sendByTrack($track, $data, $template, $template1) : false;
//        return env('SAAS_ACTIVE') ? Email::sendByCustomer($track->customer_id, $data, $template, $template1) : false;

    }


    public static function sendTrackTestEfgan($trackId, $status)
    {
        $track = Track::find($trackId);

        if (!$track) {
            return false;
        }
        if ($track->partner_id == 8 && !$track->container_id && in_array($status, [16, 20]) && !$track->scan_no_check) {
            //GFS no MAWB and inBaku/inKobia
            return false;
        }


        $track_filial_name = null;
        $track_filial_address = null;
        $track_filial_contact_name = null;
        $track_filial_contact_phone = null;
        $track_filial_url = null;
        $track_filial_work_time = null;
        $track_filial_lunch_time = null;
        $filial = $track->filial;
        if ($filial) {
            $track_filial_work_time = $filial->work_time;
            $track_filial_lunch_time = $filial->lunch_time;
        }
        if ($track->azerpost_office) {
            if ($track->azerpost_office->description) {
                $track_filial_name = $track->azerpost_office->description;
            }
            if ($track->azerpost_office->address) {
                $track_filial_address = $track->azerpost_office->address;
            }
            if (isset($track->azerpost_office->contact_phone) && $track->azerpost_office->contact_phone) {
                $track_filial_contact_phone = $track->azerpost_office->contact_phone;
            }
            if (isset($track->azerpost_office->contact_name) && $track->azerpost_office->contact_name) {
                $track_filial_contact_name = $track->azerpost_office->contact_name;
            }
            $track_filial_url = locationUrl($track->azerpost_office->latitude, $track->azerpost_office->longitude);
        } else if ($track->azeriexpress_office) {
            if ($track->azeriexpress_office->description) {
                $track_filial_name = $track->azeriexpress_office->description;
            }
            if ($track->azeriexpress_office->address) {
                $track_filial_address = $track->azeriexpress_office->address;
            }
            if (isset($track->azeriexpress_office->contact_phone) && $track->azeriexpress_office->contact_phone) {
                $track_filial_contact_phone = $track->azeriexpress_office->contact_phone;
            }
            if (isset($track->azeriexpress_office->contact_name) && $track->azeriexpress_office->contact_name) {
                $track_filial_contact_name = $track->azeriexpress_office->contact_name;
            }
            $track_filial_url = locationUrl($track->azeriexpress_office->latitude, $track->azeriexpress_office->longitude);
        } else if ($track->surat_office) {
            if ($track->surat_office->description) {
                $track_filial_name = $track->surat_office->description;
            }
            if ($track->surat_office->address) {
                $track_filial_address = $track->surat_office->address;
            }
            if (isset($track->surat_office->contact_phone) && $track->surat_office->contact_phone) {
                $track_filial_contact_phone = $track->surat_office->contact_phone;
            }
            if (isset($track->surat_office->contact_name) && $track->surat_office->contact_name) {
                $track_filial_contact_name = $track->surat_office->contact_name;
            }
            $track_filial_url = locationUrl($track->surat_office->latitude, $track->surat_office->longitude);
        } else if ($track->yenipoct_office) {
            if ($track->yenipoct_office->description) {
                $track_filial_name = $track->yenipoct_office->description;
            }
            if ($track->yenipoct_office->address) {
                $track_filial_address = $track->yenipoct_office->address;
            }
            if (isset($track->yenipoct_office->contact_phone) && $track->yenipoct_office->contact_phone) {
                $track_filial_contact_phone = $track->yenipoct_office->contact_phone;
            }
            if (isset($track->yenipoct_office->contact_name) && $track->yenipoct_office->contact_name) {
                $track_filial_contact_name = $track->yenipoct_office->contact_name;
            }
            $track_filial_url = locationUrl($track->yenipoct_office->latitude, $track->yenipoct_office->longitude);
        } else if ($track->kargomat_office) {
            if ($track->kargomat_office->description) {
                $track_filial_name = $track->kargomat_office->description;
            }
            if ($track->kargomat_office->address) {
                $track_filial_address = $track->kargomat_office->address;
            }
            if (isset($track->kargomat_office->contact_phone) && $track->kargomat_office->contact_phone) {
                $track_filial_contact_phone = $track->kargomat_office->contact_phone;
            }
            if (isset($track->kargomat_office->contact_name) && $track->kargomat_office->contact_name) {
                $track_filial_contact_name = $track->kargomat_office->contact_name;
            }
            $track_filial_url = locationUrl($track->kargomat_office->latitude, $track->kargomat_office->longitude);
        } else if ($track->delivery_point /*&& $track->store_status != 2*/) {
            if ($track->delivery_point->description) {
                $track_filial_name = $track->delivery_point->description;
            }
            if ($track->delivery_point->address) {
                $track_filial_address = $track->delivery_point->address;
            }
            if (isset($track->delivery_point->contact_phone) && $track->delivery_point->contact_phone) {
                $track_filial_contact_phone = $track->delivery_point->contact_phone;
            }
            if (isset($track->delivery_point->contact_name) && $track->delivery_point->contact_name) {
                $track_filial_contact_name = $track->delivery_point->contact_name;
            }
            $track_filial_url = locationUrl($track->delivery_point->latitude, $track->delivery_point->longitude);
        }

        $data = [
            'id' => $track->id,
            'cwb' => $track->tracking_code,
            'user' => $track->fullname,
            'city' => $track->city_name,
            'price' => $track->delivery_price_with_label,
            'code' => $track->fin ?? $track->customer->fin,
            'weight' => $track->weight,
            'label_pdf' => str_replace('admin.', '', route('track_label', $track->tracking_code)),
            'fin_url' => str_replace('admin.', '', route('track-fin', $track->custom_id)),
            'pay_url' => str_replace('admin.', '', route('track-pay', $track->custom_id)),
            'incustom_url' => str_replace('admin.', '', route('track-pay-debt', $track->custom_id)),
            'broker_url' => str_replace('admin.', '', route('track-pay-broker', $track->custom_id)),
            'broker_fee' => 15,
            'incustom_price' => $track->debt_price,
            'paid' => $track->paid,
            'track_filial_name' => $track_filial_name,
            'track_filial_address' => $track_filial_address,
            'track_filial_contact_name' => $track_filial_contact_name,
            'track_filial_contact_phone' => $track_filial_contact_phone,
            'track_filial_url' => $track_filial_url,
            'track_filial_work_time' => $track_filial_work_time,
            'track_filial_lunch_time' => $track_filial_lunch_time,
        ];

        $template = ($status == 'SCL_Stopped_in_customs_overlimit' || $status == 'package_not_paid' || $status == 'courier_picked_up' || $status == 'Precint_notpaid' || $status == 'customs_storage_fee' || $status == 'OZON_RUS_SMART' || $status == 'tracking_courier_delivery' || $status == 'IHERB_RUS_SMART' || $status == 'TAOBAO_SENT_PAYMENT' || $status == 'TAOBAO_SENT_UNDECLARED' || $status == 'transit_filial_added' || $status == 'PUDO_DELIVERED_STATUS' || $status == 'track_scan_diff_price' || $status == 'customs_broker_fee' ) ? $status : ('track_status_' . $status);

        $template1 = 'track_status_' . $status . '_' . $track->partner_id;

        if ($template1 === 'track_status_20_9' && !$track->paid) {
            return false;
        }

//        if($template == 'customs_broker_fee'){
//            return env('SMS_NOTIFICATION') ? SMS::sendByTrack($track, $data, $template,$template1) : false;
//        }

        dd($track, $data, $template, $template1,        $phone = $track->phone ?: ($track->customer && $track->customer->phone ? $track->customer->phone : null)
    ,$track->partner_id,$track->customer  ? $track->customer->id : null);


        return env('SAAS_ACTIVE') ? Whatsapp::sendByTrack($track, $data, $template, $template1) : false;
//        return env('SAAS_ACTIVE') ? Email::sendByCustomer($track->customer_id, $data, $template, $template1) : false;

    }

    /**
     * @param $userID
     * @param $data
     * @param $status
     * @return bool
     */
    public static function sendPackageManually($userID, $data, $status)
    {
        return self::sendBoth($userID, $data, 'package_status_' . $status);
    }

    /**
     * @param $orderID
     * @param int $status
     * @return bool
     */
    public static function sendOrder($orderID, $status = 0)
    {
        $order = Order::find($orderID);

        if (!$order || !$order->user) {
            return false;
        }

        $data = [
            'id' => $order->id,
            'order_id' => $order->id,
            'user' => $order->user->full_name,
        ];

        return self::sendBoth($order->user_id, $data, 'order_status_' . $status);
    }

    /**
     * @param $userID
     * @param $data
     * @param $status
     * @return bool
     */
    public static function sendOrderManually($userID, $data, $status)
    {
        return self::sendBoth($userID, $data, 'order_status_' . $status);
    }

    public static function sendBothForQueue(NotificationQueue $queue)
    {
        @self::sendQueueEmail($queue);
        @self::sendQueueSMS($queue);
//        @self::sendQueueWhatsapp($queue);
        @self::sendQueueWhatsappNew($queue);
        @self::sendQueueMobile($queue);

        return true;
    }

    public static function sendQueueEmail(NotificationQueue $queue)
    {
        return env('EMAIL_NOTIFICATION') ? Email::sendByQueue($queue) : false;
    }

    public static function sendQueueSMS(NotificationQueue $queue)
    {
        return env('SMS_NOTIFICATION') ? SMS::sendByQueue($queue) : false;
    }

    public static function sendQueueWhatsapp(NotificationQueue $queue)
    {
        return env('SAAS_ACTIVE') ? Whatsapp::sendByQueue($queue) : false;
    }

    public static function sendQueueWhatsappNew(NotificationQueue $queue)
    {
        return env('WP_ACTIVE') ? Whatsapp::sendByQueueNew($queue) : false;
    }

    public static function sendQueueMobile(NotificationQueue $queue)
    {
        return env('MOBILE_NOTIFICATION') ? Mobile::sendByQueue($queue) : false;
    }

}
