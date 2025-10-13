<?php

namespace App\Http\Controllers\Warehouse;

use Alert;
use App\Http\Controllers\Admin\Controller;
use App\Models\Warehouse;
use Validator;
use View;

class UserController extends Controller
{
    protected $modelName = 'Warehouse';
    protected $view = [
        'formColumns' => 12
    ];

    protected $route = 'my';

    protected $notificationKey = 'name';

    protected $fields = [
        /* [
             'name'              => 'parcelling',
             'label'             => 'Parcelling',
             'type'              => 'checkbox',
             'wrapperAttributes' => [
                 'class' => 'form-group col-lg-2',
             ],
         ],*/
        [
            'name' => 'show_label',
            'label' => 'Show Label on scan',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-2',
            ],
        ],
        [
            'name' => 'show_invoice',
            'label' => 'Show Invoice on scan',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-2',
            ],
        ],
        [
            'type' => 'html',
            'html' => '<div class="col-lg-12"></div>',
        ],
        [
            'name' => 'auto_print',
            'label' => 'Auto Print (Parcel Processing)',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-2',
            ],
        ],
        [
            'name' => 'auto_print_invoice',
            'label' => 'Auto Print Invoice (Parcel Processing)',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-2',
            ],
        ],
        [
            'name' => 'auto_print_pp',
            'label' => 'Auto Print (Package Processing)',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-2',
            ],
        ],
        [
            'name' => 'draft_label',
            'label' => 'Draft Label (Package Processing)',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-2',
            ],
        ],
        [
            'name' => 'auto_print_pp_invoice',
            'label' => 'Auto Print Invoice (Package Processing)',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-2',
            ],
        ],
        [
            'type' => 'html',
            'html' => '<div class="col-lg-12"></div>',
        ],
        [
            'label' => 'Label Printer',
            'type' => 'select_from_array',
            'options' => [],
            'wrapperAttributes' => [
                'class' => 'col-lg-3',
                'id' => 'label_printer',
            ],
            'allowNull' => 'Loading',
        ],
        [
            'label' => 'Invoice Printer',
            'type' => 'select_from_array',
            'options' => [],
            'wrapperAttributes' => [
                'class' => 'col-lg-3',
                'id' => 'invoice_printer',
            ],
            'allowNull' => 'Loading',
        ],
    ];

    public function __construct()
    {
        parent::__construct();

        View::share('bodyClass', 'sidebar-xs');
    }

    public function editObject($id = null)
    {
        return Warehouse::find(auth()->guard('worker')->user()->warehouse_id);
    }
}
