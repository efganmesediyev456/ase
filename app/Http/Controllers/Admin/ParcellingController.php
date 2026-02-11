<?php

namespace App\Http\Controllers\Admin;

use App\Exports\Admin\ContainersExport;
use App\Exports\Admin\ParcellsExport;
use App\Models\Parcel;
use Request;
use View;
use Excel as FacadeExcel;

class ParcellingController extends Controller
{
    protected $can = [
        'export' => true,
        'update' => false,
        'create' => false,
        'delete' => false,
    ];

    protected $extraActions = [
        [
            'key' => 'id',
            'role' => 'customs-reset',
            'label' => 'Customs reset',
            'icon' => 'spinner11',
            'route' => 'packages.parselcarrierupdate',
            'color' => 'info',
            'target' => '_blank',
        ],
        [
            'key' => 'id',
            'role' => 'customs-check',
            'label' => 'Customs check',
            'icon' => 'checkmark',
            'route' => 'packages.parseldepeshcheck',
            'color' => 'info',
            'target' => '_blank',
        ],
        [
            'key' => 'id',
            'role' => 'customs-depesh',
            'label' => 'Customs depesh',
            'icon' => 'cart',
            'route' => 'packages.parseldepesh',
            'color' => 'info',
            'target' => '_blank',
            'confirm' => 'depesh this parcel',
        ],
        [
            'key' => 'custom_id',
            'label' => 'Export [XLS]',
            'icon' => 'file-download',
            'route' => 'packages.export',
            'query' => [
                'format' => 'Xlsx'
            ],
            'color' => 'info',
        ],
        [
            'key' => 'custom_id',
            'label' => 'Export [PDF]',
            'icon' => 'file-download',
            'route' => 'packages.export',
            'query' => [
                'format' => 'Mpdf'
            ],
            'color' => 'info',
        ],
        [
            'key' => 'custom_id',
            'label' => 'Manifest [XLS]',
            'icon' => 'download',
            'route' => 'packages.manifest',
            'query' => [
                'format' => 'Xlsx'
            ],
            'color' => 'success',
        ],
        [
            'key' => 'custom_id',
            'label' => 'Manifest [PDF]',
            'icon' => 'download',
            'route' => 'packages.manifest',
            'query' => [
                'format' => 'Mpdf'
            ],
            'color' => 'success',
        ],
        [
            'key' => 'id',
            'role' => 'customs-depesh',
            'label' => 'Sync to Ukr Express',
            'icon' => 'loop',
            'route' => 'packages.parselukrexpress',
            'color' => 'info',
            'target' => '_blank',
            'confirm' => 'Sync to Ukr Express this parcel',
        ],
    ];

    protected $extraActionsForBag = [
        [
            'route' => 'packages.bagcarrierupdate',
            'key' => 'id',
            'role' => 'customs-reset',
            'label' => 'Customs reset',
            'icon' => 'spinner11',
            'color' => 'success',
            'target' => '_blank',
        ],
        [
            'key' => 'id',
            'role' => 'customs-check',
            'label' => 'Customs check',
            'icon' => 'checkmark',
            'route' => 'packages.bagdepeshcheck',
            'color' => 'info',
            'target' => '_blank',
        ],
        [
            'key' => 'id',
            'role' => 'customs-depesh',
            'label' => 'Customs depesh',
            'icon' => 'cart',
            'route' => 'packages.bagdepesh',
            'color' => 'info',
            'target' => '_blank',
            'confirm' => 'depesh this package',
        ],
    ];

    protected $extraActionsForPackage = [
        [
            'route' => 'packages.carrier_update',
            'key' => 'id',
            'role' => 'customs-reset',
            'label' => 'Customs reset',
            'icon' => 'spinner11',
            'color' => 'success',
            'target' => '_blank',
        ],
        [
            'key' => 'id',
            'role' => 'customs-check',
            'label' => 'Customs check',
            'icon' => 'checkmark',
            'route' => 'packages.packagedepeshcheck',
            'color' => 'info',
            'target' => '_blank',
        ],
        [
            'key' => 'id',
            'role' => 'customs-depesh',
            'label' => 'Customs depesh',
            'icon' => 'cart',
            'route' => 'packages.packagedepesh',
            'color' => 'info',
            'target' => '_blank',
            'confirm' => 'depesh this package',
        ],
    ];

    protected $route = 'parcels';

    protected $notificationKey = 'custom_id';

    protected $modelName = 'Parcel';

    protected $view = [
        'search' => [
            [
                'name' => 'parcel',
                'type' => 'text',
                'attributes' => ['placeholder' => 'MAWB'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
            ],
            [
                'type' => 'select2',
                'name' => 'warehouse_id',
                'attribute' => 'country.name',
                'model' => 'App\Models\Warehouse',
                'wrapperAttributes' => [
                    'class' => 'col-lg-3',
                ],
                'allowNull' => 'All warehouses',
            ],
        ]
    ];
    protected $list = [
        'warehouse.country' => [
            'label' => 'Country',
            'type' => 'country',
        ],
        'custom_id' => [
            'label' => 'CWB No',
            'type' => 'package_link',
        ],
        "tracking_code" => [
            'label' => 'Tracking #',
            'type' => 'package_link',
        ],
        'user' => [
            'type' => 'custom.user_link',
            'label' => 'User',
        ],
        'weight_with_type' => [
            'label' => 'Weight',
            'type' => 'text',
        ],
        /* 'full_size'        => [
             'label' => 'W/H/L',
         ],*/
        'number_items_goods' => [
            'label' => 'Items',
        ],

        'carrier' => [
            'type' => 'carrier',
            'label' => 'Customs',
        ],

        'scanned_at' => [
            'label' => 'Delivered',
        ],

        'status_with_label' => [
            'label' => 'Status',
        ],
        'created_at' => [
            'label' => 'At',
            'type' => 'date',
        ],
    ];

    public function __construct()
    {
        $this->limit = 10;
        parent::__construct();
        View::share('extraActionsForPackage', $this->extraActionsForPackage);
        View::share('extraActionsForBag', $this->extraActionsForBag);
    }

    public function indexObject()
    {
        //$items = Parcel::with(['packages'])->withCount(['packages', 'waiting','packagecarriers','packagecarriersreg','packagecarriersdepesh'])->whereHas('packages')->orderBy('sent', 'asc')->latest();
        //$items = Parcel::with(['bags','packages'])->withCount(['packages', 'waiting','packagecarriers','packagecarriersreg','packagecarriersdepesh'])->whereHas('packages')->orderBy('sent', 'asc')->latest();
        $items = Parcel::with(['bags', 'packages'])->withCount(['packages', 'waiting', 'packagecarriers', 'packagecarriersreg', 'packagecarriersdepesh'])->whereHas('packages')->orderBy('sent', 'asc')->latest();
        if (Request::get('warehouse_id') != null) {
            $items->where('warehouse_id', Request::get('warehouse_id'));
        }
        if (Request::get('parcel') != null) {
            $items->where('custom_id', Request::get('parcel'));
        }

        return $items->paginate($this->limit);
    }

    public function panelView($blade)
    {
        return 'admin.parcel.index';
    }

    public function export($items = null)
    {

        $formats = ['Xlsx' => 'Xlsx', 'Mpdf' => 'pdf'];
        $type = isset($formats[\request()->get('format')]) ? \request()->get('format') : 'Xlsx';
        $ext = $formats[$type];

        if ($ext == 'pdf') {
            $pdf = PDF::loadView('admin.exports.parcells', compact('items'));
            return $pdf->download('packages_' . uniqid() . '.' . $ext);
        }

        return FacadeExcel::download(new ParcellsExport($items), 'parcels_' . uniqid() . '.' . $ext, $type);
    }
}
