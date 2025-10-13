@php
    $id = rand(1, 10);
@endphp
<a href="/logout"
   onclick="event.preventDefault(); document.getElementById('logout-form-<?= $id?>').submit();">
    <i class="icon-switch2"></i>{{ trans('saysay::base.logout') }}
</a>
<form id="logout-form-<?= $id?>" action="{{ route('auth.logout') }}" method="POST" style="display: none;">
    {{ csrf_field() }}
</form>