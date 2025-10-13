<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Models\CustomsModel;
use Auth;
use DB;
use View;
use function request;

class AirPackageController extends Controller
{
    protected $list = [
        'num' => [
            'label' => '#'
        ],
        'trackinG_NO' => [
            'label' => 'trackinG_NO'
        ],
        'weighT_OF_GOODS' => [
            'label' => 'weighT_OF_GOODS',
        ],
        'airwaybill' => [
            'label' => 'airwaybill',
        ],
        'depesH_NUMBER' => [
            'label' => 'depesH_NUMBER',
        ],
        'depesH_DATE' => [
            'label' => 'depesH_DATE',
        ],
    ];

    protected $view = [
        'sum' => [
            [
                'key' => 'weighT_OF_GOODS',
                'skip' => 2,
                'add' => "kg",
            ],
        ]
    ];

    protected $modelName = 'AirPackage';
    protected $airPackages = [];

    public function __construct()
    {
        parent::__construct();
        View::share('_list', $this->list);
    }

    /*    public function index()
        {
        $items=[];
        return view($this->panelView('list'), compact('items'));
    }*/

    public function index()
    {
        $alertText = '';
        $alertType = 'danger';//success/warning,danger
        $airwaybill = request()->get('airwaybill');
        $depesH_NUMBER = request()->get('depesH_NUMBER');
        $airPackages = [];
        if (!Auth::user()->can('customs-check')) {
            $alertText = 'No permissions to Read Packages';
            $alertType = 'danger';
            return view('admin.air_packages', compact('airwaybill', 'depesH_NUMBER', 'airPackages', 'alertText', 'alertType'));
        }
        $alertText = $this->getAirPackages($airwaybill, $depesH_NUMBER);
        $airPackages = $this->airPackages;
        $cnt = 0;
        foreach ($airPackages as $airPackage) {
            $cnt++;
            $airPackage->num = $cnt;
        }

        return view('admin.air_packages', compact('airwaybill', 'depesH_NUMBER', 'airPackages', 'alertText', 'alertType'));
    }


    public function getAirPackages($airwaybill, $depesH_NUMBER)
    {
        $this->airPackages = [];
        if (empty($airwaybill))
            return "";
        $cm = new CustomsModel();
        $cm->airWaybill = $airwaybill;
        $cm->depeshNumber = $depesH_NUMBER;
        $arr = $cm->air();
        if ($cm->errorMessage)
            return "Error: " . $cm->errorMessage . " " . $cm->validationError;
        if ($arr)
            $this->airPackages = $arr;
        return '';
    }
}
