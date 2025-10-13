<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Models\Setting;
use Auth;

class SettingController extends Controller
{
    protected $view = [
        'formColumns' => 10,
        'sub_title' => 'Front side settings',
    ];

    protected $can = [
        'create' => false,
        'delete' => false
    ];

    protected $list = [
        'email',
        'phone',
        'facebook',
        'twitter',
    ];

    protected $fields = [
        [
            'name' => 'header_logo',
            'type' => 'image',
            'label' => 'Header logo',
            'asset' => 'uploads/setting/',
            'validation' => 'image',
            'hint' => 'Size 158x42 is good',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-6'
            ]
        ],
        [
            'name' => 'footer_logo',
            'type' => 'image',
            'label' => 'Footer logo',
            'asset' => 'uploads/setting/',
            'validation' => 'image',
            'hint' => 'Size 160x60 is good',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-6'
            ]
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12"><h3 class="text-center">Socials</h3></div>'
        ],
        //Debt
        [
            'name' => 'debt_price_first_day',
            'label' => 'Saxlanc haqqı ilk gün',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-6'
            ]
        ],
        [
            'name' => 'debt_price_day',
            'label' => 'Saxlanc haqqı sonraki gün',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-6'
            ]
        ],
        //Debt
        [
            'name' => 'facebook',
            'label' => 'Facebook',
            'type' => 'url',
            'prefix' => '<i class="icon-facebook"></i>',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-6'
            ]
        ],
        [
            'name' => 'twitter',
            'label' => 'Twitter',
            'type' => 'url',
            'prefix' => '<i class="icon-twitter"></i>',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-6'
            ]
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12"></div>'
        ],
        [
            'name' => 'instagram',
            'label' => 'Instagram',
            'type' => 'url',
            'prefix' => '<i class="icon-instagram"></i>',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-6'
            ]
        ],
        [
            'name' => 'linkedin',
            'label' => 'Linkedin',
            'type' => 'url',
            'prefix' => '<i class="icon-linkedin"></i>',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-6'
            ]
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12"></div>'
        ],
        [
            'name' => 'whatsapp',
            'label' => 'Whatsapp (US,GB)',
            'type' => 'text',
            'prefix' => '<i class="icon-whatsapp"></i>',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-6'
            ]
        ],
        [
            'name' => 'whatsapp2',
            'label' => 'Whatsapp (TR)',
            'type' => 'text',
            'prefix' => '<i class="icon-whatsapp"></i>',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-6'
            ]
        ],

        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12"><h3 class="text-center">Contact</h3></div>'
        ],

        [
            'name' => 'address',
            'type' => 'textarea',
            'label' => 'Address'
        ],
        [
            'name' => 'location',
            'type' => 'text',
            'label' => 'Map location',
            'prefix' => '<i class="icon-location4"></i>'
        ],
        [
            'name' => 'email',
            'type' => 'email',
            'label' => 'Email',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-6'
            ]
        ],
        [
            'name' => 'phone',
            'type' => 'text',
            'label' => 'Phone',
            'prefix' => '<i class="icon-mobile"></i>',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-6'
            ]
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12"><h3 class="text-center">Covers (1900x480)</h3></div>'
        ],
        [
            'name' => 'about_cover',
            'type' => 'image',
            'asset' => 'uploads/setting/',
            'label' => 'About us',
            'validation' => 'image',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-4'
            ]
        ],
        [
            'name' => 'shop_cover',
            'type' => 'image',
            'label' => 'Shop',
            'asset' => 'uploads/setting/',
            'validation' => 'image',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-4'
            ]
        ],
        [
            'name' => 'tariffs_cover',
            'type' => 'image',
            'label' => 'Tariffs',
            'asset' => 'uploads/setting/',
            'validation' => 'image',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-4'
            ]
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12"></div>'
        ],
        [
            'name' => 'calculator_cover',
            'type' => 'image',
            'label' => 'Calculator page',
            'validation' => 'image',
            'asset' => 'uploads/setting/',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-4'
            ]
        ],
        [
            'name' => 'faq_cover',
            'type' => 'image',
            'label' => 'FAQ page',
            'validation' => 'image',
            'asset' => 'uploads/setting/',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-4'
            ]
        ],
        [
            'name' => 'contact_cover',
            'type' => 'image',
            'label' => 'Contact page',
            'validation' => 'image',
            'asset' => 'uploads/setting/',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-4'
            ]
        ],
        [
            'name' => 'user_cover',
            'type' => 'image',
            'label' => 'User panel cover',
            'validation' => 'image',
            'asset' => 'uploads/setting/',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-4'
            ]
        ],
        [
            'name' => 'news_cover',
            'type' => 'image',
            'label' => 'News cover',
            'validation' => 'image',
            'asset' => 'uploads/setting/',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-4'
            ]
        ],
    ];

    public function editObject($id)
    {
        $item = Setting::find($id);

        if(!Auth::guard('admin')->user()->can('in-customs-debt-edit')) {
            //eger in-customs-debt-edit rolu icazesi yoxdursa $field 9,10 elemntleri gizledirik
            unset($this->fields[9]);
            unset($this->fields[10]);
        }

        \View::share([
            'fields' => $this->fields,
        ]);

        return $item;
    }
}
