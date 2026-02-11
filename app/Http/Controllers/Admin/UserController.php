<?php

namespace App\Http\Controllers\Admin;

use Alert;
use App\Exports\Admin\UsersExport;
use App\Http\Requests;
use App\Models\Activity;
use App\Models\User;
use DB;
use Excel;
use Request;
use Validator;
use View;
use function request;
//use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $notificationKey = 'customer_id';

    protected $can = [
        'export' => true,
    ];

    protected $with = ['city', 'dealer'];

    protected $view = [
        'formColumns' => 10,
        'bodyClass' => 'sidebar-xs',
        'row' => true,
        'total_sum' => [
            [
                'key' => 'packages_cnt',
                'skip' => 4,
            ],
        ],
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
                'name' => 'dealer',
                'type' => 'text',
                'attributes' => ['placeholder' => 'Dealer...'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
            ],
            [
                'type' => 'select2',
                'name' => 'city_id',
                'attribute' => 'name',
                'model' => 'App\Models\City',
                'wrapperAttributes' => [
                    'class' => 'col-lg-3',
                ],
                'allowNull' => 'All Cities',
            ],
            [
                'name' => 'fin',
                'type' => 'select_from_array',
                'optionsFromConfig' => 'ase.attributes.yes_no',
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'Has Fin',
            ],
            [
                'name' => 'status',
                'type' => 'select_from_array',
                'options' => ['ACTIVE' => 'Active', 'PASSIVE' => 'Passive', 'BANNED' => 'Banned'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'Status',
            ],
            [
                'name' => 'commercial',
                'type' => 'select_from_array',
                'options' => [0 => 'No Commercial', 1 => 'Commercial'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'Commercial Status',
            ],
            [
                'type' => 'html',
                'html' => '<div class="form-group col-lg-2"><h6 class="text-right">Package activity</h6></div>',
            ],
            [
                'name' => 'packages_cnt1',
                'type' => 'text',
                'attributes' => ['placeholder' => 'Min'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-1',
                ],
            ],
            [
                'name' => 'packages_cnt2',
                'type' => 'text',
                'attributes' => ['placeholder' => 'Max'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-1',
                ],
            ],
            [
                'name' => 'event_date_range',
                'start_name' => 'start_date',
                'end_name' => 'end_date',
                'type' => 'date_range',

                'date_range_options' => [
                    'timePicker' => true,
                    'locale' => ['format' => 'DD/MM/YYYY'],
                ],
                'wrapperAttributes' => [
                    'class' => 'col-lg-4',
                ],
            ],
        ],
        'sub_title' => 'Our members',
    ];

    protected $list = [
        'customer_id' => [
            'order' => 'customer_id',
        ],
        'dealer' => [
            'type' => 'custom.user',
            'label' => 'Dealer',
        ],
        'full_name' => [
            'label' => 'Full name',
        ],
        'packages_cnt' => [
            'label' => 'Packages',
            'order' => 'packages_cnt',
        ],
        'email',
        'cleared_phone' => [
            'label' => 'Phone',
        ],
        'passport',
        /*'passporta2' => [
            'label' => 'Passport A',
        ],
        'passportb2' => [
            'label' => 'Passport B',
        ],
        'agreement2' => [
            'label' => 'Agreement',
    ],*/
        'fin' => [
            'order' => 'fin',
        ],
        'commercial',
        'city_name' => [
            'label' => 'City',
        ],
        'zip_code' => ['label' => 'ZIP Code', 'order' => 'zip_code'],
        'azerpoct_status' => [
            'label' => 'AzerPoct Status',
    ],
        'delivery_point.description' => [
            'label' => 'Delivery point',
        ],
        'real_azeri_express_use' => [
            'label' => 'Azeri Express',
	    'type' => 'yes_no',
        ],
        'azeri_express_office.description' => [
            'label' => 'Azeri Express Office',
        ],
        'real_surat_use' => [
            'label' => 'Surat Kargo',
	    'type' => 'yes_no',
        ],
        'surat_office.description' => [
            'label' => 'Surat Kargo Office',
        ],
        'real_yenipoct_use' => [
            'label' => 'Yeni poçt',
            'type' => 'yes_no',
        ],
        'yenipoct_office.description' => [
            'label' => 'Yeni poçt Office',
        ],
        'real_kargomat_use' => [
            'label' => 'Kargomat',
            'type' => 'yes_no',
        ],
        'kargomat_office.description' => [
            'label' => 'Kargomat Office',
        ],
        'address',
        'status',
        'login_at' => ['label' => 'LastLogin', 'type' => 'date', 'order' => 'login_at'],
        'created_at' => ['label' => 'Registered', 'type' => 'date', 'order' => 'created_at'],
    ];

    protected $fields = [
        [
            'name' => 'name',
            'label' => 'Name',
            'type' => 'text',
            'validation' => 'required|string|max:30|regex:/(^([a-zA-Z]+)?$)/u',
            'wrapperAttributes' => [
                'class' => 'form-group col-md-4',
            ],
        ],
        [
            'name' => 'surname',
            'label' => 'Surname name',
            'type' => 'text',
            'validation' => 'required|string|max:30|regex:/(^([a-zA-Z]+)?$)/u',
            'wrapperAttributes' => [
                'class' => 'col-md-4',
            ],
        ],
        [
            'label' => 'Dealer',
            //'type'              => 'select2',
            'type' => 'select_from_array',
            'name' => 'parent_id',
            'attribute' => 'full_name,customer_id',
            'model' => 'App\Models\User',
            'wrapperAttributes' => [
                'class' => 'col-md-4',
                'id' => 'cleardiv',
            ],
            'validation' => 'nullable|integer',
            'allowNull' => true,
            'attributes' => [
                'data-url' => '/search-users',
                'class' => 'select2-ajax',
            ],
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10 clearfix"></div>',
        ],
        [
            'name' => 'address',
            'label' => 'Address',
            'type' => 'textarea',
            'validation' => 'required|string',
            'attributes' => [
                'rows' => 8,
            ],
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10 clearfix"></div>',
        ],
        [
            'name' => 'phone',
            'label' => 'Phone number',
            'type' => 'text',
            'validation' => 'required|string', //|unique:users,phone
            'wrapperAttributes' => [
                'class' => 'col-md-4',
            ],

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
            //'allowNull'         => true,
        ],
        [
            'name' => 'zip_code',
            'label' => 'Zip code',
            'type' => 'text',
            'validation' => 'required|string',
            'wrapperAttributes' => [
                'class' => 'form-group col-md-4',
            ],
        ],
        [
            'name' => 'azerpoct_send',
            'label' => 'Sent to Post Office Index',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-2',
            ],
    ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10 clearfix"></div>',
        ],
        [
            'label' => 'Delivery Point',
            'type' => 'select2',
            'name' => 'store_status',
            'attribute' => 'description',
            'model' => 'App\Models\DeliveryPoint',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-2',
            ],
            'validation' => 'nullable|integer',
//            'allowNull'         => true,
        ],
        [
            'name' => 'azeri_express_use',
            'label' => 'Sent to Azeri Express Office',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-2',
            ],
        ],

        [
            'label' => 'Azeri Express Office',
            'type' => 'select2',
            'name' => 'azeri_express_office_id',
            'attribute' => 'description',
            'model' => 'App\Models\AzeriExpress\AzeriExpressOffice',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-3',
            ],
            'validation' => 'nullable|integer',
            'allowNull'         => true,
        ],
        [
            'name' => 'surat_use',
            'label' => 'Sent to Surat Kargo Office',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-2',
            ],
        ],
        [
            'name' => 'yenipoct_use',
            'label' => 'Sent to Yeni poçt Office',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-2',
            ],
        ],
        [
            'name' => 'kargomat_use',
            'label' => 'Sent to Kargomat Office',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-2',
            ],
        ],
        [
            'label' => 'Surat Kargo Office',
            'type' => 'select2',
            'name' => 'surat_office_id',
            'attribute' => 'description',
            'model' => 'App\Models\Surat\SuratOffice',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-3',
            ],
            'validation' => 'nullable|integer',
            'allowNull'         => true,
        ],
        [
            'label' => 'Yeni poçt Office',
            'type' => 'select2',
            'name' => 'yenipoct_office_id',
            'attribute' => 'description',
            'model' => 'App\Models\YeniPoct\YenipoctOffice',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-3',
            ],
            'validation' => 'nullable|integer',
            'allowNull'         => true,
        ],
        [
            'label' => 'Kargomat Office',
            'type' => 'select2',
            'name' => 'kargomat_office_id',
            'attribute' => 'description',
            'model' => 'App\Models\Kargomat\KargomatOffice',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-3',
            ],
            'validation' => 'nullable|integer',
            'allowNull'         => true,
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10 clearfix"></div>',
        ],
        [
            'name' => 'customer_id',
            'label' => 'Customer ID',
            'type' => 'text',
//            'attributes' => [
//                'readonly' => 'readonly',
//            ],
            'validation' => 'required|string|unique:users,customer_id',
        ],
        [
            'name' => 'passport',
            'label' => 'Passport',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'form-group col-md-4',
            ],
            'validation' => 'required|string|unique:users,passport',
        ],
        [
            'name' => 'fin',
            'label' => 'FIN',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'form-group col-md-4',
            ],
            'validation' => 'required|string|unique:users,fin',
        ],
        [
            'name' => 'company',
            'label' => 'Company name',
            'type' => 'text',
            'hint' => 'For companies',
            'wrapperAttributes' => [
                'class' => 'from-group col-md-4',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10 clearfix"></div>',
        ],
        [
            'name' => 'email',
            'label' => 'Email',
            'type' => 'email',
            'validation' => 'required|email|unique:users,email',
            'wrapperAttributes' => [
                'class' => 'from-group col-md-3',
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
                'class' => 'from-group col-md-3',
            ],
        ],
        [
            'name' => 'verified',
            'label' => 'Email Verified',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-2',
            ],
        ],
        [
            'name' => 'check_customs',
            'label' => 'Use In Smart Customs',
            'type' => 'checkbox',
            'default' => '1',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-2',
            ],
        ],
        [
            'name' => 'is_commercial',
            'label' => 'Is Commercial',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-2',
            ],
        ],
        [
            'name' => 'voen',
            'label' => 'Voen',
            'type' => 'text',
            'hint' => 'For commercial only',
            'wrapperAttributes' => [
                'class' => 'from-group col-md-4',
            ],
            'validation' => 'nullable|string|required_if:is_commercial,1',
        ],
        [
            'name' => 'sms_verification_status',
            'label' => 'Phone # Verified',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-2',
            ],
        ],
        [
            'name' => 'status',
            'label' => 'Status',
            'type' => 'select_from_array',
            'options' => ['ACTIVE' => 'Active', 'PASSIVE' => 'Passive', 'BANNED' => 'Banned'],
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-2',
            ],
        ],


        [
            'name' => 'discount_check',
            'label' => 'Discount Check',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-4',
            ],
        ],

        [
            'label' => 'Dicount Country',
            'type' => 'select2',
            'name' => 'discount_country_id',
            'attribute' => 'country_name',
            'model' => 'App\Models\Country',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-4',
            ],
            'validation' => 'nullable|integer',
            'allowNull'         => true,
        ],

        [
            'name' => 'discount_percent',
            'label' => 'Discount Percent',
            'type' => 'text',
            'validation' => 'required|integer|max:100|min:1',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-4',
            ],
        ],

        [
            'name' => 'passporta',
            'label' => 'Passport A',
            'type' => 'file',
            'wrapperAttributes' => [
                'class' => 'col-md-3 text-center',
            ],
            'validation' => 'nullable|mimes:jpeg,jpg,png,gif,svg,pdf',
        ],
        [
            'name' => 'passportb',
            'label' => 'Passport B',
            'type' => 'file',
            'wrapperAttributes' => [
                'class' => 'col-md-3 text-center',
            ],
            'validation' => 'nullable|mimes:jpeg,jpg,png,gif,svg,pdf',
        ],
        [
            'name' => 'agreement',
            'label' => 'Agreement',
            'type' => 'file',
            'wrapperAttributes' => [
                'class' => 'col-md-3 text-center',
            ],
            'validation' => 'nullable|mimes:jpeg,jpg,png,gif,svg,pdf',
        ],
    ];

    protected $extraActions = [
        [
            'route' => 'users.logs',
            'key' => 'id',
            'label' => 'Logs',
            'icon' => 'list',
            'color' => 'default',
            'target' => '_blank',
        ],
    ];



    public function __construct()
    {
        $this->fields[21]['default'] = User::generateCode();
        $this->middleware(function ($request, $next) {
            if (optional(auth()->user()->role)->id == 10 or optional(auth()->user()->role)->id == 26) {
                unset($this->list['dealer']);
                foreach ($this->view['search'] as $key => $item) {
                    if (isset($item['name']) && $item['name'] === 'dealer') {
                        unset($this->view['search'][$key]);
                    }
                }
            }

            // Password field yalnız Super Admin-lərə görsənsin
            if (optional(auth()->guard('admin')->user()->role)->name !== 'super_admin') {
                foreach ($this->fields as $key => $field) {
                    if (isset($field['name']) && $field['name'] === 'password') {
                        unset($this->fields[$key]);
                        break;
                    }
                }
            }

            parent::__construct();
            return $next($request);
        });
    }



    public function generateValidation($action, $id = false)
    {
        $validation = parent::generateValidation($action, $id);

        // Password field-i yalnız Super Admin dəyişə bilsin
        if (optional(auth()->guard('admin')->user()->role)->name !== 'super_admin') {
            unset($validation['password']);
        }

        $validation['azeri_express_office_id'] = [
            'nullable',
            'integer',
            function ($attribute, $value, $fail) {
                $this->validateSingleOfficeSelection($attribute, $value, $fail);
            },
            'required_if:azeri_express_use,1'
        ];

        $validation['surat_office_id'] = [
            'nullable',
            'integer',
            function ($attribute, $value, $fail) {
                $this->validateSingleOfficeSelection($attribute, $value, $fail);
            },
            'required_if:surat_use,1'
        ];

        $validation['yenipoct_office_id'] = [
            'nullable',
            'integer',
            function ($attribute, $value, $fail) {
                $this->validateSingleOfficeSelection($attribute, $value, $fail);
            },
            'required_if:yenipoct_use,1'
        ];

        $validation['kargomat_office_id'] = [
            'nullable',
            'integer',
            function ($attribute, $value, $fail) {
                $this->validateSingleOfficeSelection($attribute, $value, $fail);
            },
            'required_if:kargomat_use,1'
        ];

        return $validation;
    }

    /**
     * Validate that only one office is selected
     */
    protected function validateSingleOfficeSelection($attribute, $value, $fail)
    {
        if ($value) {
            $request = request();
            $otherOffices = [
                'azeri_express_office_id',
                'surat_office_id',
                'yenipoct_office_id',
                'kargomat_office_id'
            ];

            $selectedCount = 0;
            foreach ($otherOffices as $office) {
                if ($request->has($office) && $request->input($office) && $office !== $attribute) {
                    $selectedCount++;
                }
            }

            if ($selectedCount > 0) {
                $fail('Yalnız bir çatdırılma ofisi seçə bilərsiniz.');
            }
        }
    }

    /**
     * Process data before saving
     */
    public function beforeSave($item, $request)
    {
        // Password field-i yalnız Super Admin dəyişə bilsin
        if (optional(auth()->guard('admin')->user()->role)->name !== 'super_admin') {
            $request->request->remove('password');
        }

        $data = $request->all();

        // Check which office is selected
        $selectedOffice = null;
        $officeFields = [
            'azeri_express_office_id',
            'surat_office_id',
            'yenipoct_office_id',
            'kargomat_office_id'
        ];

        foreach ($officeFields as $field) {
            if (!empty($data[$field])) {
                $selectedOffice = $field;
                break;
            }
        }

        // If an office is selected, nullify others and ensure the corresponding "use" is checked
        if ($selectedOffice) {
            foreach ($officeFields as $field) {
                if ($field !== $selectedOffice) {
                    $item->{$field} = null;
                    // Also uncheck the corresponding "use" checkbox
                    $useField = str_replace('_office_id', '_use', $field);
                    $item->{$useField} = 0;
                } else {
                    // Ensure the corresponding "use" is checked
                    $useField = str_replace('_office_id', '_use', $field);
                    $item->{$useField} = 1;
                }
            }
        }

        return $item;
    }

    /**
     * Override store method to include beforeSave logic
     */
//    public function store(Request $request)
//    {
//        if (!$this->can['create']) {
//            return abort(403);
//        }
//
//        $this->validate($request, $this->generateValidation('store'));
//
//        $item = $this->modelObject;
//        $item = $this->beforeSave($item, $request);
//
//        // ... rest of the store method from parent controller
//        return parent::store($request);
//    }

    /**
     * Override update method to include beforeSave logic
     */
//    public function update(Request $request, $id)
//    {
//        $this->validate($request, $this->generateValidation('update', $id));
//
//        $item = $this->modelObject->find($id);
//        $item = $this->beforeSave($item, $request);
//
//        // ... rest of the update method from parent controller
//        return parent::update($request, $id);
//    }

    public function indexObject()
    {
        $validator = Validator::make(request()->all(), [
            'q' => 'string',
        ]);

        if ($validator->failed()) {
            Alert::error('Unexpected variables!');

            return redirect()->route("my.dashboard");
        }

        if (request()->get('sort') != null) {
            $sortKey = explode("__", request()->get('sort'))[0];
            $sortType = explode("__", request()->get('sort'))[1];
            $items = User::with(['city', 'dealer'])->orderBy($sortKey, $sortType)->orderBy('id', 'desc');
        } else {
            $items = User::with(['city', 'dealer'])->orderBy('created_at', 'desc')->orderBy('id', 'desc');
        }
        $pkg_where = '';

        if (Request::get('start_date') != null) {
            $dateField = Request::get('date_by', 'created_at');
            $pkg_where = ' and packages.' . $dateField . ">='" . Request::get('start_date') . " 00:00:00'";
        }
        if (Request::get('end_date') != null) {
            $dateField = Request::get('date_by', 'created_at');
            $pkg_where .= ' and packages.' . $dateField . "<='" . Request::get('end_date') . " 23:59:59'";
        }
        if (request()->get('packages_cnt1') != null || request()->get('packages_cnt2') != null) {
            $items = $items->selectRaw('users.*,(select count(*) from packages where deleted_at is null and packages.user_id=users.id' . $pkg_where . ') as packages_cnt');
        } else {
            $items = $items->selectRaw('users.*,(select count(*) from packages where deleted_at is null and packages.user_id=users.id) as packages_cnt');
            if (Request::get('start_date') != null) {
                $dateField = Request::get('date_by', 'created_at');
                $items->where('users.' . $dateField, '>=', Request::get('start_date') . " 00:00:00");
            }
            if (Request::get('end_date') != null) {
                $dateField = Request::get('date_by', 'created_at');
                $items->where('users.' . $dateField, '<=', Request::get('end_date') . " 23:59:59");
            }
        }

        /* Filter cities */
        $cities = auth()->guard('admin')->user()->cities->pluck('id')->all();
        if ($cities) {
            $items->whereIn('city_id', $cities);
        }

        if (request()->get('q') != null) {
            $q = request()->get('q');
            $items->where(function ($query) use ($q) {
                $query->where("customer_id", "LIKE", "%" . $q . "%")->orWhere("email", "LIKE", "%" . $q . "%")->orWhere(DB::raw('concat(trim(name)," ",trim(surname))'), 'LIKE', "%" . $q . "%")->orWhere("phone", "LIKE", "%" . $q . "%")->orWhere("passport", "LIKE", "%" . $q . "%")->orWhere("company", "LIKE", "%" . $q . "%")->orWhere("address", "LIKE", "%" . $q . "%")->orWhere("status", "LIKE", "%" . $q . "%")->orWhere('fin', 'LIKE', '%' . $q . '%');
            });
        }
        if (request()->get('dealer') != null) {
            $q = request()->get('dealer');
            $items->whereHas('dealer', function (
                $query
            ) use ($q) {
                $query->where('customer_id', 'LIKE', '%' . $q . '%')->orWhere('passport', 'LIKE', '%' . $q . '%')->orWhere('fin', 'LIKE', '%' . $q . '%')->orWhere('phone', 'LIKE', '%' . $q . '%')->orWhere('email', 'LIKE', '%' . $q . '%')->orWhere(\Illuminate\Support\Facades\DB::raw('concat(trim(name)," ",trim(surname))'), 'LIKE', "%" . $q . "%");
            });
        }

        if (request()->get('city_id') != null) {
            $items->where('city_id', request()->get('city_id'));
        }


        if (request()->get('packages_cnt2') != null) {
            $items->whereRaw('(select count(*) from packages where deleted_at is null and packages.user_id=users.id' . $pkg_where . ') <=' . request()->get('packages_cnt2'));
        }

        if (request()->get('packages_cnt1') != null) {
            $items->whereRaw('(select count(*) from packages where deleted_at is null and packages.user_id=users.id' . $pkg_where . ') >=' . request()->get('packages_cnt1'));
        }


        if (request()->get('status') != null) {
            $items->where('status', request()->get('status'));
        }

        if (request()->get('fin') !== null) {
            if (request()->get('fin')) {
                $items->whereNotNull('fin');
            } else {
                $items->whereNull('fin');
            }
        }

        if (request()->get('commercial') !== null) {
            if (request()->get('commercial')) {
                $items->where('is_commercial', 1);
            } else {
                $items->where('is_commercial', 0);
            }
        }
        $items_all = null;

        if (request()->has('search_type') && request()->get('search_type') == 'export') {
            if ($items->count()) {
                $items = $items->get();
            } else {
                //$items_all = $items->get();
                $items = $items->paginate($this->limit);
            }
        } else {
            //$items_all = $items->get();
            $items = $items->paginate($this->limit);
        }

        View::share('items_all', $items_all);
        return $items;
    }

    public function export($items = null)
    {
        if (request()->has('hidden_items')) {
            $items = explode(",", request()->get('hidden_items'));
        }

        return Excel::download(new UsersExport($items), 'users_' . uniqid() . '.xlsx');
    }

    public function search()
    {

        $q = request()->get('q') != null ? request()->get('q') : request()->get('term');

        $users = User::where(function ($query) use ($q) {
            $query->where("customer_id", "LIKE", "%" . $q . "%")->orWhere(\Illuminate\Support\Facades\DB::raw('concat(trim(name)," ",trim(surname))'), 'LIKE', "%" . $q . "%");
        })->take(15)->get();
        $data = [];

        foreach ($users as $user) {
            $data[] = ["id" => $user->id, "text" => $user->full_name . " (" . $user->customer_id . ")"];
        }

        return \GuzzleHttp\json_encode(["results" => $data]);
    }

    public function logs($id)
    {
        $logs = Activity::where('content_id', $id)->where('content_type', User::class)->orderBy('id', 'desc')->get();
        if (!$logs) {
            return back();
        }

        return view('admin.widgets.logs', compact('logs', 'id'));
    }
}
