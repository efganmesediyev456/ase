<?php

namespace App\Http\Controllers\Admin;

use Alert;
use App\Http\Requests;
use App\Models\RuType;
use Request;
use Validator;
use function request;

class NewtypeController extends Controller
{
    protected $modelName = 'RuType';
    protected $route = 'newtypes';
    protected $view = [
        'name' => 'New Types',
        'search' => [
            [
                'name' => 'q',
                'type' => 'text',
                'attributes' => ['placeholder' => 'Search...'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
            ],
        ],
    ];

    protected $list = [
        'hs_code' => [
            'type' => 'text',
            'label' => 'Hs code',
            'order' => 'hs_code',
        ],
        'name_az' => [
            'type' => 'text',
            'label' => 'Name(az)',
            'order' => 'name_az',
        ],
        'name_en' => [
            'type' => 'text',
            'label' => 'Name(en)',
            'order' => 'name_en',
        ],
        'name_ru' => [
            'type' => 'text',
            'label' => 'Name(ru)',
            'order' => 'name_ru',
        ],
        'customstype.name_az_with_parent' => [
            'type' => 'text',
            'label' => 'Customs(az)',
        ],
        'customstype.name_en_with_parent' => [
            'type' => 'text',
            'label' => 'Customs(en)',
        ],
        'customstype.name_ru_with_parent' => [
            'type' => 'text',
            'label' => 'Customs(ru)',
        ],
    ];

    protected $fields = [
        [
            'name' => 'hs_code',
            'type' => 'text',
            'label' => 'Hs code',
            'wrapperAttributes' => [
                'class' => 'col-md-1',
            ],
        ],
        [
            'name' => 'name_az',
            'type' => 'text',
            'label' => 'Name(az)',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
        ],
        [
            'name' => 'name_en',
            'type' => 'text',
            'label' => 'Name(en)',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
        ],
        [
            'name' => 'name_ru',
            'type' => 'text',
            'label' => 'Name(ru)',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
        ],
        [
            'label' => 'Customs',
            'type' => 'select2',
            'name' => 'customs_type_id',
            'attribute' => 'display_name',
            'model' => 'App\Models\CustomsType',
            'wrapperAttributes' => [
                'class' => 'col-md-10',
            ],
            'validation' => 'required|integer',
            'allowNull' => true,
        ],
        /*[
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10"><br/></div>',
    ],*/
    ];

    /*public function store(Request $request)
    {
        $return = parent::store($request);
        return $return;
    }

    public function update(Request $request, $id)
    {
        return parent::update($request, $id);
    }*/
    public function indexObject()
    {
        $validator = Validator::make(Request::all(), [
            'q' => 'string',
        ]);

        $items = RuType::latest();

        if (Request::get('q') != null) {
            $q = Request::get('q');
            $items->orWhere('hs_code', 'LIKE', '%' . $q . '%')
                ->orWhere('name_az', 'LIKE', '%' . $q . '%')
                ->orWhere('name_en', 'LIKE', '%' . $q . '%')
                ->orWhere('name_ru', 'LIKE', '%' . $q . '%');
        }

        $items->getQuery()->orders = null;
        if (request()->get('sort') != null) {
            $sortKey = explode("__", request()->get('sort'))[0];
            $sortType = explode("__", request()->get('sort'))[1];
            $items = $items->orderBy($sortKey, $sortType);
        } else {
            $items = $items->orderBy('id', 'desc');
        }

        $items = $items->paginate($this->limit);

        return $items;

    }
}
