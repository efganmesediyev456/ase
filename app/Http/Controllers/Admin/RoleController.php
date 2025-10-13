<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use View;

class RoleController extends Controller
{
    protected $view = [
        'listColumns' => 8,
        'sub_title' => 'Roles for permissions',
    ];

    protected $list = [
        'name',
        'display_name',
        'description',
    ];

    protected $fields = [
        [
            'name' => 'name',
            'label' => 'Name',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-6',
            ],
            'validation' => 'required|string|min:3',
        ],
        [
            'name' => 'display_name',
            'label' => 'Display Name',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-6',
            ],
            'validation' => 'required|string|min:3',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group mt-10 col-lg-12"></div>',
        ],
        [
            'name' => 'description',
            'label' => 'Description',
            'type' => 'textarea',
            'validation' => 'required|string|min:3',
            'wrapperAttributes' => [
                'class' => 'col-md-12',
            ],
        ],
    ];

    public function update(Request $request, $id)
    {
        $item = Role::find($id);
        $permissions = [];

        foreach ($request->get('permissions') as $permissionId => $value) {
            if ($value) {
                $permissions[] = $permissionId;
            }
        }
        //dd($permissions);
        $item->permissions()->sync($permissions);

        return parent::update($request, $id);
    }

    public function edit($id)
    {
        $item = Role::with('permissions')->find($id);
        $rolePermissions = $item->permissions->pluck('id')->all();


        $permissions = Permission::all();

        $this->fields[] = [
            'type' => 'html',
            'html' => '<div class="form-group mt-10 col-lg-12"><h3 class="text-center">Permissions</h3></div>',
        ];

        foreach ($permissions as $key => $permission) {
            if ($key % 4 == 0 && $key) {
                $this->fields[] = [
                    'type' => 'html',
                    'html' => '<div class="form-group mt-10 col-lg-12"><hr/></div>',
                ];
            }
            $this->fields[] = [
                'name' => 'permissions[' . $permission->id . ']',
                'label' => $permission->display_name,
                'default' => (in_array($permission->id, $rolePermissions)),
                'type' => 'checkbox',
                'wrapperAttributes' => [
                    'class' => 'col-md-3',
                ],
            ];
        }

        View::share([
            'permissions' => $permissions,
            'fields' => $this->fields
        ]);

        return parent::edit($id);
    }
}
