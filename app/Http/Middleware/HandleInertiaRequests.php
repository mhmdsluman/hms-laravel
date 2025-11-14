<?php

namespace App\Http\Middleware;
use Tighten\Ziggy\Ziggy;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user(),
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            // Add this for i18n
            'locale' => function () {
                return app()->getLocale();
            },
            'language' => function () {
                if (file_exists(resource_path('lang/'. app()->getLocale() .'.json'))) {
                    return json_decode(file_get_contents(resource_path('lang/'. app()->getLocale() .'.json')), true);
                }
                return [];
            },
            // Add this to handle flash messages
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ]);
    }
}
