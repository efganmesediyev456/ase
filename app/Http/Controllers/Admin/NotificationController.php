<?php

namespace App\Http\Controllers\Admin;

use Alert;
use App\Http\Requests;
use App\Models\NotificationQueue;
use DB;

class NotificationController extends Controller
{
    protected $modelName = 'NotificationQueue';

    protected $can = [
        'create' => false,
        'delete' => false,
        'update' => false,
    ];

    protected $view = [
        'sub_title' => 'Notifications',
        'search'    => [
            [
                'name'              => 'user',
                'type'              => 'text',
                'attributes'        => ['placeholder' => 'User...'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-3',
                ],
            ],
            [
                'name'              => 'q',
                'type'              => 'text',
                'attributes'        => ['placeholder' => 'Search...'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-3',
                ],
            ],
            [
                'name' => 'tl',
                'type' => 'textarea',
                'attributes' => ['placeholder' => 'Package # List...'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-3',
                ],
            ],
            [
                'name'              => 'to',
                'type'              => 'text',
                'attributes'        => ['placeholder' => 'Number...'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-3',
                ],
            ],
            [
                'name'              => 'type',
                'type'              => 'select_from_array',
                'options'           => [
                    'SMS'   => 'SMS',
                    'EMAIL' => 'EMAIL',
                    'MOBILE'=>'MOBILE',
                    'WHATSAPP'=>'WHATSAPP'
                ],
                'wrapperAttributes' => [
                    'class' => 'col-lg-3',
                ],
                'allowNull'         => 'Source',
            ],
            [
                'name' => 'created_at',
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

    protected $list = [
        'user' => [
            'type'  => 'custom.user',
            'label' => 'User',
        ],

        'type',
        'to',
        'subject',
        'sent' => [
            'type' => 'notification-boolean',
        ],
        'created_at',
        'message' => [
            'label' => 'Message',
            'type' => 'custom.full_text',
        ],
        'error_message',
    ];

    public function indexObject()
    {
        $validator = \Validator::make(\Request::all(), [
            'user' => 'string',
            'q'    => 'string',
        ]);

        if ($validator->failed()) {
            \Alert::error('Unexpected variables!');

            return redirect()->route("my.dashboard");
        }

        $items = NotificationQueue::with('user')->latest();

        if (\Request::get('q') != null) {
            $q = str_replace('"', '', \Request::get('q'));
            $items->where('content', "like", "%" . $q . "%");
        }

        if (\Request::get('tl') != null) {
            $tracking_codes = preg_split("/[;:,\s]+/", trim(\Request::get('tl')));

            $items->where(function ($query) use ($tracking_codes) {
                foreach ($tracking_codes as $code) {
                    $query->orWhere('content', 'like', "%" . $code . "%");
                }
            });
        }


        if (\Request::get('to') != null) {
            $q = str_replace('"', '', \Request::get('to'));
            $phone = str_replace('+','',$q);
            if(preg_match('/9940/', $phone))
            {
                $number = substr($phone, 6);
            } elseif(preg_match('/994/', $phone) && !preg_match('/9940/', $phone)) {
                $number = substr($phone, 5);
            }else{
                $number = substr($phone, 3);
            }
            $items->where('to', "like", "%" . $number . "%");
        }

        if (\Request::get('type') != null) {
            $items->where('type', \Request::get('type'));
        }

        if (\Request::get('user') != null) {
            $q = str_replace('"', '', \Request::get('user'));
            $items->where(function ($query) use ($q) {
                $query->whereHas('user', function (
                    $query
                ) use ($q) {
                    $query->where('customer_id', 'LIKE', '%' . $q . '%')->orWhere('passport', 'LIKE', '%' . $q . '%')->orWhere('fin', 'LIKE', '%' . $q . '%')->orWhere('phone', 'LIKE', '%' . $q . '%')->orWhere('email', 'LIKE', '%' . $q . '%')->orWhere(DB::raw('concat(trim(name)," ",trim(surname))'), 'LIKE', "%" . $q . "%")->orWhereHas('dealer', function (
                        $query
                    ) use ($q) {
                        $query->where('customer_id', 'LIKE', '%' . $q . '%')->orWhere('passport', 'LIKE', '%' . $q . '%')->orWhere('fin', 'LIKE', '%' . $q . '%')->orWhere('phone', 'LIKE', '%' . $q . '%')->orWhere('email', 'LIKE', '%' . $q . '%')->orWhere(DB::raw('concat(trim(name)," ",trim(surname))'), 'LIKE', "%" . $q . "%");
                    });
                });
            });
        }

        return $items->paginate($this->limit);
    }
}
