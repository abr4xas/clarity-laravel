<?php

declare(strict_types=1);

namespace Abr4xas\ClarityLaravel\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

final class ClarityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('clarity.enabled', false)) {
            return $next($request);
        }

        $this->shareClarityData($request);

        return $next($request);
    }

    /**
     * Share Clarity data with views.
     */
    private function shareClarityData(Request $request): void
    {
        $tags = [];

        // Auto-tag environment
        if (config('clarity.auto_tag_environment', true)) {
            $tags['environment'] = [app()->environment()];
        }

        // Auto-tag routes
        if (config('clarity.auto_tag_routes', false)) {
            $routeName = $request->route()?->getName();
            if ($routeName) {
                $tags['route'] = [$routeName];
            }

            $routePrefix = $request->route()?->getPrefix();
            if ($routePrefix) {
                $tags['route_prefix'] = [$routePrefix];
            }
        }

        // Share tags with views
        if (! empty($tags)) {
            View::share('clarity_auto_tags', $tags);
        }

        // Auto-identify users
        if (config('clarity.auto_identify_users', false) && $request->user()) {
            View::share('clarity_auto_identify_user', $request->user());
        }
    }
}
