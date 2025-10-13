<div class="btn-group">
    <a href="#" class="label border-left-{{ $head['options'][$entry]['class'] or '' }} label-striped dropdown-toggle" data-toggle="dropdown" aria-expanded="false">{{ str_limit(strip_tags($head['options'][$entry]['value']), 80) }} <span class="caret"></span></a>

    <ul class="dropdown-menu dropdown-menu-right">
        @foreach($head['options'] as $option)
        <li><a href="#"><span class="status-mark position-left border-{{ $option['class'] or '' }}"></span> {{ $option['value'] }}</a></li>
        @endforeach
    </ul>
</div>