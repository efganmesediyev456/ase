<?php

namespace App\Http\Controllers\Admin\AzeriExpress;

use App\Models\Log;
use App\Services\AzeriExpress\CourierService;

class CourierOrderController
{
    protected $courierService;

    public function __construct(CourierService $courierService)
    {
        $this->courierService = $courierService;
    }

    public function getOrders()
    {
        $response = $this->courierService->getOrders();

        return response()->json($response);
    }

    public function getOrderById($id)
    {
        $response = $this->courierService->getOrderById($id);

        return response()->json($response);
    }

    public function createOrder()
    {
        $data = [
            'pickup_lattitude' => '40.3763811',
            'pickup_longitude' => '49.8410024',
            'delivery_lattitude' => '40.3777421',
            'delivery_longitude' => '49.8747613',
            'transport' => 1,
            'weight' => 0.1,
            'priority' => 1,
            'sender_name' => 'John Doe',
            'receiver_name' => 'Martin Garix',
        ];
        $response = $this->courierService->createOrder($data);
        Log::create([
            'level' => 'debug',
            'message' => 'Azeriexpress Courier CreateOrder',
            'context' => json_encode($response),
        ]);
        return response()->json($response);
    }
}
