{{ Form::open(['class' => 'btn btn-sm btn-group sure-that no-padding', 'route' => $route, 'method' => 'delete']) }}
    <button class="{{ (isset($class) ? $class : '') . " btn-ladda btn-ladda-spinner" }}" data-spinner-color="#fff" data-style="slide-left" type="submit">
        <i class="icon-trash"></i>
    </button>
{{ Form::close() }}