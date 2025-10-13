<?php

namespace App\Http\Controllers\Admin\CourierSaas;

use App\Http\Controllers\Controller;
use App\Models\AzeriExpress\AzeriExpressOffice;
use App\Services\Integration\UnitradeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CourierSaasOfficeController extends Controller
{
    public function offices(Request $request)
    {
        $offices = AzeriExpressOffice::paginate(15);

        return view('admin.azeriexpress.offices', compact('offices'));
    }

    public function storeOffice(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        // Create a new office record
        AzeriExpressOffice::query()->create([
            'name' => $validatedData['name'],
            'name_en' => $validatedData['name_en'] ?? null,
            'description' => $validatedData['description'],
            'description_en' => $validatedData['description_en'] ?? null,
            'address' => $validatedData['address'],
            'address_en' => $validatedData['address_en'] ?? null,
            'contact_name' => $validatedData['contact_name'],
            'contact_phone' => $validatedData['contact_phone'],
            'latitude' => $validatedData['latitude'],
            'longitude' => $validatedData['longitude'],
            'updated_at' => now()
        ]);

        return back()->with('success', $request->input('name') . ' created');
    }

    public function updateOffice(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description' => 'nullable|string',
            'description_en' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'address_en' => 'nullable|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        AzeriExpressOffice::where('id', $request->input('id'))->update([
            'name' => $validatedData['name'],
            'name_en' => $validatedData['name_en'] ?? null,
            'description' => $validatedData['description'],
            'description_en' => $validatedData['description_en'] ?? null,
            'address' => $validatedData['address'],
            'address_en' => $validatedData['address_en'] ?? null,
            'contact_name' => $validatedData['contact_name'],
            'contact_phone' => $validatedData['contact_phone'],
            'latitude' => $validatedData['latitude'],
            'longitude' => $validatedData['longitude'],
            'updated_at' => now()
        ]);

        return back()->with('success', $validatedData['name'] . ' yaradildi');
    }

    public function deleteOffice($id)
    {
        $find = AzeriExpressOffice::find($id);
        if (!$find) {
            return back()->withErrors('tapılmadı');
        }
        $find->delete();

        return back()->withSuccess('Silindi');
    }
}
