<?php

namespace App\Http\Controllers\Warehouse;

use Alert;
use App\Exports\PackagesExport;
use App\Http\Controllers\Admin\Controller;
use App\Models\Package;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Request;
use Validator;
use View;
use DB;

class ProcessedController extends Controller
{
    protected $modelName = 'Package';

    protected $can = [
        'delete' => false,
        'update' => false,
        'create' => false,
    ];

    protected $view = [

	'name' => 'Sent package',
        'search' => [
            [
                'name' => 'q',
                'type' => 'text',
                'attributes' => ['placeholder' => 'Search...'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-3 col-lg-offset-1',
                ],
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
                    'class' => 'col-lg-2',
                ],
            ],
        ],
    ];

    protected $route = 'w-processed';

    protected $notificationKey = 'custom_id';

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

    protected $list = [
        'custom_id' => [
            'label' => 'CWB No',
        ],
        "tracking_code" => [
            'label' => 'Tracking #',
        ],
        'user' => [
            'type' => 'custom.user',
            'label' => 'User',
        ],
        'weight_with_type' => [
            'label' => 'Weight',
            'type' => 'editable',
            'editable' => [
                'key' => 'weight',
                'route' => 'w-packages.ajax',
                'type' => 'number',
            ],
        ],
        /* 'full_size'        => [
             'label' => 'W/H/L',
         ],*/
        'number_items' => [
            'label' => 'Items',
        ],

        'status_with_label' => [
            'label' => 'Status',
        ],
        'worker' => [
            'label' => 'Worker',
        ],
        'created_at' => [
            'label' => 'At',
            'type' => 'date',
        ],
    ];

    public function __construct()
    {
        parent::__construct();

        View::share('bodyClass', 'sidebar-xs');
    }

    /**
     * @return LengthAwarePaginator
     */
    public function indexObject($status = null)
    {
        $validator = Validator::make(Request::all(), [
            'q' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return ['error' => 'Unexpected variables!'];
        }

        //$countryId = $this->me()->country_id;

        $status = request()->segment(2);

        if ($status == null) {
            $statuses = [2, 3, 4, 5];
        } else {
            $statuses = [1];
        }

        $items = Package::whereWarehouseId($this->id())->whereIn('status', $statuses);

        if (Request::get('q') != null) {
            $q = Request::get('q');

            $items->where(function ($query) use ($q) {
                $query->where("tracking_code", "LIKE", "%" . $q . "%")->orWhere("custom_id", "LIKE", "%" . $q . "%")->orWhere("website_name", "LIKE", "%" . $q . "%")->orWhereHas('user', function (
                    $query
                ) use ($q) {
                    $query->where('customer_id', 'LIKE', '%' . $q . '%')->orWhere('email', 'LIKE', '%' . $q . '%')->orWhere(DB::raw('concat(trim(name)," ",trim(surname))'), 'LIKE', "%" . $q . "%");
		});
            });
        }

        if (Request::get('start_date') != null) {
            $items->where('created_at', '>=', Request::get('start_date') . " 00:00:00");
        }
        if (Request::get('end_date') != null) {
            $items->where('created_at', '<=', Request::get('end_date') . " 23:59:59");
        }

        $items = $items->latest()->paginate($this->limit);

        return $items;
    }

    /**
     * Get id
     *
     * @return mixed
     */
    public function id()
    {
        return $this->me()->getAuthIdentifier();
    }

    public function me()
    {
        return auth()->guard('worker')->user()->warehouse;
    }
}
