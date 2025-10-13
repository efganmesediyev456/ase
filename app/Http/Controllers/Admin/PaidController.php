<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Models\Extra\Notification;
use App\Models\Package;
use App\Models\PackageLog;
use App\Models\PackageTrack;
use App\Models\Track;
use Auth;
use DB;
use View;
use function request;

class PaidController extends Controller
{
    protected $listMain = [
        'scanned_at' => [
            'label' => 'DeliveredAt',
            'order' => 'scanned_at',
        ],
        'parcel_name' => [
            'label' => 'MAWB No',
        ],
        'custom_id' => [
            'label' => 'CWB No',
        ],
        "tracking_code" => [
            'label' => 'Tracking #',
        ],
        'self' => [
            'type' => 'custom.pt_user',
            'label' => 'User',
        ],
        'phone' => [
            'label' => 'User phone',
        ],

        'status_with_label' => [
            'label' => 'Status',
        ],

        'paid' => [
            'label' => 'Paid',
            'type' => 'paid',
            'editable' => [
                'route' => 'packages.ajax',
                'type' => 'select',
                'sourceFromConfig' => 'ase.attributes.package.paidWithLabel',
            ],
        ],

        'shipping_org_price' => [
            'label' => 'Invoice',
            'type' => 'text',
        ],
        'warehouse.country' => [
            'label' => 'Country',
            'type' => 'country',
        ],
    ];

    protected $list = [
        'scanned_at' => [
            'label' => 'DeliveredAt',
            'order' => 'scanned_at',
        ],
        'parcel_name' => [
            'label' => 'MAWB No',
        ],
        'custom_id' => [
            'label' => 'CWB No',
        ],
        "tracking_code" => [
            'label' => 'Tracking #',
        ],
        'self' => [
            'type' => 'custom.pt_user',
            'label' => 'User',
        ],

        'status_with_label' => [
            'label' => 'Status',
        ],

        'paid' => [
            'label' => 'Paid',
            'type' => 'paid',
            'editable' => [
                'route' => 'packages.ajax',
                'type' => 'select',
                'sourceFromConfig' => 'ase.attributes.package.paidWithLabel',
            ],
        ],

        'shipping_org_price' => [
            'label' => 'Invoice',
            'type' => 'text',
        ],
        'warehouse.country' => [
            'label' => 'Country',
            'type' => 'country',
        ],
    ];

    protected $extraActions = [
        [
            'key' => 'invoice',
            'label' => 'Invoice',
            'icon' => 'file-pdf',
            'color' => 'info',
            'target' => '_blank',
        ],
        [
            'route' => 'w-packages.label',
            'key' => 'show_label',
            'label' => 'Label',
            'icon' => 'windows2',
            'color' => 'success',
            'target' => '_blank',
        ],
    ];

    protected $donePackageAction = [
        'route' => 'paids.index',
        'key' => 'id',
        'label' => 'Make package status DONE',
        'icon' => 'check',
        'color' => 'success',
    ];

    protected $userPackageAction = [
        'route' => 'paids.index',
        'key' => 'id',
        'label' => 'Load',
        'icon' => 'arrow-up32',
        'color' => 'primary',
    ];

    protected $modelName = 'PackageTrack';

    public function __construct()
    {
        $track = 0;
        if (request()->has('track')) {
            $track = request()->get('track');
        } elseif (request()->has('cwb')) {
            $custom_id = request()->get('cwb');
            $package = PackageTrack::where('tracking_code', $custom_id)->orWhere('custom_id', $custom_id)->first();
            if ($package)
                $track = $package->track;
        }
        if ($track) {
            $this->listMain['paid']['editable']['route'] = 'tracks.ajax';
            $this->listMain['paid']['editable']['sourceFromConfig'] = 'ase.attributes.track.paidWithLabel';
            $this->list['paid']['editable']['route'] = 'tracks.ajax';
            $this->list['paid']['editable']['sourceFromConfig'] = 'ase.attributes.track.paidWithLabel';
        }
        View::share('listMain', $this->listMain);
        View::share('userPackageAction', $this->userPackageAction);
        View::share('donePackageAction', $this->donePackageAction);
        parent::__construct();
    }

    public function getUserPackages($package, $track = 0)
    {
        $packages = [];
        if (isset($package->id) && !empty($package->id))// && isset($package->user) && !empty($package->user->id))
        {
            if ($track) {
                $packages = PackageTrack::where('customer_id', $package->customer_id)->where('status', 16)->where('id', '<>', $package->id)->where('track', $track)->get();
            } else {
                $packages = PackageTrack::where('user_id', $package->user_id)->where('status', 2)->where('id', '<>', $package->id)->where('track', $track)->get();
            }
        }
        return $packages;
    }

    public function getView($package, $alertText, $alertType, $track = 0)
    {
        $packages = $this->getUserPackages($package, $track);
        return view('admin.paid', compact('package', 'packages', 'alertText', 'alertType'));
    }

    public function logPackageDone($id)
    {

        $data = [];

        $data['status'] = [
            'before' => "2",
            'after' => "3",
        ];

        Notification::sendPackage($id, "3");

        if (!empty($data)) {
            $log = new PackageLog();
            $log->data = json_encode($data);
            $log->admin_id = Auth::guard('admin')->user()->id;
            $log->package_id = $id;
            $log->save();
        }
    }

    public function index()
    {
        $alertText = '';
        $alertType = 'danger';
        $custom_id = request()->get('cwb');
        $id = request()->get('id');
        $track = 0;
        if (request()->has('track'))
            $track = request()->get('track');
        $done = request()->get('done');
        $package = new PackageTrack();

        if (!Auth::user()->can('read-paids')) {
            $alertText = 'No permissions to read paid packages';
            $alertType = 'danger';
            return $this->getView($package, $alertText, $alertType, $track);
        }
        if (!empty($custom_id)) {
            //$package = Package::where('custom_id', $custom_id)->first();
            $package = PackageTrack::where('tracking_code', $custom_id)->orWhere('custom_id', $custom_id)->first();
            if (!isset($package->id) || empty($package->id)) {
                $package = new PackageTrack();
                $alertText = 'No packages found';
                $alertType = 'warning';
            } else {
                $track = $package->track;
            }
            return $this->getView($package, $alertText, $alertType, $track);
        }

        if (!empty($id)) {
            if (!Auth::user()->can('update-paids')) {
                $alertText = 'No permissions to process paid packages';
                $alertType = 'danger';
                return $this->getView($package, $alertText, $alertType, $track);
            }

            $package = PackageTrack::where('id', $id)->where('track', $track)->first();


            if ($done != 1) {
                return $this->getView($package, $alertText, $alertType, $package->track);
            }


            if (isset($package->id) && !empty($package->id) && $package->track) {
		$ignore = DB::table('tracks_ignore_list')->where('tracking_code', $package->custom_id)->first();
		if($ignore) {
                    $alertText = 'Track is in ignore list!';
                    $alertType = 'warning';
		}  else if ($package->status == 17) {
                    $alertText = 'Track status is already DONE!';
                    $alertType = 'warning';
                } else {
                    if (($package->status != 16) && ($package->status != 20)) {
                        $alertText = 'Track status is not In Baku';
                        $alertType = 'danger';
                    } else
                        if ($package->paid != 1) {
                            $alertText = 'Track is not PAID!';
                            $alertType = 'danger';
                        } else {
                            $package->status = 17;
                            $track = Track::where('id', $id)->first();
                            $track->status = 17;
                            $track->save();
			    if($track->partner_id != 5 && $track->partner_id != 6)
                                Notification::sendTrack($id, 17);
                            //$this->logPackageDone($package->id);
                            $alertText = "Track status successfully changed to DONE!";
                            $alertType = 'success';
                        }
		}
            } else if (isset($package->id) && !empty($package->id)) {
                if ($package->status == 3) {
                    $alertText = 'Package status is already DONE!';
                    $alertType = 'warning';
                } else
                    if (($package->status != 2) && ($package->status != 8)) {
                        $alertText = 'Package status is not In Baku';
                        $alertType = 'danger';
                    } else
                        if ($package->paid != 1) {
                            $alertText = 'Package is not PAID!';
                            $alertType = 'danger';
                        } else {
                            $pkg = Package::where('id', $id)->first();
                            $pkg->status = "3";
                            $package->status = "3";
                            $pkg->save();
                            $this->logPackageDone($pkg->id);
                            $alertText = "Package status successfully changed to DONE!";
                            $alertType = 'success';
                        }
            } else {
                $package = new Package();
                $alertText = 'No packages found';
                $alertType = 'warning';
            }
        }
        return $this->getView($package, $alertText, $alertType, $package->track);
    }
}
