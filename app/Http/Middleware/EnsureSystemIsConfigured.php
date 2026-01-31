<?php

namespace App\Http\Middleware;

use App\Models\CompanyBookmark;
use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSystemIsConfigured
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip for console and non-auth requests (like login)
        if (app()->runningInConsole() || !$request->user()) {
            return $next($request);
        }

        // Skip for certain routes to avoid infinite redirect loops
        $allowedRoutes = ['admin.settings', 'admin.settings.update', 'admin.company-bookmarks.update', 'admin.test-email', 'logout', 'admin.interrogate-url'];
        if ($request->route() && in_array($request->route()->getName(), $allowedRoutes)) {
            return $next($request);
        }

        $configured = Setting::get('company_collection_title') && CompanyBookmark::count() > 0;

        if (!$configured && $request->user()->is_admin) {
             return redirect()->route('admin.settings', ['tab' => 'bookmarks', 'setup' => '1']);
        }

        return $next($request);
    }
}
