<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Services\CachingService;
class CheckForMaintenanceMode
{
    protected $except = [];
    protected $cache;
    public function __construct(CachingService $cache)
    {
        $this->cache = $cache;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $systemSettings = $this->cache->getSystemSettings();
        if (isset($systemSettings['web_maintenance']) && $systemSettings['web_maintenance'] == 1) {
            if (!Auth::check() || (Auth::check() && !Auth::user()->hasRole('Super Admin'))) {
                return \Response::view('errors.503', [], 503);
            }
        }

        return $next($request);
    }
}
