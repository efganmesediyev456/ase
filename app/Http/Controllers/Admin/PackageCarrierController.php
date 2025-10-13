<?php

namespace App\Http\Controllers\Admin;

use Alert;
use App\Models\CustomsModel;
use App\Models\CustomsType;
use App\Models\Package;
use App\Models\Track;
use App\Models\PackageCarrier;
use App\Models\PackageGood;
use App\Models\PackageLog;
use App\Services\Package\PackageService;
use Auth;
use DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Route;
use Session;
use stdClass;
use Validator;
use App\Services\Integration\UnitradeService;

class PackagecarrierController extends Controller
{
    protected $modelName = 'PackageCarrier';
    protected $route = 'package_carriers';

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
                'name' => 'ase_status',
                'type' => 'select_from_array',
                'options' => [0 => 'NOT ASESHOP PACKAGES', 1 => 'ONLY ASESHOP PACKAGES', 2 => 'ALL PACKAGES'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
            ],
            [
                'name' => 'is_commercial',
                'type' => 'select_from_array',
                'options' => [1 => 'Only Commercial', 2 => 'Only Not Commercial'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'Commercial Status',
            ],
            [
                'type' => 'select2',
                'name' => 'country_id',
                'attribute' => 'name',
                'model' => 'App\Models\Country',
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'All countries',
            ],
            [
                'name' => 'incustoms',
                'type' => 'select_from_array',
                'optionsFromConfig' => 'ase.attributes.package.incustoms',
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'Customs status',
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
    ];

    protected $notificationKey = 'custom_id';

    protected $extraActions = [
        [
            'key' => 'id',
            'role' => 'customs-check',
            'label' => 'Customs check',
            'icon' => 'checkmark',
            'route' => 'package_carriers.packagedepeshcheck',
            'color' => 'info',
            'target' => '_blank',
        ],
        [
            'route' => 'package_carriers.depesh',
            'key' => 'id',
            'label' => 'Depesh',
            'icon' => 'cart',
            'color' => 'default',
            //'target' => '_blank',
        ],
        [
            'route' => 'package_carriers.packagecanceldepesh',
            'key' => 'id',
            'label' => 'Cancel Depesh',
            'icon' => 'undo',
            'color' => 'default',
            'target' => '_blank',
        ],
        [
            'route' => 'package_carriers.regnumber',
            'key' => 'id',
            'label' => 'Declaration number',
            'icon' => 'basket',
            'color' => 'default',
            //'target' => '_blank',
        ],
    ];

    protected $list = [
        'package.custom_id' => [
            'label' => 'CWB #',
        ],
        'trackingNumber' => [
            'label' => 'Track #',
        ],
        'country' => [
            'label' => 'Country',
            'type' => 'country',
        ],
        'idxal_name' => [
            'label' => 'Idxal Name',
        ],
        'ixrac_name' => [
            'label' => 'Ixrac Name',
        ],
        'trackingNumber' => [
            'label' => 'Track #',
        ],
        'carrier' => [
            'type' => 'carrier',
            'label' => 'Customs',
        ],
        'inserT_DATE' => [
            'label' => 'Insert Date',
        ],
        'airwaybill' => [
            'label' => 'Airwaybill',
        ],
        'depesH_DATE' => [
            'label' => 'Depesh Date',
        ],
        'depesH_NUMBER' => [
            'label' => 'Depesh Number',
        ],
        'ecoM_REGNUMBER' => [
            'label' => 'Declration Number',
        ],
        'code' => [
            'label' => 'Code',
        ],
        'errorMessage' => [
            'label' => 'Error',
        ],
        'status' => [
            'label' => 'Status',
        ],
        'created_at' => [
            'label' => 'At',
            'type' => 'date',
        ],
    ];

    protected $r_fields = [
        [
            'name' => 'fname',
            'type' => 'hidden',
            'default' => 'regnumber',
            'short' => true,
        ],
        [
            'name' => 'ecoM_REGNUMBER',
            'label' => 'Declaration Reg Number',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
            'validation' => 'nullable|string',
        ],
    ];

    protected $d_fields = [
        [
            'name' => 'fname',
            'type' => 'hidden',
            'default' => 'depesh',
            'short' => true,
        ],
        [
            'name' => 'airwaybill',
            'label' => 'airwaybill',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
            'validation' => 'required|string',
        ],
        [
            'name' => 'depesH_NUMBER',
            'label' => 'Depesh Number',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
            'validation' => 'required|string',
        ],
    ];

    protected $fields = [
        [
            'label' => 'Country',
            'type' => 'select2',
            'name' => 'country_id',
            'attribute' => 'name',
            'model' => 'App\Models\Country',
            'wrapperAttributes' => [
                'class' => 'form-group col-md-2',
            ],
            'validation' => 'required|integer',
        ],
        [
            //'name'              => 'trackingNumber',
            'name' => 'trackinG_NO',
            'label' => 'Track #',
            'type' => 'text',
            'hint' => 'Special Tracking number',
            'prefix' => '<i class="icon-truck"></i>',
            'wrapperAttributes' => [
                'class' => 'col-md-4',
            ],
            'validation' => 'required|string',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10"></div>',
        ],
        [
            'name' => 'isCommercial',
            'label' => 'Commercial',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'required|integer',
        ],
        [
            'name' => 'voen',
            'label' => 'Voen',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'required_if:isCommercial,1',
        ],
        [
            'name' => 'airwaybill',
            'label' => 'airwaybill',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'required_if:isCommercial,1',
        ],
        [
            'name' => 'depesH_NUMBER',
            'label' => 'Depesh Number',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'required_if:isCommercial,1',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10"></div>',
        ],
        [
            'label' => 'Document Type',
            'type' => 'select_from_array',
            'name' => 'documenT_TYPE',
            'options' => ['PinCode' => 'FIN', 'PassportNumber' => 'PASSPORT'],
            'wrapperAttributes' => [
                'class' => 'form-group col-md-2',
            ],
            'validation' => 'required|string',
        ],
        [
            'name' => 'fin',
            'label' => 'FIN/PASSPORT',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'required|string',
        ],
        [
            'name' => 'phone',
            'label' => 'Phone',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
            'validation' => 'required|string',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10"></div>',
        ],
        [
            'name' => 'idxaL_NAME',
            'label' => 'IdxaL Name',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-4',
            ],
            'validation' => 'required|string',
        ],
        [
            'name' => 'idxaL_ADRESS',
            'label' => 'IdxaL Adress',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-6',
            ],
            'validation' => 'required|string',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10"></div>',
        ],
        [
            'name' => 'ixraC_NAME',
            'label' => 'IxraC Name',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-4',
            ],
            'validation' => 'required|string',
        ],
        [
            'name' => 'ixraC_ADRESS',
            'label' => 'IxraC Adress',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-6',
            ],
            'validation' => 'required|string',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-8 mt-6"></div>',
        ],
        [
            'type' => 'html',
            'html' => '<div id="package_goods"><div class="mt-10 col-lg-12"><h3 class="text-center">Package goods</h3></div>',
        ],
        [
            'type' => 'html',
            'html' => '<div class="row"><div id="type_section" class="col-lg-8">',
        ],
        [
            'type' => 'html',
            'html' => '<div class="row type_item" id="main_type_item">',
        ],
        [
            'type' => 'html',
            'html' => '<div class="col-md-8" id="ase_types_item">',
        ],
        [
            'label' => 'Type',
            'type' => 'select3',
            'name_parent' => 'customs_type_parents[]',
            'name_child' => 'customs_types[]',
            'attribute' => 'name_az',
            'name' => 'type',
            'model' => 'App\Models\CustomsType',
            'allowNull' => true,
        ],
        [
            'type' => 'html',
            'html' => '</div>',
        ],
        [
            'type' => 'html',
            'html' => '<div class="col-lg-1"> <span class="btn btn-danger btn-icon btn_minus" style="margin-top: 20px"><i
                                        class="icon-minus2"></i></span></div>',
        ],
        [
            'type' => 'html',
            'html' => '</div></div>'
        ],
        [
            'type' => 'html',
            'html' => '<div class="col-lg-1"> <span id="add_type" class="btn btn-primary btn-icon" style="margin-top: 20px"><i
                                        class="icon-plus2"></i></span></div>',
        ],
        [
            'type' => 'html',
            'html' => '</div></div>',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10"></div>',
        ],
        [
            //'name'              => 'weight',
            'name' => 'weighT_GOODS',
            'label' => 'Weight',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'required|numeric',
        ],
        [
            'name' => 'weight_type',
            'label' => '&nbsp',
            'type' => 'select_from_array',
            'optionsFromConfig' => 'ase.attributes.weight',
            'wrapperAttributes' => [
                'class' => 'col-md-1',
            ],
            'validation' => 'required|integer',
        ],
        [
            'name' => 'transP_COSTS',
            'label' => 'Delivery Price',
            'type' => 'text',
            'prefix' => '<i class="icon-coin-dollar"></i>',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'required|numeric',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10 clearfix"></div>',
        ],
        [
            'name' => 'quantitY_OF_GOODS',
            'label' => 'Number Items',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'required|numeric',
        ],
        [
            'name' => 'invoyS_PRICE',
            'label' => 'Invoice price',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'required|numeric',
        ],
        [
            'name' => 'shipping_amount_cur',
            'label' => '&nbsp',
            'type' => 'select_from_array',
            'optionsFromConfig' => 'ase.attributes.currencies',
            'wrapperAttributes' => [
                'class' => 'col-md-1',
            ],
            'validation' => 'required|integer',
            'default' => 0,
        ],
    ];

    protected $with = ['package'];

    public function __construct(Request $request)
    {
        /*$id=$request->route()->parameter('id');
        if($id) {
            $this->fields[1]['attributes']=['disabled'=>'disabled'];
            $this->fields[1]['validation']='nullable|string';
        }*/
	$route=$request->route();
	if($route) {
            if ($route->getName() == 'package_carriers.depesh')
                $this->fields = $this->d_fields;
            if ($route->getName() == 'package_carriers.regnumber')
               $this->fields = $this->r_fields;
	}
        return parent::__construct();
    }

    public function packagecanceldepesh($id = null)
    {
        if (Auth::user()->can('customs-depesh')) {
            Artisan::call('canceldepesh', ['package' => 4, 'parcel_id' => $id, 'checkonly' => 0, 'htmlformat' => 1, 'user_id' => auth()->guard('admin')->user()->id]);
            $d_out = Artisan::output();
        } else {
            $d_out = "No permissions";
        }
        return view('admin.depesh', ['d_out' => $d_out]);
    }

    public function packagedepeshcheck($id = null)
    {
        if (Auth::user()->can('customs-check')) {
            Artisan::call('depesh', ['package' => 4, 'parcel_id' => $id, 'checkonly' => 1, 'htmlformat' => 1, 'user_id' => auth()->guard('admin')->user()->id]);
            $d_out = Artisan::output();
        } else {
            $d_out = "No permissions";
        }
        return view('admin.depesh', ['d_out' => $d_out]);
    }


    public function regnumber($id)
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

        $pc = NULL;
        if (method_exists($this, 'editObject')) {
            $pc = $this->editObject($id);

            if (is_array($item) and array_key_exists('error', $item)) {
                Alert::error($item['error']);

                return redirect()->route($this->route . '.index');
            }
        } else {
            //$pc = PackageCarrier::find($id);
            $pc = PackageCarrier::where('id', $id)->first();
        }

        if (!$pc) {
            Alert::error(trans('saysay::crud.not_found'));
            return back();
        }


        if ($this->currentLang) {
            $pc->setDefaultLocale($this->currentLang);
        }

        $item = new stdClass();
        $item->ecoM_REGNUMBER = $pc->ecoM_REGNUMBER;
        return view($this->panelView('form'), compact('item', 'form'));
    }

    public function depesh($id)
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

        $pc = NULL;
        if (method_exists($this, 'editObject')) {
            $pc = $this->editObject($id);

            if (is_array($item) and array_key_exists('error', $item)) {
                Alert::error($item['error']);

                return redirect()->route($this->route . '.index');
            }
        } else {
            //$pc = PackageCarrier::find($id);
            $pc = PackageCarrier::where('id', $id)->first();
        }

        if (!$pc) {
            Alert::error(trans('saysay::crud.not_found'));
            return back();
        }


        if ($this->currentLang) {
            $pc->setDefaultLocale($this->currentLang);
        }

        $cm = new CustomsModel();
        $cm->isCommercial = $pc->is_commercial;
        $cm->trackingNumber = $pc->trackingNumber;
        $cm->pinNumber = $pc->fin;
        $cpost = $cm->get_carrierposts2();
        //echo $cm->trackingNumber."\n";
        //print_r($cpost);
        if (empty($cpost->code) || $cpost->code == 999) {
            sleep(2);
            $cpost = $cm->get_carrierposts2();
        }
        if (empty($cpost->code)) {
            $item = NULL;
            $message = "Cannot get package Error: Empty resposne";
            Session::flash('error', $message);
            return view($this->panelView('form'), compact('item', 'form'));
        }
        if ($cpost->code != 200) {
            $item = NULL;
            $message = "Cannot get package Error: code:" . $cpost->code . " " . $cpost->errorMessage . " " . $cpost->validationError;
            Session::flash('error', $message);
            return view($this->panelView('form'), compact('item', 'form'));
        }
        if (!isset($cpost->data)) {
            $item = NULL;
            $message = "Cannot get package Error: No data";
            Session::flash('error', $message);
            return view($this->panelView('form'), compact('item', 'form'));
        }

        $item = $cpost->data;

        $item->isCommercial = $pc->is_commercial;
        if (empty($item->airwaybill) && !empty($pc->airwaybill_1))
            $item->airwaybill = $pc->airwaybill_1;
        if (empty($item->depesH_NUMBER) && !empty($pc->depesH_NUMBER_1))
            $item->depesH_NUMBER = $pc->depesH_NUMBER_1;
        return view($this->panelView('form'), compact('item', 'form'));
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

        $pc = NULL;
        if (method_exists($this, 'editObject')) {
            $pc = $this->editObject($id);

            if (is_array($item) and array_key_exists('error', $item)) {
                Alert::error($item['error']);

                return redirect()->route($this->route . '.index');
            }
        } else {
            //$pc = PackageCarrier::find($id);
            $pc = PackageCarrier::where('id', $id)->first();
        }

        if (!$pc) {
            Alert::error(trans('saysay::crud.not_found'));

            return back();
        }

        if ($this->currentLang) {
            $pc->setDefaultLocale($this->currentLang);
        }

        $ctrs = DB::select("select c.code,c.id,cc.code_n from countries c left outer join customs_countries cc on lower(case when c.code='uk' then 'gb' when c.code='uae' then 'ae' else  c.code end collate utf8mb4_general_ci)=lower(cc.code_c)");
        $cm_countries = [];
        foreach ($ctrs as $ctr) {
            $cm_countries[$ctr->code_n] = $ctr->id;
        }

        $cm_currencies = [];
        $ccts = DB::select("select * from customs_currencies");
        foreach ($ccts as $cct) {
            $cm_currencies[strtoupper($cct->CODE_C)] = $cct->CODE_N;
        }

        $cm = new CustomsModel();
        $cm->isCommercial = $pc->is_commercial;
        $cm->trackingNumber = $pc->trackingNumber;
        $cm->pinNumber = $pc->fin;
        $cpost = $cm->get_carrierposts2();
        if (empty($cpost->code) || $cpost->code == 999) {
            sleep(2);
            $cpost = $cm->get_carrierposts2();
        }
        //echo $cm->trackingNumber."\n";
        //print_r($cpost);
        $item = NULL;
        if (empty($cpost->code)) {
            $message = "Cannot get package Error: Empty resposne";
            Session::flash('error', $message);
            //return view($this->panelView('form'), compact('item', 'form'));
        }
        if ($cpost->code != 200) {
            $message = "Cannot get package Error: code:" . $cpost->code . " " . $cpost->errorMessage . " " . $cpost->validationError;
            Session::flash('error', $message);
            //return view($this->panelView('form'), compact('item', 'form'));
        }
        if (!isset($cpost->data)) {
            //$message="Cannot get package Error: No data";
            //Session::flash('error', $message);
            //return view($this->panelView('form'), compact('item', 'form'));
        }

        if (isset($cpost->data)) {
            $item = $cpost->data;
            $item->warehouse_id = 0;
            $item->goods = [];
            foreach ($item->goodsList as $key => $type) {
                $ct = CustomsType::find($type->goodS_ID);
		if($ct) {
                    $good = new PackageGood();
                    $good->customs_type_id = $ct->id;
                    $good->customs_type_parent_id = $ct->parent_id;
                    $item->goods[] = $good;
		}
            }
            //print_r($item->currencY_TYPE);
            //return;
            //$item->country_id = $cm_countries[$item->goodS_TRAFFIC_FR];
            foreach (config('ase.attributes.currencies') as $key => $value) {
                if ($cm_currencies[$value] == $item->currencY_TYPE) {
                    $item->shipping_amount_cur = $key;
                    break;
                }
            }
	    if(array_key_exists($item->goodS_TRAFFIC_FR,$cm_countries))
                $item->country_id = $cm_countries[$item->goodS_TRAFFIC_FR];
            $item->isCommercial = $pc->is_commercial;
            if (empty($item->airwaybill) && !empty($pc->airwaybill_1))
                $item->airwaybill = $pc->airwaybill_1;
            if (empty($item->depesH_NUMBER) && !empty($pc->depesH_NUMBER_1))
                $item->depesH_NUMBER = $pc->depesH_NUMBER_1;
        } else {
            $item = $pc;
            $item->idxaL_NAME = $pc->idxal_name;
            $item->ixraC_NAME = $pc->ixrac_name;
            $item->trackinG_NO = $pc->trackingNumber;
        }
        return view($this->panelView('form'), compact('item', 'form'));
    }

    public function r_store(Request $request, $id)
    {
        $ldate = date('Y-m-d H:i:s');
        $ldate = date('Y-m-d H:i:s', strtotime($ldate . ' +7 day'));
        $this->fields = $this->r_fields;
        $this->validate($request, $this->generateValidation('store'));
        $ecoM_REGNUMBER = $request->get('ecoM_REGNUMBER');
        $str = "update package_carriers";
        $str .= " set ecoM_REGNUMBER=?,created_at=?";
        $str .= " where id=?";
        DB::update($str, [
            $ecoM_REGNUMBER, $ldate
            , $id]);
	$pc = PackageCarrier::where('id', $id)->first();
	if($pc && $pc->track_id) {
	   $_track = Track::find($pc->track_id); 
           if($_track) {
              if($_track->status <=7 && !empty($ecoM_REGNUMBER)) {
                   $_track->status=7;
                   $_track->save();
                   (new PackageService())->updateStatus($_track, 7);
              }
           }
	}

        Alert::success(trans('saysay::crud.action_alert', [
            'name' => 'CUSTOMS',
            'key' => 'Reg number',
            'value' => $ecoM_REGNUMBER,
            'action' => 'changed',
        ]));

        return redirect()->route($this->route . '.index', $this->routeParams);
    }

    public function d_store(Request $request, $id)
    {
        $this->fields = $this->d_fields;
        $this->validate($request, $this->generateValidation('store'));
        $airwaybill = $request->get('airwaybill');
        $depesH_NUMBER = $request->get('depesH_NUMBER');

        $str = "update package_carriers";
        $str .= " set depesH_NUMBER_1=?,airwaybill_1=?";
        $str .= " where id=?";

        DB::update($str, [
            $depesH_NUMBER, $airwaybill
            , $id]);

        $ldate = date('Y-m-d H:i:s');
        $cm = new CustomsModel();
        if ($id) {
            //$pc = PackageCarrier::find($id);
            $pc = PackageCarrier::where('id', $id)->first();
            $cm->isCommercial = $pc->is_commercial;
            $cm->trackingNumber = $pc->trackingNumber;
            $cm->fin = $pc->fin;
            $cpost = $cm->get_carrierposts2();
            if (empty($cpost->code) || $cpost->code == 999) {
                sleep(2);
                $cpost = $cm->get_carrierposts2();
            }
            if (empty($cpost->code)) {
                $message = "Cannot get package for depesh Error: Empty resposne";
                Session::flash('error', $message);
                sleep(2);
                return redirect()->back()->withInput($request->all());
            }
            if ($cpost->code != 200) {
                $cm->parse_error($res);
                $message = "Cannot get package for depesh Error: code:" . $cpost->code . " " . $cpost->errorMessage . " " . $cpost->validationError;
                Session::flash('error', $message);
                sleep(2);
                return redirect()->back()->withInput($request->all());
            }
            if ($airwaybill == $cpost->airwaybill && $depesH_NUMBER == $cpost->depesH_NUMBER) {
                $message = 'Airwaybill & Depesh number are the same as in customs';
                Session::flash('error', $message);
                sleep(2);
                return redirect()->back()->withInput($request->all());
            }

            if (!empty($cpost->airwaybill) && !empty($cpost->depesH_NUMBER)) {
                $cm->airwaybill = $airwaybill;
                $cm->depesH_NUMBER = $depesH_NUMBER;
                $res = $cm->update_carriers();
                if (empty($res->code)) {
                    sleep(2);
                    $res = $cm->update_carriers();
                }
                if (!isset($res->code)) {
                    $message = "Cannot change depesh Error: Empty response";
                    $request->session()->flash('error', $message);
                    sleep(2);
                    return redirect()->back()->withInput($request->all());
                }

                if (($res->code != 200) && ($res->code != 400)) {
                    $message = 'Cannot change depesh Error: ' . $cm->errorStr;
                    $request->session()->flash('error', $message);
                    sleep(2);
                    return redirect()->back()->withInput($request->all());
                }
            } else {
                $cpost->airwaybill;
                $regNumber = $cpost->ecoM_REGNUMBER;
                if (empty($regNumber))
                    $regNumber = $pc->ecoM_REGNUMBER;
                if (empty($regNumber)) {
                    $message = 'Package has no declaration in customs (No declaration number)';
                    Session::flash('error', $message);
                    sleep(2);
                    return redirect()->back()->withInput($request->all());
                }

                $cm->regNumber = $regNumber;
                $res = $cm->approvesearch();
                if (empty($res->code)) {
                    sleep(2);
                    $res = $cm->approvesearch();
                }
                if (empty($res->code)) {
                    $message = "Cannot approvesearch Error: Empty resposne";
                    Session::flash('error', $message);
                    sleep(2);
                    return redirect()->back()->withInput($request->all());
                }
                //if($res->code != 200) {
                if (($res->code != 200) && ($res->code != 400)) {
                    $cm->parse_error($res);
                    $message = "Cannot approvesearch Error: code:" . $cpost->code . " " . $cm->errorMessage . " " . $cm->validationError;
                    Session::flash('error', $message);
                    sleep(2);
                    return redirect()->back()->withInput($request->all());
                }

                $res = $cm->addtoboxes();
                if (empty($res->code)) {
                    sleep(2);
                    $res = $cm->addtoboxes();
                }
                if (empty($res->code)) {
                    $message = "Cannot addtoboxes Error: Empty resposne";
                    Session::flash('error', $message);
                    sleep(2);
                    return redirect()->back()->withInput($request->all());
                }
                //if($res->code != 200) {
                if (($res->code != 200) && ($res->code != 400)) {
                    $cm->parse_error($res);
                    $message = "Cannot addtoboxes Error: code:" . $res->code . " " . $cm->errorMessage . " " . $cm->validationError;
                    Session::flash('error', $message);
                    sleep(2);
                    return redirect()->back()->withInput($request->all());
                }

                //$cm->airwaybill=$airwaybill;
                //$cm->depesH_NUMBER=$depesH_NUMBER;
                $cm->airWaybill = $airwaybill;
                $cm->depeshNumber = $depesH_NUMBER;
                $res = $cm->depesh();
                if (empty($res->code)) {
                    sleep(2);
                    $res = $cm->depesh();
                }
                if (empty($res->code)) {
                    $message = "Cannot depesh Error: Empty resposne";
                    Session::flash('error', $message);
                    sleep(2);
                    return redirect()->back()->withInput($request->all());
                }
                if ($res->code != 200) {
                    $cm->parse_error($res);
                    $message = "Cannot depesh Error: code:" . $res->code . " " . $cm->errorMessage . " " . $cm->validationError;
                    Session::flash('error', $message);
                    sleep(2);
                    return redirect()->back()->withInput($request->all());
                }

            }

            $str = "update package_carriers";
            $str .= " set depesH_NUMBER=?,airwaybill=?,depesH_DATE=?";
            $str .= " where id=?";

            DB::update($str, [
                $depesH_NUMBER, $airwaybill, $ldate
                , $id]);
        }

        Alert::success(trans('saysay::crud.action_alert', [
            'name' => 'CUSTOMS',
            'key' => 'Track #',
            'value' => $cm->trackinG_NO,
            'action' => 'depesh',
        ]));

        return redirect()->route($this->route . '.index', $this->routeParams);
    }

    public function c_store(Request $request, $id)
    {

        $this->validate($request, $this->generateValidation('store'));
        $customs_types = $request->get('customs_types');
        foreach ($customs_types as $key => $value) {
            if (empty($value)) {
                return redirect()->back()->withErrors(['type' => [0 => 'TYPE IS REQUIRED']])->withInput($request->all());
            }
        }

        //$packageCarrier = $this->modelObject;

        $ldate = date('Y-m-d H:i:s');
        //echo "Tracking #".$request->get('trackinG_NO');
        //return;
        $cm = new CustomsModel();
        if ($id) {
            //$pc = PackageCarrier::find($id);
            $pc = PackageCarrier::where('id', $id)->first();
            $cm->isCommercial = $pc->is_commercial;
            $cm->trackingNumber = $pc->trackingNumber;
            $cm->fin = $pc->fin;
            $cpost = $cm->get_carrierposts2();
            if (empty($cpost->code) || $cpost->code == 999) {
                sleep(2);
                $cpost = $cm->get_carrierposts2();
            }
            /*if($cpost->code != 200) {
                $message="Cannot get package for delete Error: code:".$cpost->code." ".$cpost->errorMessage." ".$cpost->validationError;
                Session::flash('error', $message);
                //sleep(2);
                    //return redirect()->back()->withInput($request->all());
            }*/
            if (empty($cpost->code) || $cpost->code == 999 || $cpost->code == 200 || $cpost->code == 400) {
                $res = $cm->delete_carriers();
                if (empty($res->code)) {
                    sleep(2);
                    $res = $cm->delete_carriers();
                }
            }
            /*if(!isset($res->code))
            {
                $message="Cannot delete package Error: Empty response";
	        $request->session()->flash('error', $message);
	        sleep(2);
                return redirect()->back()->withInput($request->all());
            }

            if(($res->code != 200) && ($res->code != 400))
            {
                 $cm->parse_error($res);
	         $message='Cannot delete package Error:';
                 if(!empty($cm->errorMessage))
		     $message.="    errorMessage: ".$cm->errorMessage;
	         if(!empty($cm->validationError))
                     $message.="    validationError: ".$cm->validationError;
	         $request->session()->flash('error', $message);
	         sleep(2);
                 return redirect()->back()->withInput($request->all());
	    }*/
            //$cm->trackinG_NO=$pc->trackingNumber;
        }
        $cm->direction = 1;
        $items = DB::select("select c.code,c.id,cc.code_n from countries c left outer join customs_countries cc on lower(case when c.code='uk' then 'gb' when c.code='uae' then 'ae' else  c.code end collate utf8mb4_general_ci)=lower(cc.code_c)");
        $cm_countries = [];
        foreach ($items as $item) {
            $cm_countries[$item->id] = $item->code_n;
        }

        $items = DB::select("select * from customs_currencies");
        foreach ($items as $item) {
            $cm_currencies[strtoupper($item->CODE_C)] = $item->CODE_N;
        }


        // replace empty values with NULL, so that it will work with MySQL strict mode on
        $country_id = $request->get('country_id');
        $idxal_name = $request->get('idxaL_NAME');
        $ixrac_name = $request->get('ixraC_NAME');
        $airwaybill_1 = $request->get('airwaybill');
        $depesH_NUMBER_1 = $request->get('depesH_NUMBER');
        $airwaybill = NULL;
        $depesH_NUMBER = NULL;
        if ($request->get('isCommercial')) {
            $airwaybill = $request->get('airwaybill');
            $depesH_NUMBER = $request->get('depesH_NUMBER');
        }
        foreach ($this->fields as $field) {
            if (!isset($field['name']))
                continue;

            echo $field['name'] . "=" . $request->get($field['name']) . "<br>\n";

            if ($field['name'] == 'type') {
                continue;
            } else if ($field['name'] == 'country_id') {
                $cm->goodS_TRAFFIC_FR = $cm_countries[$request->get('country_id')];
            } else if ($field['name'] == 'documenT_TYPE') {
                $cm->document_type = $request->get('documenT_TYPE');
            } else if ($field['name'] == 'shipping_amount_cur') {
                $cm->currencY_TYPE = $cm_currencies[config('ase.attributes.currencies')[$request->get('shipping_amount_cur')]];
            } else {
                $cm->{$field['name']} = $request->get($field['name']);
            }
        }

        $cm->get_carriers_goods_from_arr($request->get('customs_types'));
        $res = $cm->add_carriers();
        if (empty($res->code)) {
            sleep(2);
            $res = $cm->add_carriers();
        }
        //echo $cm->get_carriers_json_str();
        //return;
        $ldate = date('Y-m-d H:i:s');

        if (!isset($res->code)) {
            $message = "Cannot add carrier to customs Error: Empty response";
            $cm->errorMessage = $message;

            $request->session()->flash('error', $message);
            $cm->updateDB2(NULL, $cm->fin, $cm->trackinG_NO, $ldate, 999, $country_id, $idxal_name, $ixrac_name, $airwaybill, $depesH_NUMBER, $airwaybill_1, $depesH_NUMBER_1);

            return redirect()->back()->withInput($request->all());
        }

        if (($res->code == 400) && isset($res->exception) && is_object($res->exception) && isset($res->exception->status) && $res->exception->status == 'error')
            $res->code = 888;

        $cm->updateDB2(NULL, $cm->fin, $cm->trackinG_NO, $ldate, $res->code, $country_id, $idxal_name, $ixrac_name, $airwaybill, $depesH_NUMBER, $airwaybill_1, $depesH_NUMBER_1);
        if ($res->code != 200) {
            $cm->parse_error($res);
            $message = 'Cannot add carrier to customs Error:';
            if (!empty($cm->errorMessage))
                $message .= "    errorMessage: " . $cm->errorMessage;
            if (!empty($cm->validationError))
                $message .= "    validationError: " . $cm->validationError;
            $request->session()->flash('error', $message);
            sleep(2);
            return redirect()->back()->withInput($request->all());
        }

        Alert::success(trans('saysay::crud.action_alert', [
            'name' => 'CUSTOMS',
            'key' => 'Track #',
            'value' => $cm->trackinG_NO,
            'action' => 'added',
        ]));

        return redirect()->route($this->route . '.index', $this->routeParams);
    }

    public function destroy($id)
    {
        $ldate = date('Y-m-d H:i:s');
        $cm = new CustomsModel();
        $item = NULL;
        if ($id) {
            //$item = PackageCarrier::find($id);
            $item = PackageCarrier::where('id', $id)->first();
            if (!$item)
                return parent::destroy($id);
            $cm->isCommercial = $item->is_commercial;
            $cm->trackingNumber = $item->trackingNumber;
            $cm->fin = $item->fin;
            //sleep(2);
            $cpost = $cm->get_carrierposts2();
            if (empty($cpost->code) || $cpost->code == 999) {
                sleep(2);
                $cpost = $cm->get_carrierposts2();
            }
            if ($cpost->code != 200) {
                $item = NULL;
                $message = "Cannot get package for delete Error: code:" . $cpost->code . " " . $cpost->errorMessage . " " . $cpost->validationError;
                Session::flash('error', $message);
                return redirect()->route($this->route . '.index', $this->routeParams);
                //return redirect()->back()->withInput($request->all());
            }
            $res = $cm->delete_carriers();
            if (empty($res->code)) {
                sleep(2);
                $res = $cm->delete_carriers();
            }
            if (!isset($res->code)) {
                $message = "Cannot delete package Error: Empty response";
                $request->session()->flash('error', $message);
                return redirect()->route($this->route . '.index', $this->routeParams);
                //return redirect()->back()->withInput($request->all());
            }

            if (($res->code != 200) && ($res->code != 400)) {
                $cm->parse_error($res);
                $message = 'Cannot delete package Error:';
                if (!empty($cm->errorMessage))
                    $message .= "    errorMessage: " . $cm->errorMessage;
                if (!empty($cm->validationError))
                    $message .= "    validationError: " . $cm->validationError;
                $request->session()->flash('error', $message);
                return redirect()->route($this->route . '.index', $this->routeParams);
                //return redirect()->back()->withInput($request->all());
            }
        }
        if ($item) {
            DB::update('update package_carriers set deleted_at = ?,updated_at = ? where id=?', [$ldate, $ldate, $id]);
            Alert::success(trans('saysay::crud.action_alert', [
                'name' => 'CUSTOMS',
                'key' => 'Track #',
                'value' => $cm->trackinG_NO,
                'action' => 'deleted',
            ]));

            return redirect()->route($this->route . '.index', $this->routeParams);
        }
        return parent::destroy($id);
    }

    public function store(Request $request)
    {
        $trackingNumber = $request->get('trackinG_NO');
        if (empty($trackingNumber)) {
            $fname = $request->get('fname');
            if ($fname == 'depesh')
                return $this->d_store($request, 0);
            if ($fname == 'regnumber')
                return $this->r_store($request, 0);
            return $this->d_store($request, 0);
        } else {
            return $this->c_store($request, 0);
        }
    }


    public function update(Request $request, $id)
    {
        $trackingNumber = $request->get('trackinG_NO');
        if (empty($trackingNumber)) {
            $fname = $request->get('fname');
            if ($fname == 'depesh')
                return $this->d_store($request, $id);
            if ($fname == 'regnumber')
                return $this->r_store($request, $id);
        } else {
            return $this->c_store($request, $id);
        }
    }


    /**
     * @return LengthAwarePaginator
     */
    public function indexObject()
    {
        $validator = Validator::make(\Request::all(), [
            'q' => 'string',
            'status' => 'integer',
            'ase_status' => 'integer',
            'start_date' => 'date',
            'start_end' => 'date',
        ]);

        if ($validator->failed()) {
            Alert::error('Unexpected variables!');

            return redirect()->route("my.dashboard");
        }

        //$items = PackageCarrier::whereNull('package_id')->latest();
        $items = PackageCarrier::orderBy('id', 'DESC')->latest();

        $ase_status = \Request::get('ase_status');
        if (!$ase_status) {
            $items->whereNull('package_id');
        } else if ($ase_status == 1) {
            $items->whereNotNull('package_id');
        }

        $country_id = \Request::get('country_id');
        if ($country_id) {
            $items->where('country_id', $country_id);
        }

        if (\Request::get('incustoms') != null) {
            $items->incustoms(\Request::get('incustoms'));
        }

        $is_commercial = \Request::get('is_commercial');
        if ($is_commercial) {
            if ($is_commercial == 1)
                $items->where('is_commercial', 1);
            if ($is_commercial == 2)
                $items->where('is_commercial', 0);
        }

        if (\Request::get('q') != null) {
            $q = \Request::get('q');
            $items->where(function ($query) use ($q) {
                $query->orWhere("trackingNumber", "LIKE", "%" . $q . "%")
                    ->orWhere("idxal_name", "LIKE", "%" . $q . "%")
                    ->orWhere("idxal_name", "LIKE", "%" . $q . "%")
                    ->orWhere("depesH_NUMBER", "LIKE", "%" . $q . "%")
                    ->orWhere("ecoM_REGNUMBER", "LIKE", "%" . $q . "%");
            });
        }

        if (\Request::get('start_date') != null) {
            $items->where('created_at', '>=', \Request::get('start_date') . " 00:00:00");
        }
        if (\Request::get('end_date') != null) {
            $items->where('created_at', '<=', \Request::get('end_date') . " 23:59:59");
        }

        $items = $items->paginate($this->limit);

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
}
