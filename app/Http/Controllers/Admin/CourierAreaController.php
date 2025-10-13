<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Models\Courier;
use App\Models\CA;

class CourierAreaController extends Controller
{
    protected $can = [
        'export' => false,
    ];
    protected $view = [
        'name' => 'Courier Areas',
        'formColumns' => 10,
        'sub_title' => 'Courier Areas',
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
                'name' => 'type',
                'type' => 'select_from_array',
                'options' =>  ['CITY'=>'CITY','REGION'=>'REGION','ADDRESS'=>'ADDRESS'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-3',
                ],
                'allowNull' => 'All types',
            ],
            [
                'type' => 'select2',
                'name' => 'courier_id',
                'attribute' => 'name',
                'model' => 'App\Models\Courier',
                'wrapperAttributes' => [
                    'class' => 'col-lg-3',
                ],
                'allowNull' => 'All couriers',
            ],
            [
                'name' => 'partner_id',
                'type' => 'select_from_array',
                'optionsFromConfig' => 'ase.attributes.track.partner',
                'wrapperAttributes' => [
                    'class' => 'col-lg-3',
                ],
                'allowNull' => 'All Partners',
            ],
        ],
    ];

    protected $route = 'courier_areas';
    protected $modelName = 'CA';

    protected $list = [
        'partner_with_label' => [
            'label' => 'Partner',
            'order' => 'partner_id',
        ],
        'courier_with_label' => [
            'label' => 'Courier',
            'order' => 'courier_id',
        ],
        'type' => [
            'label' => 'Type',
            'order' => 'type',
        ],
        'mach' => [
            'label' => 'Mach',
        ],
        'name' => [
            'label' => 'Text',
            'order' => 'name',
        ],
        'created_at' => [
            'label' => 'CreatedAt',
            'order' => 'created_at',
        ],
    ];

    protected $fields = [
        [
            'label' => 'Partner',
            'type' => 'select2',
            'name' => 'partner_id',
            'attribute' => 'name',
            'model' => 'App\Models\Partner',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'nullable|integer',
            'allowNull'         => true,
        ],
        [
            'label' => 'Courier',
            'type' => 'select2',
            'name' => 'courier_id',
            'attribute' => 'name',
            'model' => 'App\Models\Courier',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
            'validation' => 'nullable|integer',
            'allowNull' => true,
        ],
        [
            'name' => 'type',
            'label' => 'Type',
            'type' => 'select_from_array',
            'options' => ['CITY'=>'CITY','REGION'=>'REGION','ADDRESS'=>'ADDRESS'],
            'wrapperAttributes' => [
                'class' => 'col-md-1',
            ],
            'validation' => 'required',
        ],
        [
            'name' => 'mach',
            'label' => 'Mach',
            'type' => 'select_from_array',
            'options' => ['EQUAL'=>'EQUAL','NOT_EQUAL'=>'NOT_EQUAL','IN'=>'IN','NOT IN'=>'NOT_IN'],
            'wrapperAttributes' => [
                'class' => 'col-md-1',
            ],
            'validation' => 'required',
        ],
        [
            'name' => 'name',
            'label' => 'Text',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => ' col-md-3',
            ],
            'validation' => 'nullable|string',
        ],
    ];

    public function __construct()
    {
        $cId = 0;//request()->route('courier_id');
	$arr=request()->query();
	if(count($arr)>=1 && array_keys($arr)[0] && !array_values($arr)[0]) 
	   $cId=array_keys($arr)[0];
	if($cId) {
            $courier = Courier::find($cId);
            if ($courier) {
              $this->routeParams = [
                'courier_id' => $courier->id,
              ];
              $this->view['name'] = 'Courier ares for ' . $courier->name;
	      $this->fields[0]['default']=$courier->id;
            }
	}
        parent::__construct();
    }

    public function indexObject()
    {

	$items=NULL;

	if(\request()->get('sort')) {
            $sortKey = explode("__", \request()->get('sort'))[0];
            $sortType = explode("__", \request()->get('sort'))[1];
            $items = CA::orderBy($sortKey, $sortType);
	} else {
	    $items=CA::orderBy('name','asc');
	    $items=$items->orderBy('type','asc');
	    $items=$items->orderBy('mach','asc');
	}
	if(array_key_exists('courier_id',$this->routeParams)) {
           $items = $items->where('courier_id', $this->routeParams['courier_id']);
	}
        if (\Request::get('partner_id') != null) {
           $items = $items->where('partner_id', \Request::get('partner_id'));
        }
        if (\Request::get('courier_id') != null) {
           $items = $items->where('courier_id', \Request::get('courier_id'));
        }
        if (\Request::get('type') != null) {
           $items = $items->where('type', \Request::get('type'));
        }
        if (\Request::get('q') != null) {
	   $q=\Request::get('q');
	   $items=$items->whereRaw("(name like '%" . $q . "%')");
	}
	if(\request()->get('sort')) {
            $sortKey = explode("__", \request()->get('sort'))[0];
            $sortType = explode("__", \request()->get('sort'))[1];
            $items = $items->orderBy($sortKey, $sortType);
	}
        $items = $items->paginate($this->limit);
        return $items;
    }

}
