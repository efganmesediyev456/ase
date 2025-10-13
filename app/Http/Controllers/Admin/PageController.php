<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Models\Page;

class PageController extends Controller
{
    protected $notificationKey = 'title';

    protected $view = [
        'formColumns' => 10,
        'sub_title' => 'Custom pages for front side',
    ];

    protected $list = [
        'order_num' => [
            'label' => 'Order',
        ],
        'keyword',
        'slug',
        'title',
    ];

    protected $fields = [
        [
            'name' => 'order_num',
            'label' => 'Order',
            'type' => 'text',
            'validation' => 'numeric',
        ],
        [
            'name' => 'keyword',
            'label' => 'Key',
            'type' => 'text',
            'validation' => 'required|string|min:3|alpha_dash|unique:pages,keyword',
            'hint' => 'Only for backend-side'
        ],
        [
            'name' => 'title',
            'label' => 'Title',
            'type' => 'text',
            'validation' => 'required|string|min:3',
        ],
        [
            'name' => 'slug',
            'label' => 'Page Slug (URL)',
            'type' => 'text',
            'prefix' => '<i class="icon-unlink"></i>',
            'hint' => 'Will be automatically generated from your title, if left empty.',
            'validation' => 'nullable|string',
        ],
        [
            'name' => 'content',
            'label' => 'Content',
            'type' => 'summernote',
            'validation' => 'required|string|min:10',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group mt-10 col-lg-12"><h3 class="text-center">SEO</h3></div>'
        ],
        [
            'name' => 'meta_title',
            'label' => 'Meta title',
            'type' => 'text',
            'hint' => 'Meta title for SEO',
            'validation' => 'nullable|string',
        ],
        [
            'name' => 'meta_description',
            'label' => 'Meta Description',
            'type' => 'textarea',
            'attributes' => [
                'rows' => 8,
            ],
            'hint' => 'Meta Description for SEO',
            'validation' => 'nullable|string',
        ],
        [
            'name' => 'meta_keywords',
            'label' => 'Meta keywords',
            'type' => 'tag',
            'hint' => 'Meta keywords for SEO',
            'validation' => 'nullable|string',
        ],
    ];

    public function index()
    {
        $items = Page::self()->latest()->paginate($this->limit);

        return view($this->panelView('list'), compact('items'));
    }
}
