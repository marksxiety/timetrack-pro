<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\Storage;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth.user' => fn() => $request->user()
                ? collect($request->user()->only('name', 'email', 'active', 'organization_unit_id', 'role', 'employeeid', 'avatar'))
                ->mapWithKeys(fn($value, $key) => $key === 'avatar'
                    ? ['avatar_url' => $value ? Storage::url($value) : null]
                    : [$key => $value])
                ->toArray()
                : null,
            'flash' => [
                'message' => fn() => $request->session()->get('message')
            ],
        ];
    }
}
