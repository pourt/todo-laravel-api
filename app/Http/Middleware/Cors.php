<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $httpOrigin = isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] ? $_SERVER['HTTP_ORIGIN'] : null;
        $httpReferer = isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : null;

        $requestScheme = parse_url($httpOrigin, PHP_URL_SCHEME);
        $requestHost = parse_url($httpOrigin, PHP_URL_HOST);
        $requestPort = parse_url($httpOrigin, PHP_URL_PORT);

        if (!$httpOrigin && $httpReferer) {
            $requestScheme = parse_url($httpReferer, PHP_URL_SCHEME);
            $requestHost = parse_url($httpReferer, PHP_URL_HOST);
            $requestPort = parse_url($httpReferer, PHP_URL_PORT);
        }

        //$origin = $requestScheme . "://" . $requestHost . (($requestPort != "" && $requestPort != "NULL" && $requestPort != "80") ? ":" . $requestPort : "");
        $origin = $requestHost . (($requestPort != "" && $requestPort != "NULL" && $requestPort != "80") ? ":" . $requestPort : "");

        return $next($request)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, X-Token-Auth, Authorization')
            ->header('Access-Control-Allow-Credentials', ' true');
    }
}
