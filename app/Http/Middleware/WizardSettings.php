<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Services\CachingService;

class WizardSettings
{
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
        try {
            // Only check wizard settings for super admin
            if (!Auth::check() || !Auth::user()->hasRole('Super Admin')) {
                return $next($request);
            }

            //cache clear for wizard_checkMark
            $this->cache->removeSystemCache(config('constants.CACHE.SYSTEM.SETTINGS'));
            $systemSettings = $this->cache->getSystemSettings();

            $checkSettingList = [
                'wizard_checkMark',
                'system_settings_wizard_checkMark', 
                'notification_settings_wizard_checkMark',
                'email_settings_wizard_checkMark',
                'verify_email_wizard_checkMark',
                'email_template_settings_wizard_checkMark',
                'payment_settings_wizard_checkMark',
                'third_party_api_settings_wizard_checkMark'
            ];

            $allowedRoutes = [
                'wizard-settings*',
                'dashboard',
                'system-settings*', 
                'guidances*',
                'language*',
                'system-update*',
                'web-settings*',
                'faqs*',
                'notification-setting.update'
            ];

            // Check if current route is allowed
            $currentRoute = $request->route()->getName();
            $isAllowedRoute = false;
            foreach ($allowedRoutes as $route) {
                if (fnmatch($route, $currentRoute)) {
                    $isAllowedRoute = true;
                    break;
                }
            }

            // Check if any wizard setting is incomplete
            $incompleteWizard = false;
            foreach ($checkSettingList as $setting) {
                if (isset($systemSettings[$setting]) && $systemSettings[$setting] == 0) {
                    $incompleteWizard = true;
                    break;
                }
            }

            // Only redirect if wizard incomplete and route not allowed
            if ($incompleteWizard && !$isAllowedRoute) {
                return redirect()->route('wizard-settings.index');
            }

            return $next($request);

        } catch (\Exception $e) {
            return $next($request);
        }
    }
}
