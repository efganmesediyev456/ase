<?php

namespace App\Http\Controllers\Warehouse;

use Alert;
use App\Http\Controllers\Admin\Controller;
use App\Http\Requests;
use App\Models\RuType;
use Request;
use Validator;
use function request;

class NewtypeController extends Controller
{
    protected $modelName = 'RuType';
    protected $route = 'w-newtypes';
    protected $view = [
        'name' => 'Types',
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
        'name_ru' => [
            'type' => 'text',
            'label' => 'Name(ru)',
            'order' => 'name_ru',
        ],
        'name_en' => [
            'type' => 'text',
            'label' => 'Name(en)',
            'order' => 'name_en',
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
            'name' => 'name_ru',
            'type' => 'text',
            'label' => 'Name(ru)',
            'wrapperAttributes' => [
                'class' => 'col-md-5',
            ],
        ],
        [
            'name' => 'name_en',
            'type' => 'text',
            'label' => 'Name(en)',
            'wrapperAttributes' => [
                'class' => 'col-md-5',
            ],
        ],
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
