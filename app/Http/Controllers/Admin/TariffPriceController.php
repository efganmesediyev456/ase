<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Models\TariffPrice;
use App\Models\TariffWeight;

class TariffPriceController extends Controller
{
    protected $view = [
        'name' => 'Tariff Prices',
        'formColumns' => 10,
        'sub_title' => 'Tariff Wiehgts',
    ];

    protected $route = 'tariff_prices';
    protected $modelName = 'TariffPrice';

    protected $list = [
        'tariff_weight.tariff.active' => [
            'label' => 'Active'
        ],
        'tariff_weight.tariff.name' => [
            'label' => 'Name',
        ],
        'tariff_weight.tariff.warehouse.country' => [
            'label' => 'Country',
            'type' => 'country',
        ],
        'tariff_weight.name' => [
            'label' => 'Weight Name',
        ],
        'tariff_weight.from_weight' => [
            'label' => 'From Weight',
        ],
        'tariff_weight.to_weight' => [
            'label' => 'To Weight',
        ],
        'tariff_weight.per_weight' => [
            'label' => 'Per Weight',
        ],
        'price' => [
            'label' => 'Price',
        ],
        'azerpoct1' => [
            'label' => 'Azerpoct',
        ],
        'city_name' => [
            'label' => 'City',
        ],
        'created_at' => [
            'label' => 'CreatedAt',
            'type' => 'date',
            'order' => 'created_at',
        ],
    ];

    protected $fields = [
        [
            'name' => 'azerpoct',
            'label' => 'AzerPoct',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-2',
            ],
            'default' => '0',
        ],
        [
            'name' => 'price',
            'label' => 'Price',
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
            'label' => 'City',
            'type' => 'select2',
            'name' => 'city_id',
            'attribute' => 'name',
            'model' => 'App\Models\City',
            'wrapperAttributes' => [
                'class' => 'col-md-4',
            ],
            'validation' => 'nullable|integer',
            'allowNull' => true,
        ],
    ];

    public function __construct()
    {
        $twId = request()->route('tariff_weight_id');
        $tariff_weight = TariffWeight::find($twId);
        if (!$tariff_weight) {
            return back();
        }
        $this->routeParams = [
            'tariff_weight_id' => $tariff_weight->id,
        ];
        $this->view['name'] = 'Tariff prices for ' . $tariff_weight->tariff->name;
        parent::__construct();
    }

    public function indexObject()
    {

        $items = TariffPrice::where('tariff_weight_id', $this->routeParams['tariff_weight_id'])->paginate($this->limit);
        return $items;
    }

}
