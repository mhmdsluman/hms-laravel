<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function index(): Response
    {
        $settings = Setting::all()->pluck('value', 'key');
        return Inertia::render('Admin/Settings/Index', ['settings' => $settings]);
    }

    public function update(Request $request)
    {
        foreach ($request->all() as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        if ($request->hasFile('hospital_logo')) {
            $path = $request->file('hospital_logo')->store('logos', 'public');
            Setting::updateOrCreate(['key' => 'hospital_logo'], ['value' => $path]);
        }

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }
}
