<?php

namespace App\Http\Controllers\Admin;

use Alert;
use App\Http\Requests;
use App\Models\Admin;
use App\Models\Track;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    protected $view = [
        'sub_title' => 'Administration members',
    ];

    protected $list = [
        'avatar' => [
            'type' => 'image'
        ],
        'role.display_name' => [
            'label' => 'Role'
        ],
        'store_status_label' => [
            'label' => 'Delivery Point',
        ],
        'name',
        'email',
    ];

    protected $fields = [
        [
            'name' => 'avatar',
            'type' => 'image',
            'label' => 'Avatar',
            'validation' => 'nullable|image'
        ],
        [
            'name' => 'name',
            'label' => 'Name',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-4',
            ],
            'validation' => 'required|string|min:3'
        ],
        [
            'label' => 'Role',
            'type' => 'select2',
            'name' => 'role_id',
            'attribute' => 'display_name',
            'model' => 'App\Models\Role',
            'wrapperAttributes' => [
                'class' => 'col-md-4',
            ],
            'validation' => 'required|integer',
            'allowNull' => true,
        ],
        [
            'name' => 'show_menu',
            'label' => 'Show menu',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'col-md-4',
            ],
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group mt-10 col-lg-12">&nbsp;</div>',
        ],
        [
            'label' => 'Delivery Point',
            'type' => 'select2',
            'name' => 'store_status',
            'attribute' => 'name',
            'model' => 'App\Models\DeliveryPoint',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-2',
            ],
            'validation' => 'nullable|integer',
            //'allowNull'         => true,
        ],
        [
            'name' => 'scan_check_only',
            'label' => 'Scan Check Only',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
        ],
        [
            'name' => 'scan_no_alerts',
            'label' => 'Scan Without Alerts',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
        ],
        [
            'name' => 'check_declaration',
            'label' => 'Check only declarations',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
        ],
        [
            'label' => "Cities",
            'type' => 'select2_multiple',
            'name' => 'cities',
            'entity' => 'cities',
            'attribute' => 'name',
            'model' => 'App\Models\City',
            'pivot' => true,
            'validation' => 'nullable|array',
        ],
        [
            'name' => 'email',
            'label' => 'Email',
            'type' => 'email',
            'validation' => 'required|email|unique:admins,email',
            'wrapperAttributes' => [
                'class' => 'col-md-6',
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
                'class' => 'col-md-6',
            ],
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10"><br/></div>',
        ],
        [
            'name' => 'cells',
            'label' => 'Cells',
            'type' => 'textarea',
            'validation' => 'nullable|string|min:5',
            'attributes' => [
                'rows' => 6,
            ],
        ]
    ];

    public function store(Request $request)
    {
        $return = parent::store($request);
        $admin = Admin::latest()->first();
        $admin->roles()->sync([$request->get('role_id')]);

        return $return;
    }

    public function update(Request $request, $id)
    {
        $admin = Admin::find($id);
        $admin->roles()->sync([$request->get('role_id')]);

        return parent::update($request, $id);
    }

    public function sendMessages(Request  $request)
    {
        $tracks = Track::query()
            ->where([
                'partner_id' => 8,
            ])
            ->get();
    }
}
