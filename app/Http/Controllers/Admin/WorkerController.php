<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Models\Warehouse;
use App\Models\Worker;

class WorkerController extends Controller
{
    protected $notificationKey = 'id';

    protected $view = [
        'name' => null,
    ];

    protected $list = [
        'warehouse.company_name' => [
            'label' => 'Warehouse',
        ],
        'name',
        'email',
    ];

    protected $fields = [
        [
            'name' => 'name',
            'label' => 'Name',
            'type' => 'text',
            'validation' => 'required|string',
            'wrapperAttributes' => [
                'class' => 'col-md-4',
            ],
        ],
        [
            'name' => 'email',
            'label' => 'Email',
            'type' => 'email',
            'validation' => 'required|email|unique:workers,email',
            'wrapperAttributes' => [
                'class' => 'col-md-4',
            ],
        ],
        [
            'name' => 'password',
            'label' => 'Password',
            'type' => 'password',
            'validation' => [
                'store' => 'required|string|min:6',
                'update' => 'nullable|string|min:6',
            ],
            'wrapperAttributes' => [
                'class' => 'col-md-4',
            ],
        ],
    ];

    public function __construct()
    {
        $wId = request()->route('warehouse_id');

        $warehouse = Warehouse::find($wId);

        if (!$warehouse) {
            return back();
        }

        $this->routeParams = [
            'warehouse_id' => $warehouse->id,
        ];

        $this->view['name'] = 'Workers for ' . $warehouse->company_name;
        parent::__construct();
    }

    public function indexObject()
    {
        $items = Worker::where('warehouse_id', $this->routeParams['warehouse_id'])->paginate($this->limit);

        return $items;
    }
}
