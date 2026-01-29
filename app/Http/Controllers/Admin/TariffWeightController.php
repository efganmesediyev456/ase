<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Models\Tariff;
use App\Models\TariffWeight;

class TariffWeightController extends Controller
{
    protected $view = [
        'name' => 'Tariff Weights',
        'formColumns' => 10,
        'sub_title' => 'Tariff Wiehgts',
    ];

    protected $route = 'tariff_weights';
    protected $modelName = 'TariffWeight';

    protected $list = [
        'tariff.active' => [
            'label' => 'Active'
        ],
        'tariff.name' => [
            'label' => 'Name',
        ],
        'tariff.warehouse.country' => [
            'label' => 'Country',
            'type' => 'country',
        ],
        'name' => [
            'label' => 'Weight Name',
        ],
        'from_weight' => [
            'label' => 'From Weight',
        ],
        'to_weight' => [
            'label' => 'To Weight',
        ],
        'per_weight' => [
            'label' => 'Per Weight',
        ],
        'created_at' => [
            'label' => 'CreatedAt',
            'type' => 'date',
            'order' => 'created_at',
        ],
    ];

    protected $extraActions = [
        [
            'key' => 'id',
            'label' => 'Prices',
            'icon' => 'map',
            'route' => 'tariff_prices.index',
            'color' => 'success',
        ],
    ];

    protected $fields = [
        [
            'name' => 'from_weight',
            'label' => 'From Weight',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => ' col-md-2',
            ],
        ],
        [
            'name' => 'to_weight',
            'label' => 'To Weight',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => ' col-md-2',
            ],
        ],
        [
            'name' => 'per_weight',
            'label' => 'per Weight',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => ' col-md-2',
            ],
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10"></div>',
        ],
        [
            'name' => 'name',
            'label' => 'Weight Name',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => ' col-md-4',
            ],
            'validation' => 'nullable|string|max:50',
        ],
        [
            'name' => 'is_one_kg',
            'label' => 'Get 1 Kg price from this',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => ' col-md-4',
            ]
        ],
        [
            'name' => 'is_ten_kg',
            'label' => 'Get 10 and over Kg price from this',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => ' col-md-4',
            ]
        ],
    ];

    public function __construct()
    {
        $tId = request()->route('tariff_id');
        $tariff = Tariff::find($tId);
        if (!$tariff) {
            return back();
        }
        $this->routeParams = [
            'tariff_id' => $tariff->id,
        ];
        $this->view['name'] = 'Tariff wieghts for ' . $tariff->name;
        parent::__construct();
    }

    public function indexObject()
    {

        $items = TariffWeight::where('tariff_id', $this->routeParams['tariff_id'])->paginate($this->limit);
        return $items;
    }

}
