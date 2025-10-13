<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Models\GiftCard;

class GiftCardController extends Controller
{
    protected $notificationKey = 'card_number';

    protected $route = 'gift_cards';

    protected $can = ['delete'];

    protected $view = [
        'name' => 'Gift Cards',
        'sub_title' => 'Gift cards for using custom payment',
        'listColumns' => 10,
        'formColumns' => 6,
    ];

    protected $extraActions = [

        [
            'route' => 'gift_cards.label',
            'key' => 'id',
            'label' => 'Label',
            'icon' => 'windows2',
            'color' => 'success',
            'target' => '_blank',
        ],
    ];

    protected $with = ['user'];

    protected $list = [
        'card_number',
        'user' => [
            'type' => 'custom.user',
            'label' => 'User',
        ],
        'amount',
        'status',
        'created_at' => [
            'label' => 'Created',
        ],
    ];

    protected $fields = [
        [
            'label' => 'User',
            'type' => 'select2',
            'name' => 'user_id',
            'attribute' => 'full_name,customer_id',
            'model' => 'App\Models\User',
            'wrapperAttributes' => [
                'class' => 'col-md-6',
            ],
            'validation' => 'nullable|integer',
            'allowNull' => true,
        ],
        [
            'name' => 'amount',
            'label' => 'Amount (AZN)',
            'type' => 'text',
            'validation' => 'required|numeric',
            'wrapperAttributes' => [
                'class' => 'col-md-6',
            ],
        ],
    ];

    public function label($id)
    {
        $item = GiftCard::with(['user'])->find($id);
        if (!$item) {
            abort(404);
        }

        return view('admin.widgets.gift_card', compact('item'));
    }
}
