<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Models\Point;
use Illuminate\Http\Request;
use Alert;

class PointController extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    protected $modelName = 'Point';
    protected $route = 'points';

//    protected $templateDir = 'admin.points';

    protected $list = [
        'id' => ['label' => 'ID', 'sortable' => true],
        'courier_id' => ['label' => 'Kuryer ID', 'sortable' => true],
        'courier_name' => ['label' => 'Kuryer Adı', 'sortable' => true],
        'lat' => ['label' => 'Enlik', 'sortable' => true],
        'lon' => ['label' => 'Uzunluq', 'sortable' => true],
        'created_at' => ['label' => 'Yaradılma tarixi', 'sortable' => true]
    ];

    protected $fields = [
        [
            'name' => 'kml',
            'label' => 'kml file',
            'type' => 'file',
            'validation' => 'required'
        ],
    ];

    protected $view = [
        'name' => 'Nöqtə',
        'sub_title' => 'Kuryer nöqtələrinin idarə edilməsi'
    ];


    // Xüsusi siyahı sorğusu
    public function indexObject()
    {
        return Point::orderBy('id','desc')->paginate($this->limit);
    }

    public function index()
    {
        $items = Point::latest()->paginate($this->limit);
        return view($this->panelView('list'), compact('items'));
    }

    public function store(Request $request)
    {
        if (!$request->hasFile('kml')) {
            return response()->json(['error' => 'No file uploaded'], 400);
        }

        $file = $request->file('kml');
        $fileName = 'map.kml';

        $directoryPath = storage_path('app/');
        $filePath = $directoryPath . $fileName;
        $jsonPath = $directoryPath . 'regions.json';

        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0755, true);
        }

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $file->move($directoryPath, $fileName);

        if (!file_exists($filePath) || !is_readable($filePath)) {
            return response()->json(['error' => 'File not found or unreadable after upload'], 500);
        }

        $xmlContent = file_get_contents($filePath);
        $xml = simplexml_load_string($xmlContent);

        if (!$xml) {
            return response()->json(['error' => 'Invalid XML format'], 422);
        }

        $namespaces = $xml->getNamespaces(true);
        $xml->registerXPathNamespace('kml', $namespaces[''] ?? 'http://www.opengis.net/kml/2.2');

        $regions = [];
        foreach ($xml->xpath('//kml:Placemark') as $placemark) {
            $raw = (string) $placemark->name;

            $parts = preg_split('/\s*[-–—]\s*/u', $raw);

            $name = trim($parts[0]);

            $id = isset($parts[1]) ? (int) trim($parts[1]) : null;
//            if($name == 'Şəfa Müalicəvi Diaqnostika Mərkəzi I Шафа Лечебно'){
//                dd($id);
//            }
            $points = [];

            if (isset($placemark->Polygon)) {
                $coordinates = (string) $placemark->Polygon->outerBoundaryIs->LinearRing->coordinates;
                $points = $this->parseCoordinates($coordinates);
            }

            elseif (isset($placemark->MultiGeometry)) {
                if (isset($placemark->MultiGeometry->Polygon)) {
                    foreach ($placemark->MultiGeometry->Polygon as $polygon) {
                        $coordinates = (string) $polygon->outerBoundaryIs->LinearRing->coordinates;
                        $points = array_merge($points, $this->parseCoordinates($coordinates));
                    }
                }
            }
            elseif (isset($placemark->Point)) {
                $coordinates = (string) $placemark->Point->coordinates;
                $points = $this->parseCoordinates($coordinates);
            }
            elseif (isset($placemark->LineString)) {
                $coordinates = (string) $placemark->LineString->coordinates;
                $points = $this->parseCoordinates($coordinates);
            }

            if (!empty($points)) {
                $regions[] = [
                    'name' => $name,
                    'id' => $id,
                    'points' => $points
                ];
            }
        }

        if (file_exists($jsonPath)) {
            unlink($jsonPath);
        }
//        dd($regions);
        file_put_contents($jsonPath, json_encode($regions));


        Alert::success(trans('saysay::crud.action_alert', [
            'name' => 'Kml file import olundu',
            'key' => 'Kml',
            'value' => 'Kml',
            'action' => 'created',
        ]));

        return redirect()->route($this->route . '.create', $this->routeParams);

    }

    private function parseCoordinates($coordinates)
    {
        $points = [];
        $coordinateParts = preg_split("/[\s,\n]+/", trim($coordinates));

        for ($i = 0; $i < count($coordinateParts); $i += 3) {
            if (isset($coordinateParts[$i]) && isset($coordinateParts[$i+1])) {
                $lon = floatval($coordinateParts[$i]);
                $lat = floatval($coordinateParts[$i+1]);
                $points[] = [$lat, $lon];
            }
        }

        if (empty($points)) {
            foreach (preg_split("/[\s]+/", trim($coordinates)) as $coord) {
                $coord = trim($coord);
                if (empty($coord)) continue;

                $parts = explode(',', $coord);
                if (count($parts) >= 2) {
                    $lon = floatval($parts[0]);
                    $lat = floatval($parts[1]);
                    $points[] = [$lat, $lon];
                }
            }
        }

        return $points;
    }


    public function findCourier(Request $request)
    {
//        $lat = $request->input('lat');
        $lat = 40.3752594;
//        $lon = $request->input('lon');
        $lon = 49.91129684;

        $regions = json_decode(file_get_contents(storage_path('app/regions.json')), true);

        foreach ($regions as $region) {
            if ($this->pointInPolygon([$lat, $lon], $region['points'])) {
                return response()->json(['courier' => $region['name'],'id' => $region['id']]);
            }
        }

        return response()->json(['courier' => null, 'message' => 'No region found'], 404);
    }



    private function pointInPolygon($point, $polygon)
    {
        $x = $point[1];
        $y = $point[0];
        $inside = false;
        $n = count($polygon);
        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $xi = $polygon[$i][1]; $yi = $polygon[$i][0];
            $xj = $polygon[$j][1]; $yj = $polygon[$j][0];

            $intersect = (($yi > $y) != ($yj > $y)) &&
                ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi + 0.0000001) + $xi);
            if ($intersect) $inside = !$inside;
        }
        return $inside;
    }

}
