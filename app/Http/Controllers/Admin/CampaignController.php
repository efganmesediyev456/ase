<?php

namespace App\Http\Controllers\Admin;

use Alert;
use App\Exports\Admin\UsersExport;
use App\Models\Campaign;
use App\Models\Package;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Excel;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    protected $notificationKey = 'title';

    protected $view = [
        'sub_title' => 'Create new campaign',
        'listColumns' => 10,
        'formColumns' => 12,
    ];

    protected $list = [
        'title',
        'sent_status',
        'matched',
        'type' => [
            'label' => 'By'
        ],
        'created_at' => [
            'label' => 'Date',
        ],
    ];

    public function __construct()
    {

        $all = User::count();
        $sms_verified = User::where('sms_verification_status', 1)->count(); // verified
        $email_verified = User::where('verified', 1)->count(); // verified
        $mobile_users = User::distinct()->join('user_devices', 'users.id', '=', 'user_devices.user_id')->select('users.*', 'user_devices.fcm_token')->whereNotNull('fcm_token')->count(); // mobile
        $matchedText = 'Total matched : <b><span id="matched" data-route="' . route('campaigns.search') . '">' . $all . '</span> users</b>.  Click <a href="#!" id="export_matched">here</a> to download matched users';

        $this->fields = [
            [
                'type' => 'html',
                'html' => '<div class="row"><div class="col-lg-12"> <div class="alert alert-success">' . $matchedText . '</div> </div></div>',
            ],
            [
                'type' => 'html',
                'html' => '<div class="row"><div class="col-lg-5"> <h6>Include</h6>',
            ],
            [
                'name' => 'all',
                'label' => 'All (' . ($all) . ' users)',
                'type' => 'checkbox',
                'attributes' => [
                    'checked' => 'checked',
                ],
                'wrapperAttributes' => [
                    'style' => 'margin-bottom: -5px;',
                ],
            ],
            [
                'name' => 'phone_verified',
                'label' => 'Phone number verified (' . ($sms_verified) . ' users)',
                'type' => 'checkbox',
                'wrapperAttributes' => [
                    'style' => 'margin-bottom: -5px;',
                ],
            ],
            [
                'name' => 'email_verified',
                'label' => 'Email verified (' . ($email_verified) . ' users)',
                'type' => 'checkbox',
                'wrapperAttributes' => [
                    'style' => 'margin-bottom: -5px;',
                ],
            ],
            [
                'name' => 'mobile_users',
                'label' => 'Mobile users (' . ($mobile_users) . ' users)',
                'type' => 'checkbox',
                'wrapperAttributes' => [
                    'style' => 'margin-bottom: -5px;',
                ],
            ],
            [
                'name' => 'active',
                'label_pre' => 'Has an activity at the last  ',
                'label_post' => ' days ',
                'type' => 'checkbox_with_input',
                'wrapperAttributes' => [
                    'style' => 'margin-bottom: -5px;',
                ],
                'input' => [
                    'name' => 'active_input',
                    'default' => 30,
                    'attributes' => [
                        'class' => 'form-control display-inline-block text-center',
                        'style' => 'width: 35px;',
                    ],
                ],
            ],
            [
                'name' => 'no_active',
                'label_pre' => 'Has NO activity more than ',
                'label_post' => ' days',
                'type' => 'checkbox_with_input',
                'wrapperAttributes' => [
                    'style' => 'margin-bottom: -5px;',
                ],
                'input' => [
                    'name' => 'no_active_input',
                    'default' => 30,
                    'attributes' => [
                        'class' => 'form-control display-inline-block text-center',
                        'style' => 'width: 35px;',
                    ],
                ],
            ],
            [
                'name' => 'monthly_package',
                'label_pre' => 'Has minimum ',
                'label_post' => ' packages within 30 days',
                'type' => 'checkbox_with_input',
                'wrapperAttributes' => [
                    'style' => 'margin-bottom: -5px;',
                ],
                'input' => [
                    'name' => 'monthly_package_input',
                    'default' => 2,
                    'attributes' => [
                        'class' => 'form-control display-inline-block text-center',
                        'style' => 'width: 35px;',
                    ],
                ],
            ],
            [
                'name' => 'monthly_spend',
                'label_pre' => 'Customer spend more than ',
                'label_post' => '$ within 30 days',
                'type' => 'checkbox_with_input',
                'input' => [
                    'name' => 'monthly_spend_input',
                    'default' => 50,
                    'attributes' => [
                        'class' => 'form-control display-inline-block text-center',
                        'style' => 'width: 35px;',
                    ],
                ],
            ],
            [
                'type' => 'html',
                'html' => '</div><div class="col-lg-2"',
            ],

            [
                'label' => '',
                'name' => 'condition',
                'type' => 'select2_from_array',
                'validation' => 'required',
                'options' => [
                    'or' => 'OR',
                    'and' => 'AND',
                ],
            ],
            [
                'type' => 'html',
                'html' => '</div><div class="col-lg-5"> <h6 style="margin-bottom: 40px">Exclude</h6>',
            ],
            [
                'name' => 'exclude_phone_verified',
                'label' => 'Phone number verified (' . ($sms_verified) . ' users)',
                'type' => 'checkbox',
                'wrapperAttributes' => [
                    'style' => 'margin-bottom: -5px;',
                ],
            ],
            [
                'name' => 'exclude_email_verified',
                'label' => 'Email verified (' . ($email_verified) . ' users)',
                'type' => 'checkbox',
                'wrapperAttributes' => [
                    'style' => 'margin-bottom: -5px;',
                ],
            ],
            [
                'name' => 'exclude_mobile_users',
                'label' => 'Mobile users (' . ($mobile_users) . ' users)',
                'type' => 'checkbox',
                'wrapperAttributes' => [
                    'style' => 'margin-bottom: -5px;',
                ],
            ],
            [
                'name' => 'exclude_active',
                'label_pre' => 'Has an activity at the last  ',
                'label_post' => ' days',
                'type' => 'checkbox_with_input',
                'wrapperAttributes' => [
                    'style' => 'margin-bottom: -5px;',
                ],
                'input' => [
                    'name' => 'exclude_active_input',
                    'default' => 30,
                    'attributes' => [
                        'class' => 'form-control display-inline-block text-center',
                        'style' => 'width: 35px;',
                    ],
                ],
            ],
            [
                'name' => 'exclude_no_active',
                'label_pre' => 'Has NO activity more than ',
                'label_post' => ' days',
                'type' => 'checkbox_with_input',
                'wrapperAttributes' => [
                    'style' => 'margin-bottom: -5px;',
                ],
                'input' => [
                    'name' => 'exclude_no_active_input',
                    'default' => 30,
                    'attributes' => [
                        'class' => 'form-control display-inline-block text-center',
                        'style' => 'width: 35px;',
                    ],
                ],
            ],
            [
                'name' => 'exclude_monthly_package',
                'label_pre' => 'Has minimum ',
                'label_post' => ' packages within 30 days',
                'type' => 'checkbox_with_input',
                'wrapperAttributes' => [
                    'style' => 'margin-bottom: -5px;',
                ],
                'input' => [
                    'name' => 'exclude_monthly_package_input',
                    'default' => 2,
                    'attributes' => [
                        'class' => 'form-control display-inline-block text-center',
                        'style' => 'width: 35px;',
                    ],
                ],
            ],
            [
                'name' => 'exclude_monthly_spend',
                'label_pre' => 'Customer spend more than ',
                'label_post' => '$ within 30 days',
                'type' => 'checkbox_with_input',
                'input' => [
                    'name' => 'exclude_monthly_spend_input',
                    'default' => 50,
                    'attributes' => [
                        'class' => 'form-control display-inline-block text-center',
                        'style' => 'width: 35px;',
                    ],
                ],
            ],
            [
                'type' => 'html',
                'html' => '</div></div><div class="form-group col-lg-12 mt-10"><br/></div>',
            ],
            [
                'name' => 'title',
                'label' => 'Title',
                'type' => 'text',
                'validation' => 'required|string|min:3',
                'wrapperAttributes' => [
                    'class' => ' col-md-7',
                ],
            ],
            [
                'label' => 'By',
                'name' => 'by',
                'type' => 'select2_from_array',
                'validation' => 'required',
                'options' => [
                    'sms' => 'SMS',
                    'email' => 'Email',
                    'mobile' => 'Mobile',
                ],
                'wrapperAttributes' => [
                    'class' => ' col-md-2',
                    'id' => 'by',
                ],
            ],
            [
                'label' => 'Send after',
                'name' => 'send_after',
                'type' => 'select2_from_array',
                'validation' => 'required',
                'options' => [
                    '0' => 'Now',
                    '1' => '1 hour',
                    '6' => '6 hours',
                    '8' => '8 hours',
                    '12' => '12 hours',
                    '24' => '24 hours',
                ],
                'wrapperAttributes' => [
                    'class' => ' col-md-3',
                ],
            ],
            [
                'type' => 'html',
                'html' => '<div class="form-group col-lg-12 mt-10"><br/></div>',
            ],
            [
                'name' => 'content_sms',
                'label' => 'Content',
                'type' => 'textarea',
                'validation' => 'required|string|min:10',
                'attributes' => [
                    'rows' => 6,
                ],
                'wrapperAttributes' => [
                    'class' => ' col-md-9 campaign_content',
                    'id' => 'sms_content',
                ],
            ],
            [
                'name' => 'content_email',
                'label' => 'Content',
                'type' => 'summernote',
                'validation' => 'required|string|min:10',
                'attributes' => [
                    'rows' => 6,
                ],
                'wrapperAttributes' => [
                    'class' => ' col-md-9 campaign_content',
                    'style' => 'display:none',
                    'id' => 'email_content',
                ],
            ],
            [
                'name' => 'content_mobile',
                'label' => 'Content',
                'type' => 'textarea',
                'validation' => 'required|string|min:10',
                'attributes' => [
                    'rows' => 6,
                ],
                'wrapperAttributes' => [
                    'class' => ' col-md-9 campaign_content',
                    'id' => 'mobile_content',
                ],
            ],
            [
                'type' => 'html',
                'html' => '<div class="col-md-3">
                                <h6>Variables you can use</h6>
                                <ul>
                                    <li><b>{name}</b> : User`s fullname</li>
                                    <li><b>{phone}</b> : Phone number</li>
                                    <li><b>{email}</b> : Email</li>
                                    <li><b>{code}</b> : ASE code</li>
                                    <li><b>{passport}</b> : Passport</li>
                                    <li><b>{city}</b> : City</li>
                                    <li><b>:filial_name</b> : User`s filial name</li>
                                    <li><b>:filial_address</b> : User`s filial address</li>
                                </ul>
                            </div>',
            ],
        ];

        parent::__construct();
    }

    public function store(Request $request)
    {
        if (!$this->can['create']) {
            return abort(403);
        }

        $this->validate($request, [
            'title' => 'required',

            'active_input' => 'required|int',
            'no_active_input' => 'required|int',
            'monthly_package_input' => 'required|int',
            'monthly_spend_input' => 'required|int',

            'exclude_active_input' => 'required|int',
            'exclude_no_active_input' => 'required|int',
            'exclude_monthly_package_input' => 'required|int',
            'exclude_monthly_spend_input' => 'required|int',

            'send_after' => 'required|int',
            'by' => 'required|in:sms,email,mobile',
        ]);

        $includes = $this->getUsers();

        if (empty($includes)) {
            Alert::error('No any user matched, please select right options');

            return redirect()->back()->withInput(\request()->all());
        }

        $by = strtoupper(\request()->get('by'));
        $item = new Campaign();
        $item->title = \request()->get('title');
        $item->filtering = \GuzzleHttp\json_encode(\request()->all());
        $item->send_after = \request()->get('send_after');
        //$item->content = $by == 'SMS' ? strip_tags(\request()->get('content_sms') ): \request()->get('content_email');
        $item->content = '';
        if ($by == 'SMS')
            $item->content = strip_tags(\request()->get('content_sms'));
        if ($by == 'EMAIL')
            $item->content = strip_tags(\request()->get('content_email'));
        if ($by == 'MOBILE')
            $item->content = strip_tags(\request()->get('content_mobile'));
        $item->type = $by;
        $item->matched = count($includes);
        $item->users = implode(",", $includes);
        $item->save();

        Alert::success(trans('saysay::crud.action_alert', [
            'name' => $this->modelName,
            'key' => clearKey($this->notificationKey),
            'value' => $item->{$this->notificationKey},
            'action' => 'created',
        ]));

        return $this->createRedirection ? redirect()->route($this->createRedirection, $item->id) : redirect()->route($this->route . '.index', $this->routeParams);
    }

    public function getUsers()
    {
        $all = User::pluck('id')->all();
        $exclude_phone_verified = $phone_verified = User::where('sms_verification_status', 1)->pluck('id')->all(); // verified
        $exclude_email_verified = $email_verified = User::where('verified', 1)->pluck('id')->all(); // verified
        $exclude_mobile_users = $mobile_users = User::distinct()->join('user_devices', 'users.id', '=', 'user_devices.user_id')->select('users.*', 'user_devices.fcm_token')->whereNotNull('fcm_token')->pluck('id')->all(); // mobile

        /* Excludes */
        $exclude_active = array_unique(Package::whereNotNull('user_id')->where('created_at', '>=', Carbon::now()->subDays(\request()->get('exclude_active_input', 30)))->pluck('user_id')->all()); // has an activity more than 30 days

        $ep_last30DaysActiveUserIDs = array_unique(Package::whereNotNull('user_id')->where('created_at', '>=', Carbon::now()->subDays(\request()->get('exclude_no_active_input', 30)))->pluck('user_id')->all()); // has an activity more than 30 days
        $exclude_no_active = User::whereNotIn('id', $ep_last30DaysActiveUserIDs)->pluck('id')->all(); // has no activity more than 30 days

        $exclude_monthly_package = DB::table('packages')->select('user_id', DB::raw('count(id) as cc'))->where('created_at', '>=', Carbon::now()->subDays(30))->having('cc', '>=', \request()->get('exclude_monthly_package_input', 2))->groupBy('user_id')->pluck('user_id')->all(); // Monthly packages more than 2

        $e_spendMoreThan50 = [];  // Customer spend more than 50$ within 30

        $e_minSpend = \request()->get('exclude_monthly_spend_input');
        $packages = Package::where('created_at', '>=', Carbon::now()->subDays(30))->get();
        foreach ($packages as $package) {

            if ($package->delivery_price >= $e_minSpend) {
                $e_spendMoreThan50[] = $package->user_id;
            }
        }
        $exclude_monthly_spend = array_unique($e_spendMoreThan50);

        /* Includes */
        $active = array_unique(Package::whereNotNull('user_id')->where('created_at', '>=', Carbon::now()->subDays(\request()->get('active_input')))->pluck('user_id')->all()); // has an activity more than 30 days

        // Non active
        $p_last30DaysActiveUserIDs = array_unique(Package::whereNotNull('user_id')->where('created_at', '>=', Carbon::now()->subDays(\request()->get('no_active_input')))->pluck('user_id')->all()); // has an activity more than 30 days
        $no_active = User::whereNotIn('id', $p_last30DaysActiveUserIDs)->pluck('id')->all(); // has no activity more than 30 days

        $monthly_package = DB::table('packages')->select('user_id', DB::raw('count(id) as cc'))->where('created_at', '>=', Carbon::now()->subDays(30))->having('cc', '>=', \request()->get('monthly_package_input', 50))->groupBy('user_id')->pluck('user_id')->all(); // Monthly packages more than 2

        // Monthly Spend
        $spendMoreThan50 = [];
        $minSpend = \request()->get('monthly_spend_input');
        $packages = Package::where('created_at', '>=', Carbon::now()->subDays(30))->get();
        foreach ($packages as $package) {

            if ($package->delivery_price >= $minSpend) {
                $spendMoreThan50[] = $package->user_id;
            }
        }
        $monthly_spend = array_unique($spendMoreThan50);

        $condition = \request()->get('condition') == 'or' ? true : false;

        $includes = [];
        $excludes = [];
        $init = true;

        $includeKeys = [
            'all',
            'phone_verified',
            'email_verified',
            'mobile_users',
            'active',
            'no_active',
            'monthly_package',
            'monthly_spend',
        ];

        foreach ($includeKeys as $key) {
            if (\request()->get($key) == '1') {
                if ($init || $condition) {
                    $includes = array_merge($includes, ${$key});
                    $init = false;
                } else {
                    $includes = array_intersect($includes, ${$key});
                }
            }
        }

        $includes = array_unique($includes);

        $excludeKeys = [
            'exclude_phone_verified',
            'exclude_email_verified',
            'exclude_mobile_users',
            'exclude_active',
            'exclude_no_active',
            'exclude_monthly_package',
            'exclude_monthly_spend',
        ];
        $init = true;
        foreach ($excludeKeys as $key) {
            if (\request()->get($key) == '1') {
                if ($init || $condition) {
                    $excludes = array_merge($excludes, ${$key});
                    $init = false;
                } else {
                    $excludes = array_intersect($excludes, ${$key});
                }
            }
        }

        $excludes = array_unique($excludes);

        if (!empty($includes)) {
            $includes = array_diff($includes, $excludes);
        }

        return $includes;
    }

    public function search()
    {
        $ids = $this->getUsers();
        if (\request()->get('export') != null && !empty($ids)) {
            $items = User::whereIn('id', $ids)->get();

            return Excel::download(new UsersExport($items), 'users_' . uniqid() . '.xlsx');
        }

        return count($ids);
    }
}
