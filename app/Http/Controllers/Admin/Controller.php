<?php

namespace App\Http\Controllers\Admin;

use Alert;
use App\Services\Package\PackageService;
use Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Response;
use Route;
use View;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Limit for pagination on list view
     *
     * @var int
     */
    protected $limit = 25;

    /**
     * Template path for crud
     *
     * @var string
     */
    protected $templateDir = 'vendor.saysay.crud';

    /**
     * Model Name
     *
     * @var
     */
    protected $modelName;

    /**
     * Full Model Path
     *
     * @var
     */
    private $model;

    /**
     * @var
     */
    private $modelObject;

    /**
     * Route name
     *
     * @var
     */
    protected $route;

    /**
     * Route params
     *
     * @var
     */
    protected $routeParams = [];

    /**
     * Redirect After Create
     *
     * @var
     */
    protected $createRedirection = null;

    /**
     * Controller Action name
     *
     * @var
     */
    protected $action;

    /**
     * Model translatable property
     *
     * @var bool
     */
    protected $translatable = false;

    /**
     * Current choose language
     *
     * @var bool
     */
    protected $currentLang = false;

    /**
     * View attributes
     *
     * @var array
     */
    protected $view = [];

    /**
     * List columns
     *
     * @var
     */
    protected $list;

    /**
     * @var array
     */
    protected $extraActions = [];

    /**
     * @var array
     */
    protected $extraButtons = [];

    /**
     * Form fields
     *
     * @var array
     */
    protected $fields = [];

    /**
     * Permission controller
     *
     * @var array
     */
    protected $can = [];

    /**
     * Model with scope for list view
     *
     * @var null
     */
    protected $with = null;

    /**
     * Model withCount scope for list view
     *
     * @var null
     */
    protected $withCount = null;

    /**
     * Main notification key alerts
     *
     * @var string
     */
    protected $notificationKey = 'name';

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        $this->setParamsByController();

        /* Set Default Template Directory */
        $this->setViewPath();

        /* Set Translatable */
        $this->setTranslatable();

        $this->mergeCanAttribute();
        $this->mergeViewAttribute();

        if (empty($this->limit))
            $this->limit = 25;
        $this->limit = \Request::get('limit') != null ? \Request::get('limit') : $this->limit;

        View::share([
            "crud" => [
                'model' => $this->modelObject,
                'action' => $this->action,
                'route' => $this->route,
                'routeParams' => $this->routeParams,
                'translatable' => $this->translatable,
            ],
            '_view' => $this->view,
            'title' => str_plural($this->view['name']) . " : " . config('app.name') . ' Admin',
            '_list' => $this->list,
            'extraActions' => $this->extraActions,
            'extraButtons' => $this->extraButtons,
            'fields' => $this->fields,
            '_can' => $this->can,
            '_limit' => $this->limit,
        ]);
    }

    /**
     * Set Properties for main controller
     */
    private function setParamsByController()
    {
        /* Parse Action and Controller */
        $route = Route::currentRouteAction();
        $parsed = explode("@", $route ?: 'UserController@index');
        $action = $parsed[1];
        $names = explode("\\", $parsed[0]);
        /* Get Model Name */
        $name = str_replace('Controller', '', end($names));
        $this->modelName = $this->modelName ?: $name;

        $this->model = "\\App\\Models\\" . $this->modelName ?: $name;
        $this->modelObject = new $this->model;
        $this->route = $this->route ?: str_plural(lcfirst($name));
        $this->action = $action;
    }

    /**
     * Generate Validation rules array
     *
     * @param string $action
     * @param bool $id
     * @return array
     */
    function generateValidation($action, $id = false)
    {
        $validation = [];

        foreach ($this->fields as $field) {
            if (isset($field['validation'])) {
                $value = false;

                if (is_array($field['validation'])) {
                    if (isset($field['validation'][$action])) {
                        $value = $field['validation'][$action];
                    }
                } else {
                    $value = $field['validation'];
                }

                if (str_contains($value, 'unique')) {
                    $value .= ',' . ($id ?: 'NULL');

                    if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($this->model))) {
                        $value .= ',id,deleted_at,NULL';
                    }
                }

                if ($value && isset($field['name'])) {
                    $validation[$field['name']] = $value;
                }
            }
        }

        return $validation;
    }

    /**
     * Set Translation
     */
    private function setTranslatable()
    {
        if (property_exists($this->model, 'translatedAttributes')) {
            $this->translatable = (new $this->model())->translatedAttributes;
        }
    }

    /**
     * Get Current language
     */
    public function setCurrentLang()
    {
        if ($this->translatable) {
            if (\Request::has('lang') and in_array(\Request::get('lang'), config('translatable.locales'))) {
                $this->currentLang = \Request::get('lang');
            } else {
                $this->currentLang = config('translatable.fallback_locale');
            }

            View::share('currentLang', $this->currentLang);
        }
    }

    /**
     * Generate view template dir
     */
    private function setViewPath()
    {
        if (!$this->templateDir) {
            $this->templateDir = $this->route;
        }
    }

    /**
     * Merging default permission attributes
     */
    private function mergeCanAttribute()
    {
        $defaults = [
            'create' => true,
            'update' => true,
            'delete' => true,
            'export' => false,
        ];
        $this->can = array_merge($defaults, $this->can);
    }

    /**
     * Merging default view attributes
     */
    private function mergeViewAttribute()
    {
        $defaults = [
            'name' => $this->modelName,
            'sub_title' => null,
            'formStyle' => 'horizontal',
            'listColumns' => 12,
            'formColumns' => 8,
            'bodyClass' => null,
            'colorCondition' => false,
        ];
        $this->view = array_merge($defaults, $this->view);
    }

    /**
     * Generate view path
     *
     * @param $blade
     * @return string
     */
    public function panelView($blade)
    {
        return $this->templateDir . '.' . $blade;
    }


    /********
     ********
     * CRUD *
     ********
     ********/

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {


        if (Auth::guard('admin')->check()) {
            if (!Auth::guard('admin')->user()->can('read-' . $this->route)) {
                return redirect("/");
            }
        }

        /* Get custom listing items */
        if (method_exists($this, 'indexObject')) {
            $items = $this->indexObject();
            if (is_array($items) and array_key_exists('error', $items)) {
                Alert::error($items['error']);

                return redirect()->route($this->route . '.' . $this->action);
            }
            if (method_exists($this, 'indexObject') && $this->can['export'] && (\request()->has('export') || (\request()->has('search_type') && \request()->get('search_type') == 'export')) && $items->count()) {

                return $this->export($items);
            }
        } else {
            $items = $this->modelObject->latest();
            /* Add with to query */
            if ($this->with) {
                $items = $items->with($this->with);
            }
            /* Add with to query */
            if ($this->withCount) {
                $items = $items->withCount($this->withCount);
            }
            $items = $items->paginate($this->limit);
        }

        // dump($this->list);

//        dd($this->panelView('list'));

        return view($this->panelView('list'), compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!$this->can['create']) {
            return abort(403);
        }

        $form = [
            'selfLink' => route($this->route . '.create', $this->routeParams),
            'route' => route($this->route . '.store', $this->routeParams),
            'method' => 'post',
        ];

        return view($this->panelView('form'), compact('form'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!$this->can['create']) {
            return abort(403);
        }

        $this->validate($request, $this->generateValidation('store'));

        $item = $this->modelObject;

        // replace empty values with NULL, so that it will work with MySQL strict mode on
        $allRequest = $request->all();

        foreach ($request->all() as $key => $value) {
            if (empty($value) && $value !== '0') {
                $allRequest[$key] = null;
            }
        }

        $pivotFields = [];

        foreach ($this->fields as $field) {
            if (isset($field['nodb']) && $field['nodb']) continue;
            if (isset($field['name_child']) and !isset($field['attributes']['disabled'])) {
                $item->{$field['name_child']} = $request->get($field['name_child']);
            }
            if (isset($field['name_parent']) and !isset($field['attributes']['disabled'])) {
                $item->{$field['name_parent']} = $request->get($field['name_parent']);
            }
            if (isset($field['name']) and !isset($field['attributes']['disabled'])) {
                if ($field['name'] == 'ru_hscodes[]') continue;
                if ($field['name'] == 'ru_names[]') continue;
                if ($field['name'] == 'ase_names[]') continue;
                if ($field['name'] == 'ru_types[]') continue;
                if ($field['name'] == 'ase_types[]') continue;
                if ($field['name'] == 'ru_shipping_amounts[]') continue;
                if ($field['name'] == 'ru_weights[]') continue;
                if ($field['name'] == 'ru_items[]') continue;
                if ($field['name'] == 'pkg_goods') continue;
                if (isset($field['pivot']) && $field['pivot']) {
                    $pivotFields[] = $field;
                } else {
                    if (in_array($field['type'], ['image', 'file'])) {
                        if ($request->hasFile($field['name'])) {
                            $fileName = uniqid() . '.' . $request->file($field['name'])->getClientOriginalExtension();
                            $request->file($field['name'])->move(public_path($this->modelObject->uploadDir), $fileName);
                            $item->{$field['name']} = $fileName;
                        }
                    } else {
                        $item->{$field['name']} = $request->get($field['name']);
                    }
                }
            }
        }

        if ($this->routeParams) {
            foreach ($this->routeParams as $key => $routeParam) {
                $item->{$key} = $routeParam;
            }
        }

        if (method_exists($this, 'autoFill')) {
            foreach ($this->autoFill() as $key => $val) {
                $item->{$key} = $val;
            }
        }

        $item->save();
        if (property_exists($this, 'itemId')) $this->itemId = $item->id;

        if ($pivotFields) {
            foreach ($pivotFields as $pivotField) {
                $item->{$pivotField['name']}()->sync($request->get($pivotField['name']));
            }
        }

        if ($request->get('only_id') == null) {
            Alert::success(trans('saysay::crud.action_alert', [
                'name' => $this->modelName,
                'key' => clearKey($this->notificationKey),
                'value' => $item->{$this->notificationKey},
                'action' => 'created',
            ]));

            return $this->createRedirection ? redirect()->route($this->createRedirection, $item->id) : redirect()->route($this->route . '.index', $this->routeParams);
        }


        return $item->id;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
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

        if (method_exists($this, 'editObject')) {
            $item = $this->editObject($id);

            if (is_array($item) and array_key_exists('error', $item)) {
                Alert::error($item['error']);

                return redirect()->route($this->route . '.index');
            }
        } else {
            $item = $this->modelObject->find($id);
        }

        if (!$item) {
            Alert::error(trans('saysay::crud.not_found'));

            return back();
        }

        if ($this->currentLang) {
            $item->setDefaultLocale($this->currentLang);
        }



//        dd($this->panelView('form'));
        return view($this->panelView('form'), compact('item', 'form'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $id = ($request->get('only_id') == null && \request()->route('id') != null) ? \request()->route('id') : $id;

        $this->validate($request, $this->generateValidation('update', $id));
        $this->setCurrentLang();

        $allRequest = $request->all();
        foreach ($request->all() as $key => $value) {
            if (empty($value) && $value !== '0') {
                $allRequest[$key] = null;
            }
        }

        $item = $this->modelObject->find($id);


        $lang = $request->get('_lang');

        if ($lang) {
            $item->setDefaultLocale($lang);
        }

        $pivotFields = [];

        foreach ($this->fields as $field) {
            if (isset($field['nodb']) && $field['nodb']) continue;
            if (isset($field['name_child']) and !isset($field['attributes']['disabled'])) {
                $item->{$field['name_child']} = $request->get($field['name_child']);
            }
            if (isset($field['name_parent']) and !isset($field['attributes']['disabled'])) {
                $item->{$field['name_parent']} = $request->get($field['name_parent']);
            }
            if (isset($field['name']) and !isset($field['attributes']['disabled'])) {
                if ($field['name'] == 'ru_hscodes[]') continue;
                if ($field['name'] == 'ru_names[]') continue;
                if ($field['name'] == 'ase_names[]') continue;
                if ($field['name'] == 'ru_types[]') continue;
                if ($field['name'] == 'ase_types[]') continue;
                if ($field['name'] == 'ru_shipping_amounts[]') continue;
                if ($field['name'] == 'ru_weights[]') continue;
                if ($field['name'] == 'ru_items[]') continue;
                if ($field['name'] == 'pkg_goods') continue;
                if (isset($field['pivot']) && $field['pivot']) {
                    $pivotFields[] = $field;
                } else {
                    if (in_array($field['type'], ['image', 'file'])) {
                        if ($request->hasFile($field['name'])) {
                            $fileName = uniqid() . '.' . $request->file($field['name'])->getClientOriginalExtension();

                            /* Delete previous file */
                            if ($item->{$field['name']} && file_exists(public_path($this->modelObject->uploadDir . basename($item->{$field['name']})))) {
                                unlink(public_path($this->modelObject->uploadDir . basename($item->{$field['name']})));
                            }

                            $request->file($field['name'])->move(public_path($this->modelObject->uploadDir), $fileName);
                            $item->{$field['name']} = $fileName;
                        }
                    } else {

                        if ('password' === $field['type']) {
                            if (!is_null($request->get($field['name']))) {
                                $item->{$field['name']} = $request->get($field['name']);
                            }
                        } else {
                            $item->{$field['name']} = $request->get($field['name']);
                        }
                    }
                }
            }
        }

        if (method_exists($this, 'warehouseFill')) {
            if ($item->status == 7) {
                foreach ($this->warehouseFill() as $key => $val) {
                    $item->{$key} = $val;
                }
            }
        }

        $item->save();

        /* Sync n-n relations */
        if ($pivotFields) {
            foreach ($pivotFields as $pivotField) {
                $item->{$pivotField['name']}()->sync($request->get($pivotField['name']));
            }
        }

        if ($request->get('only_id') == null) {
            Alert::success(trans('saysay::crud.action_alert', [
                'name' => $this->modelName,
                'key' => clearKey($this->notificationKey),
                'value' => $item->{$this->notificationKey},
                'action' => 'updated',
            ]));

            return redirect()->route($this->route . '.index', $this->routeParams);
        }

        return $item->id;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $id = \request()->route('id') != null ? \request()->route('id') : $id;

        if (Auth::guard('admin')->check()) {
            if (!Auth::guard('admin')->user()->can('delete-' . $this->route)) {
                return redirect("/");
            }
        } else {
            if (!$this->can['delete']) {
                return redirect("/");
            }
        }

        if (method_exists($this, 'deleteObject')) {
            $item = $this->deleteObject($id);

            if (is_array($item) and array_key_exists('error', $item)) {
                Alert::error($item['error']);

                return redirect()->route($this->route . '.index');
            }
        } else {
            $item = $this->modelObject->find($id);
        }

        if (!$item) {
            Alert::error(trans('saysay::crud.not_found'));
        } else {
            Alert::success(trans('saysay::crud.action_alert', [
                'name' => $this->modelName,
                'key' => clearKey($this->notificationKey),
                'value' => $item->{$this->notificationKey},
                'action' => 'deleted',
            ]));
            $item->delete();
        }

        return redirect()->route($this->route . '.index', $this->routeParams);
    }

    /**
     * For ajax updates
     *
     * @param Request $request
     * @param $id
     * @return bool|string
     */
    public function ajax(Request $request, $id)
    {
        $item = ($this->model)::find($id);

        if (!$item) {
            return false;
        }

        $value = $request->has('value') ? (is_array($request->get('value')) ? $request->get('value')[0] : $request->get('value')) : 0;
        $item->{$request->get('name')} = $value;
        $item->save();

        return Response::json(['message' => "Item's " . $request->get('name') . " has been updated!."]);
    }

    /**
     * For multiple updates
     *
     * @param Request $request
     * @return string
     */
    public function multiUpdate(Request $request)
    {
        $items = ($this->model)::whereIn('id', $request->get('ids'))->where($request->get('key'), "!=", $request->get('value'));

        $count = $items->count();

        if ($count) {

            $items->update([$request->get('key') => $request->get('value')]);

            return Response::json(['message' => $count . ' items has been updated!']);
        } else {
            return Response::json(['message' => "There isn't any data to update!"], 400);
        }
    }
}
