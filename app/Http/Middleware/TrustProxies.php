<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array|string|null
     */
    protected $proxies = '*';

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO;

    public function handle($request, \Closure $next)
    {
        \Log::info('Request Headers', [
            'host' => $request->header('host'),
            'x-forwarded-host' => $request->header('x-forwarded-host'),
            'url' => $request->url(),
            'full_url' => $request->fullUrl()
        ]);
        
        return parent::handle($request, $next);
    }
}