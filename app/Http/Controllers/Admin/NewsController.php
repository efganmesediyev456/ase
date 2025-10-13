<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Models\Page;

class NewsController extends Controller
{
    protected $modelName = 'Page';

    protected $route = 'news';

    protected $view = [
        'name' => 'News',
        'formColumns' => 10,
        'sub_title' => 'New notifications and news',
    ];

    protected $list = [
        'image' => [
            'type' => 'image',
        ],
        'slug',
        'title',
    ];

    protected $notificationKey = 'title';

    protected $fields = [
        [
            'name' => 'type',
            'type' => 'hidden',
            'default' => 1,
        ],
        [
            'name' => 'image',
            'type' => 'image',
            'label' => 'Image',
            'validation' => 'nullable|image',
        ],
        [
            'name' => 'slug',
            'label' => 'Article Slug (URL)',
            'type' => 'text',
            'prefix' => '<i class="icon-unlink"></i>',
            'hint' => 'Will be automatically generated from your title, if left empty.',
            'validation' => 'nullable|string',
        ],
        [
            'name' => 'title',
            'label' => 'Title',
            'type' => 'text',
            'validation' => 'required|string|min:3',
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
            'validation' => 'required|string',
        ],
        [
            'name' => 'meta_keywords',
            'label' => 'Meta keywords',
            'type' => 'tag',
            'hint' => 'Meta keywords for SEO',
            'validation' => 'required|string',
        ],
    ];

    public function index()
    {

        $items = Page::news()->latest()->paginate($this->limit);

        return view($this->panelView('list'), compact('items'));
    }
}
