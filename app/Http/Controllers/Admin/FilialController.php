<?php

namespace App\Http\Controllers\Admin;

use Alert;
use App\Exports\Admin\FilialsExport;
use App\Exports\Admin\UsersExport;
use App\Models\Filial;
use App\Models\DeliveryPoint;
use App\Models\AzeriExpress\AzeriExpressOffice;
use App\Models\Azerpost\AzerpostOffice;
use App\Models\Surat\SuratOffice;
use Auth;
use DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Route;
use Session;
use stdClass;
use Validator;
use View;
use Excel;


class FilialController extends Controller
{
    protected $modelName = 'Filial';
    protected $route = 'filials';
    protected $can = [
        'export' => true,
    ];
    protected $view = [
        'formColumns' => 10,
        'search' => [
            [
                'name' => 'q',
                'type' => 'text',
                'attributes' => ['placeholder' => 'Search...'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
            ],
            [
                'name' => 'type',
                'type' => 'select_from_array',
                'options' => ['ASE' => 'ASE', 'AZPOST' => 'AZPOST', 'AZEXP' => 'AZEXP', 'SURAT' => 'SURAT','UNKNOWN'=>'UNKNOWN','YP'=>'YP','KARGOMAT' => 'KARGOMAT',],
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
		'allowNull' => 'All Types',
            ],
            [
                'type' => 'select2',
                'name' => 'city_id',
                'attribute' => 'name',
                'model' => 'App\Models\City',
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'All cities',
            ],
        ],
    ];

    protected $notificationKey = 'type_id';

    protected $extraActions = [
/*        [
            'key' => 'type_id',
            'label' => 'Auto Pudo',
            'icon' => 'checkmark',
            'route' => 'filials.auto_pudo',
            'color' => 'info',
            'target' => '_blank',
    ],*/
    ];

    protected $list = [
        'type_id' => [
            'label' => 'ID',
        ],
        'foreign_id' => [
            'label' => 'Foreign Id',
        ],
        'type' => [
            'label' => 'Type',
        ],
        'name' => [
            'label' => 'Name',
        ],
        'description' => [
            'label' => 'Description',
        ],
        'city_name' => [
            'label' => 'City',
//            'type' => 'country',
        ],
        'address' => [
            'label' => 'Address',
        ],
        'contact_name' => [
            'label' => 'Contact Name',
        ],
        'contact_phone' => [
            'label' => 'Contact Phone',
        ],
        'work_time' => [
            'label' => 'Work Time',
        ],
        'all_keys' => [
	    'type' => 'raw',
            'label' => 'Keys',
        ],
        'created_at' => [
            'label' => 'At',
            'type' => 'date',
        ],
    ];

    protected $fields = [
        [
            'name' => 'type',
            'label' => 'Type',
	    'type' => 'select_from_array',
            'hint' => 'Name',
            'options' => ['ASE' => 'ASE', 'AZPOST' => 'AZPOST', 'AZEXP' => 'AZEXP', 'SURAT' => 'SURAT','UNKNOWN' => 'UNKNOWN','YP'=>'YP','KARGOMAT'=>'KARGOMAT'],
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'required|string',
        ],
        [
            'name' => 'name',
            'label' => 'Name',
            'type' => 'text',
            'hint' => 'Name',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'required|string',
        ],
        [
            'name' => 'name_en',
            'label' => 'Name Eng',
            'type' => 'text',
            'hint' => 'Namei Eng',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'name' => 'description',
            'label' => 'Description',
            'type' => 'text',
            'hint' => 'Description',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
            'validation' => 'required|string',
        ],
        [
            'name' => 'description_en',
            'label' => 'Description Eng',
            'type' => 'text',
            'hint' => 'Description Eng',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
            'validation' => 'nullable|string',
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
                'class' => 'form-group col-md-2',
            ],
            'validation' => 'nullable|integer',
        ],
        [
            'name' => 'address',
            'label' => 'Address',
            'type' => 'text',
            'hint' => 'Address',
            'wrapperAttributes' => [
                'class' => 'col-md-5',
            ],
            'validation' => 'required|string',
        ],
        [
            'name' => 'address_en',
            'label' => 'Address Eng',
            'type' => 'text',
            'hint' => 'Address Eng',
            'wrapperAttributes' => [
                'class' => 'col-md-5',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10"></div>',
        ],
        [
            'name' => 'contact_name',
            'label' => 'Contact Name',
            'type' => 'text',
            'hint' => 'Contact Name',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'name' => 'contact_phone',
            'label' => 'Contact Phone',
            'type' => 'text',
            'hint' => 'Contact Phone',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'name' => 'is_ozon',
            'label' => 'is_ozon',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
        ],
        [
            'name' => 'is_temu',
            'label' => 'is_temu',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
        ],
        [
            'name' => 'is_active',
            'label' => 'is_active',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10">Working time opening</div>',
        ],
        [
            'name' => 'monday_opening_time',
            //'label' => 'Monday Opening',
            'type' => 'text',
            'hint' => 'Monday Opening',
            'wrapperAttributes' => [
                'class' => 'col-md-1',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'name' => 'tuesday_opening_time',
            //'label' => 'Tuestday Opening',
            'type' => 'text',
            'hint' => 'Tuesday Opening',
            'wrapperAttributes' => [
                'class' => 'col-md-1',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'name' => 'wednesday_opening_time',
            //'label' => 'Wednesday Opening',
            'type' => 'text',
            'hint' => 'Wednesday Opening',
            'wrapperAttributes' => [
                'class' => 'col-md-1',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'name' => 'thursday_opening_time',
            //'label' => 'Thursday Opening',
            'type' => 'text',
            'hint' => 'Thursday Opening',
            'wrapperAttributes' => [
                'class' => 'col-md-1',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'name' => 'friday_opening_time',
            //'label' => 'friday Opening',
            'type' => 'text',
            'hint' => 'Friday Opening',
            'wrapperAttributes' => [
                'class' => 'col-md-1',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'name' => 'saturday_opening_time',
            //'label' => 'Saturday Opening',
            'type' => 'text',
            'hint' => 'Saturday Opening',
            'wrapperAttributes' => [
                'class' => 'col-md-1',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'name' => 'sunday_opening_time',
            //'label' => 'Sunday Opening',
            'type' => 'text',
            'hint' => 'Sunday Opening',
            'wrapperAttributes' => [
                'class' => 'col-md-1',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10">Working time closing</div>',
        ],
        [
            'name' => 'monday_closing_time',
            //'label' => 'Monday Closing',
            'type' => 'text',
            'hint' => 'Monday Closing',
            'wrapperAttributes' => [
                'class' => 'col-md-1',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'name' => 'tuesday_closing_time',
            //'label' => 'Tuestday Closing',
            'type' => 'text',
            'hint' => 'Tuesday Closing',
            'wrapperAttributes' => [
                'class' => 'col-md-1',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'name' => 'wednesday_closing_time',
            //'label' => 'Wednesday Closing',
            'type' => 'text',
            'hint' => 'Wednesday Closing',
            'wrapperAttributes' => [
                'class' => 'col-md-1',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'name' => 'thursday_closing_time',
            //'label' => 'Thursday Closing',
            'type' => 'text',
            'hint' => 'Thursday Closing',
            'wrapperAttributes' => [
                'class' => 'col-md-1',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'name' => 'friday_closing_time',
            //'label' => 'friday Closing',
            'type' => 'text',
            'hint' => 'Friday Closing',
            'wrapperAttributes' => [
                'class' => 'col-md-1',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'name' => 'saturday_closing_time',
            //'label' => 'Saturday Closing',
            'type' => 'text',
            'hint' => 'Saturday Closing',
            'wrapperAttributes' => [
                'class' => 'col-md-1',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'name' => 'sunday_closing_time',
            //'label' => 'Sunday Closing',
            'type' => 'text',
            'hint' => 'Sunday Closing',
            'wrapperAttributes' => [
                'class' => 'col-md-1',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10">Lunch Break time</div>',
        ],
        [
            'name' => 'lunch_break_opening_time',
            'type' => 'text',
            'hint' => 'Lunch Break Opening',
            'wrapperAttributes' => [
                'class' => 'col-md-1',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'name' => 'lunch_break_closing_time',
            'type' => 'text',
            'hint' => 'Lunch Break Closing',
            'wrapperAttributes' => [
                'class' => 'col-md-1',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10"></div>',
        ],
        [
            'name' => 'latitude',
            'label' => 'Latitude',
            'type' => 'text',
            'hint' => 'Latitude',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'name' => 'longitude',
            'label' => 'Longitude',
            'type' => 'text',
            'hint' => 'Longitude',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'type' => 'html',
            'html' => '<div id="package_goods"><div class="form-group mt-10 col-lg-12"><h3 class="text-center">Filial keys</h3></div>',
        ],
        [
            'type' => 'html',
            'html' => '<div class="row"><div id="key_section" class="">',
        ],
        [
            'type' => 'html',
            'html' => '<div class="row type_item" id="main_key_item">',
        ],
        [
            'name' => 'types[]',
            'type' => 'select_from_array',
            'options' => ['ADDRESS' => 'ADDRESS', 'CITY' => 'CITY'],//, 'REGION' => 'REGION'],
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
        ],
        [
            'name' => 'keys[]',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-4',
            ],
            'attributes' => [
                'data-validation' => 'required number',
                'data-validation-allowing' => "float",
            ],
            'validation' => 'nullable|numeric',
        ],
        [
            'type' => 'html',
            'html' => '<div class="col-lg-1"> <span class="btn btn-danger btn-icon btn_minus"><i
                                        class="icon-minus2"></i></span></div>',
        ],
        [
            'type' => 'html',
            'html' => '</div></div>'
        ],
        [
            'type' => 'html',
            'html' => '<div class="col-lg-1"> <span id="add_key" class="btn btn-primary btn-icon"><i
                                        class="icon-plus2"></i></span></div>',
        ],
        [
            'type' => 'html',
            'html' => '</div></div>',
        ],
    ];

   // protected $with = ['package'];

    public function __construct(Request $request)
    {
        return parent::__construct();
    }

    public function create()
    {
	$old_keys=NULL;
	if(session('_old_input')) {
	    $old_input=session('_old_input');
	    if(array_key_exists('types',$old_input) && array_key_exists('keys',$old_input) && $old_input['types'] && $old_input['keys']) {
		 $old_keys=[];
		 $o_keys=$old_input['keys'];
		 $o_types=$old_input['types'];
		 for($ok=0;$ok<=count($o_keys)-1;$ok++) {
		    $old_keys[]=(object)['name'=>$o_keys[$ok],'type'=>$o_types[$ok]];
		 }
	    }
	}
        View::share('old_keys', $old_keys);
        return parent::create();
    }

    public function edit($id)
    {

        $id = \request()->route('id') != null ? \request()->route('id') : $id;
        if (Auth::guard('admin')->check()) {
            if (!Auth::guard('admin')->user()->can('update-' . $this->route)) {
                return redirect("/");
            }
        } else {
            if (!$this->can['update']) {
                return redirect("/");
            }
        }

        $this->setCurrentLang();
        $this->routeParams['id'] = $id;

        $form = [
            'selfLink' => route($this->route . '.edit', $this->routeParams),
            'route' => route($this->route . '.update', $this->routeParams),
            'method' => 'put',
        ];

        $filial = NULL;
        if (method_exists($this, 'editObject')) {
            $filial = $this->editObject($id);
            if (is_array($item) and array_key_exists('error', $item)) {
                Alert::error($item['error']);

                return redirect()->route($this->route . '.index');
            }
        } else {
            //$pc = PackageCarrier::find($id);
            $filial = Filial::where('type_id', $id)->first();
        }

        if (!$filial) {
            Alert::error(trans('saysay::crud.not_found'));

            return back();
        }

        if ($this->currentLang) {
            $pc->setDefaultLocale($this->currentLang);
        }

        $item = $filial;
	$old_keys=NULL;
	if(session('_old_input')) {
	    $old_input=session('_old_input');
	    if(array_key_exists('types',$old_input) && array_key_exists('keys',$old_input) && $old_input['types'] && $old_input['keys']) {
		 $old_keys=[];
		 $o_keys=$old_input['keys'];
		 $o_types=$old_input['types'];
		 for($ok=0;$ok<=count($o_keys)-1;$ok++) {
		    $old_keys[]=(object)['name'=>$o_keys[$ok],'type'=>$o_types[$ok]];
		 }
	    }
	}
        return view($this->panelView('form'), compact('item', 'form','old_keys'));
    }

    public function store(Request $request)
    {
	$validator = Validator::make($request->all(), $this->generateValidation('store'));

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

	$action='updated';
	$type=NULL;
	if(isset($request->type_id) && $request->type_id) {
	  list($type,$id)=explode('-',$request->type_id);
	  $item=Filial::findByTypeId($request->type_id);
	} else {
	  $type= $request->get($this->fields[0]['name']);
	  $action='created';
	  $item=Filial::createByType($type);
	}
	$keys=[];
	$types=[];
	foreach ($this->fields as $field) {
	    if (isset($field['name']) and !isset($field['attributes']['disabled'])) {
		if($field['name'] == 'type') continue;
		if($field['name'] == 'keys[]') {
		    $keys=$request->get('keys');
		    continue;
		}
		if($field['name'] == 'types[]') {
		    $types=$request->get('types');
		    continue;
		}

	    	$item->{$field['name']} = $request->get($field['name']);

	    }
	}
	$haveCityKey=true;
	if($keys && $types) {
	    foreach($keys as $num => $key) {
	        if(empty($key)) continue;
		$haveCityKey=false;
		if($types[$num]=='CITY') {
		     $haveCityKey=true;
		     break;
		}
	    }
	}
	if(!$haveCityKey) {
	    $validator->getMessageBag()->add('types[]', 'At least one key must be CITY');
	    return back()->withErrors($validator)->withInput();
	}
	$item->save();
	$type_id=$type.'-'.$item->id;
	DB::delete("delete from filial_keys where filial_type_id='".$type_id."'");
	if($keys)
	foreach($keys as $num => $key) {
	    if(empty($key)) continue;
	    DB::insert("insert into filial_keys(filial_type_id,name,type) values('".$type_id."','".$key."','".$types[$num]."')");
	}
        Alert::success(trans('saysay::crud.action_alert', [
            'name' => $type.'-'.$item->id.' Filial',
            'key' => 'Name',
            'value' => $item->name,
            'action' => $action,
        ]));

        return redirect()->route($this->route . '.index', $this->routeParams);
    }

    public function destroy($type_id)
    {
	$item=Filial::findByTypeId($type_id);
        if (!$item) {
            $message = "Filial Not found";
            $request->session()->flash('error', $message);
            return redirect()->route($this->route . '.index', $this->routeParams);
        }
	$name=$item->name;
	DB::delete("delete from filial_keys where filial_type_id='".$type_id."'");
	$item->delete();

        Alert::success(trans('saysay::crud.action_alert', [
            'name' => $type_id.' Filial',
            'key' => 'Name',
            'value' => $name,
            'action' => 'deleted',
        ]));

        return redirect()->route($this->route . '.index', $this->routeParams);
    }


    /**
     * @return LengthAwarePaginator
     */
    public function indexObject()
    {
        $validator = Validator::make(\Request::all(), [
            'q' => 'string',
            'city_id' => 'integer',
            'type' => 'string',
        ]);

        if ($validator->failed()) {
            Alert::error('Unexpected variables!');

            return redirect()->route("my.dashboard");
        }

        //$items = PackageCarrier::whereNull('package_id')->latest();
        $items = Filial::orderBy('type', 'ASC')->orderBy('name','ASC');

        $city_id = \Request::get('city_id');
        if ($city_id) {
            $items->where('city_id', $city_id);
        }
        $type = \Request::get('type');
        if ($type) {
            $items->where('type', $type);
        }

        if (\Request::get('q') != null) {
            $q = \Request::get('q');
            $items->where(function ($query) use ($q) {
                $query->orWhere("address", "LIKE", "%" . $q . "%")
                    ->orWhere("address_en", "LIKE", "%" . $q . "%")
                    ->orWhere("name", "LIKE", "%" . $q . "%")
                    ->orWhere("name_en", "LIKE", "%" . $q . "%")
                    ->orWhere("contact_name", "LIKE", "%" . $q . "%")
                    ->orWhere("contact_phone", "LIKE", "%" . $q . "%")
                    ->orWhereRaw("exists(select filial_keys.id from filial_keys where filial_keys.filial_type_id=filials_v.type_id and name like '%".$q."%')");
            });
        }


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


        return $items;
    }

    public function ajax(Request $request, $id)
    {
        if ($request->get('name') == 'status') {
            $used = Package::find($id);

            $data = [];

            if (trim($used->status) != trim($request->get('value'))) {
                $data['status'] = [
                    'before' => trim($used->status),
                    'after' => trim($request->get('value')),
                ];
            }

            if (!empty($data)) {
                $log = new PackageLog();
                $log->data = json_encode($data);
                $log->admin_id = Auth::guard('admin')->user()->id;
                $log->package_id = $id;
                $log->save();
            }
        }

        return parent::ajax($request, $id);
    }

    public function export($items = null)
    {
        if (request()->has('hidden_items')) {
            $items = explode(",", request()->get('hidden_items'));
        }


        return Excel::download(new FilialsExport($items), 'filials_' . uniqid() . '.xlsx');
    }
}
