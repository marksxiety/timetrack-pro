<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key')->map(function ($value) {
            $decoded = json_decode($value, true);
            return $decoded !== null ? $decoded : $value;
        });

        return Inertia::render('Admin/Settings', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'default_shift_codes' => ['required', 'array', 'size:7'],
            'default_shift_codes.*.day' => ['required', 'string'],
            'default_shift_codes.*.code' => ['required', 'string'],
            'minimum_overtime_hours' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
                if (abs(fmod((float) $value, 0.25)) > 0.001) {
                    $fail('The minimum overtime hours must be in 0.25 increments.');
                }
            }],
            'overtime_minute_step' => ['required', 'integer', 'in:1,5,10,15,30'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        return redirect()->back()->with('message', 'Settings updated successfully.');
    }
}
