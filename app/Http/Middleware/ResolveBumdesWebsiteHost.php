<?php

namespace App\Http\Middleware;

use App\Models\WebsiteBumdes;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveBumdesWebsiteHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = strtolower($request->getHost());
        $primaryHost = strtolower((string) parse_url(config('app.url'), PHP_URL_HOST));
        $primaryHosts = app()->isProduction() ? [$primaryHost] : [$primaryHost, 'localhost', '127.0.0.1'];

        if (in_array($host, $primaryHosts, true)) {
            return $next($request);
        }

        $base = preg_quote((string) config('bumdes.website.base_domain'), '#');
        $website = null;
        if (preg_match('#^([a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)\.'.$base.'$#D', $host, $matches)) {
            $website = WebsiteBumdes::query()->active()->where('subdomain', $matches[1])->first();
        } elseif (in_array($host, config('bumdes.website.verified_custom_domains', []), true)) {
            $website = WebsiteBumdes::query()->active()->where('custom_domain', $host)->first();
        }

        abort_if($website === null, 404);
        abort_unless($request->isMethod('GET') || $request->isMethod('HEAD'), 404);
        abort_unless($request->path() === '/' || preg_match('#^media/(?:gallery|product)/[0-9]+$#D', $request->path()), 404);
        $request->attributes->set('bumdesWebsite', $website);

        return $next($request);
    }
}
