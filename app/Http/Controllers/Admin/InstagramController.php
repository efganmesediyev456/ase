<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Instagram;
use Illuminate\Support\Facades\Storage;

class InstagramController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Instagram::latest()->paginate(10);
        return view('admin.instagrams.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.instagrams.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'image' => 'required',
            'url'   => 'required|string|min:3',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $destinationPath = public_path('uploads/instagram');
            $file->move($destinationPath, $filename);

            $validated['image'] = 'uploads/instagram/' . $filename;
        }


        Instagram::create($validated);

        return redirect()->route('instagrams.index')->with('success', 'Post added successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Instagram $instagram)
    {
        return view('admin.instagrams.edit', compact('instagram'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Instagram $instagram)
    {
        $validated = $request->validate([
            'image' => 'required',
            'url'   => 'required|string|min:3',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $destinationPath = public_path('uploads/instagram');
            $file->move($destinationPath, $filename);

            $validated['image'] = 'uploads/instagram/' . $filename;
        }


        $instagram->update($validated);

        return redirect()->route('instagrams.index')->with('success', 'Post updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Instagram $instagram)
    {
        if ($instagram->image && Storage::disk('public')->exists($instagram->image)) {
            Storage::disk('public')->delete($instagram->image);
        }

        $instagram->delete();

        return redirect()->route('instagrams.index')->with('success', 'Post deleted successfully!');
    }
}
