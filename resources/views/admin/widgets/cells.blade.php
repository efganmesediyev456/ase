@if(app('laratrust')->can('update-cells'))
<?php
$cells = App\Models\Package::select([
    \DB::raw('count(id) as total'),
    'cell',
])->whereNotNull('cell')->whereIn('status',[2,8])->groupBy('cell')->orderBy('cell', 'asc');

/* Filter cities */
$cities = auth()->guard('admin')->user()->cities->pluck('id')->all();
if ($cities) {
    $cells->whereHas('user', function (
        $query
    ) use ($cities) {
        $query->whereIn('city_id', $cities)->orWhere('city_id', null);
    });
}
$cells = $cells->pluck('total', 'cell')->all();

$cells=[];
$str='select count(p.id) as total,p.cell from packages p';
$str.=' left outer join users u on p.user_id=u.id';
$str.=' where p.cell is not null and (p.status=2 or p.status=8) and p.deleted_at is null';
if($cities)
  $str.=' and u.city_id in ('.implode(',',$cities).')';
$str.=' group by p.cell order by p.cell';
$cells1=DB::select($str);
foreach($cells1 as $cell1) {
    $cells[$cell1->cell]=$cell1->total;
}
$str='select count(id) as total,cell from tracks where cell is not null and (status=16 or status=20) and deleted_at is null';
if($cities)
  $str.=' and city_id in ('.implode(',',$cities).')';
$str.=' group by cell order by cell';
$cells1=DB::select($str);
foreach($cells1 as $cell1) {
    if(isset($cells[$cell1->cell]))
        $cells[$cell1->cell]+=$cell1->total;
    else
        $cells[$cell1->cell]=$cell1->total;
}

?>
    <div style="margin-top: 30px">
    <table style="margin: 0 auto;" class="chess-board">
        <tbody>
        <tr>
            <th></th>
            <?php $max = 0; ?>
            @foreach (cellStructure() as $let => $value)
                <?php $max = $value > $max ? $value : $max; ?>
                <th>{{ $let }}</th>
            @endforeach
        </tr>
        @for($i = 1; $i <= $max; $i++)
            <tr>
                <th>{{ $i }}</th>
                @foreach (cellStructure() as $let => $value)
                    @if($i <= $value)
                        <?php $cellName = $let . $i; $numPack = isset($cells[$cellName]) ? $cells[$cellName] : 0; ?>
                        <td data-id="{{ $cellName }}" class="light select_cell" style="background: {{ luminance($numPack) }}"><div class="@if((isset($nearBy) && $nearBy == $cellName) || (isset($item->cell) && $item->cell ==$cellName )) pulse @endif">{{ $numPack }}</div></td>
		    @else
			<td></td>
                    @endif
                @endforeach

            </tr>
        @endfor

        </tbody>
    </table>
</div>
@endif
