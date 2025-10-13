<?php

namespace App\Http\Controllers\Admin\Azerpost;

use App\Http\Controllers\Controller;
use App\Models\Azerpost\AzerpostOffice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AzerpostOfficeController extends Controller
{
    public function offices(Request $request)
    {
        $offices = AzerpostOffice::paginate(15);

        return view('admin.azerpost.offices', compact('offices'));
    }

    public function storeOffice(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'note' => 'nullable|string',
            'foreign_id' => 'required',
            'address' => 'nullable|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        // Create a new office record
        AzerpostOffice::query()->create([
            'name' => $validatedData['name'],
            'name_en' => $validatedData['name_en'],
            'foreign_id' => $validatedData['foreign_id'] ?? 0,
            'description' => $validatedData['description'] ?? null,
            'description_en' => $validatedData['description_en'] ?? null,
            'address' => $validatedData['address'] ?? null,
            'address_en' => $validatedData['address_en'] ?? null,
            'contact_name' => $validatedData['contact_name'] ?? null,
            'contact_phone' => $validatedData['contact_phone'] ?? null,
            'latitude' => $validatedData['latitude'] ?? null,
            'longitude' => $validatedData['longitude'] ?? null,
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
            'foreign_id' => 'required',
            'description_en' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'address_en' => 'nullable|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        AzerpostOffice::where('id', $request->input('id'))->update([
            'name' => $validatedData['name'],
            'name_en' => $validatedData['name_en'] ?? null,
            'description' => $validatedData['description'] ?? null,
            'description_en' => $validatedData['description_en'] ?? null,
            'address' => $validatedData['address'] ?? null,
            'foreign_id' => $validatedData['foreign_id'] ?? 0,
            'address_en' => $validatedData['address_en'] ?? null,
            'contact_name' => $validatedData['contact_name'] ?? null,
            'contact_phone' => $validatedData['contact_phone'] ?? null,
            'latitude' => $validatedData['latitude'] ?? null,
            'longitude' => $validatedData['longitude'] ?? null,
            'updated_at' => now()
        ]);

        return back()->with('success', $validatedData['name'] . ' yaradildi');
    }

    public function deleteOffice($id)
    {
        $find = AzerpostOffice::find($id);
        if (!$find) {
            return back()->withErrors('tapılmadı');
        }
        $find->delete();

        return back()->withSuccess('Silindi');
    }
}
