<?php

namespace App\Http\Controllers\Admin;

use Alert;
use App\Exports\Admin\ActivityExport;
use App\Http\Requests;
use App\Models\Activity;
use Request;
use Validator;
use Excel;
use Carbon\Carbon;

class ActivityController extends Controller
{
    protected $can = [
        'export' => true,
    ];

    protected $view = [
        'sub_title' => 'Admin activities',
        'search' => [
            [
                'name' => 'q',
                'type' => 'text',
                'attributes' => ['placeholder' => 'Search...'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-3',
                ],
            ],
            [
                'name' => 'content_type',
                'type' => 'select_from_array',
                'options' => [
                    'App\Models\Package' => 'Package',
                    'App\Models\Track' => 'Track',
                    'App\Models\Warehouse' => 'Warehouse',
                    'App\Models\User' => 'User',
                    'App\Models\Transaction' => 'Transaction',

                ],
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'Type',
            ],
            [
                'type' => 'select2',
                'name' => 'admin_id',
                'attribute' => 'name',
                'model' => 'App\Models\Admin',
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'All Admins',
            ],
            [
                'type' => 'select2',
                'name' => 'worker_id',
                'attribute' => 'name',
                'model' => 'App\Models\Worker',
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'All Workers',
            ],

            [
                'name' => 'package_status',
                'type' => 'select_from_array',
                'optionsFromConfig' => 'ase.attributes.package.status',
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'Package Status',
            ],

            [
                'name' => 'track_status',
                'type' => 'select_from_array',
                'optionsFromConfig' => 'ase.attributes.track.statusShort',
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'Track Status',
            ],
            [
                'name' => 'partner_id',
                'type' => 'select_from_array',
                'optionsFromConfig' => 'ase.attributes.track.partner',
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'All Partners',
            ],
            [
                'name' => 'event_date_range',
                'start_name' => 'start_date',
                'end_name' => 'end_date',
                'type' => 'date_range',

                'date_range_options' => [
                    'timePicker' => true,
                    'locale' => ['format' => 'DD/MM/YYYY'],
                ],
                'wrapperAttributes' => [
                    'class' => 'col-lg-5',
                ],
            ],

        ],
    ];

    protected $list = [
        'admin.name' => [
            'label' => 'Admin',
        ],
        'worker.name' => [
            'label' => 'Worker',
        ],
        'partner_name' => [
            'label' => 'Partner name',
        ],
        'content_code' => [
            'label' => 'Package',
        ],

//        'content_id',
        //'content_type',
        'description',
        'data',
        'ip',
        'user_agent',
        'created_at',
    ];

    public function indexObject()
    {

        $validator = Validator::make(Request::all(), [
            'q'         => 'nullable|string',
            'admin_id'  => 'nullable|integer',
            'worker_id' => 'nullable|integer',
            'content_type' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            Alert::error('Unexpected variables!');
            return redirect()->route("my.dashboard");
        }

        $items = Activity::latest();

        if ($workerId = Request::get('worker_id')) {
            $items->where('worker_id', $workerId);
        }

        if ($contentType = Request::get('content_type')) {
            $items->where('content_type', $contentType);
        }

        if ($adminId = Request::get('admin_id')) {
            $items->where('admin_id', $adminId);
        }

        if ($start = Request::get('start_date')) {
            $items->where('created_at', '>=', Carbon::parse($start)->startOfDay());
        }

        if ($end = Request::get('end_date')) {
            $items->where('created_at', '<=', Carbon::parse($end)->endOfDay());
        }

        if ($partnerId = Request::get('partner_id')) {
            $items->where('content_type', 'App\Models\Track')
                ->whereIn('content_id', function ($query) use ($partnerId) {
                    $query->select('id')
                        ->from('tracks')
                        ->where('partner_id', $partnerId);
                });
        }


        if ($q = Request::get('q')) {
            $q = str_replace('"', '', $q);
            $codes = preg_split("/[;:,\s]+/", trim($q));

            $trackIds = \DB::table('tracks')
                ->whereIn('tracking_code', $codes)
                ->pluck('id')
                ->toArray();

            $packageIds = \DB::table('packages')
                ->whereIn('custom_id', $codes)
                ->pluck('id')
                ->toArray();

            $allIds = array_merge($trackIds, $packageIds);

            if (!empty($allIds)) {
                $items->whereIn('content_id', $allIds);
            } else {
                $items->whereNull('id');
            }
        }

        if ($trackStatus = Request::get('track_status')) {
            $items->where('content_type', 'App\Models\Track')
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(details, '$.status')) = ?", [$trackStatus]);
        }

        if ($packageStatus = Request::get('package_status')) {
            $items->where('content_type', 'App\Models\Package')
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(details, '$.status')) = ?", [$packageStatus]);
        }

        if (Request::get('search_type') === 'export' || Request::has('export')) {
            return $items->get();
        }

        return $items->paginate($this->limit);
    }

    public function export($items = null)
    {

        $formats = ['Xlsx' => 'Xlsx', 'Mpdf' => 'pdf'];
        $type = isset($formats[\request()->get('format')]) ? \request()->get('format') : 'Xlsx';
        $ext = $formats[$type];

//        if ($ext == 'pdf') {
//            $pdf = PDF::loadView('admin.exports.pdf_tracks', compact('items'));
//            return $pdf->download('packages_' . uniqid() . '.' . $ext);
//        }

        return Excel::download(new ActivityExport($items), 'activities_' . uniqid() . '.' . $ext, $type);

    }

}
