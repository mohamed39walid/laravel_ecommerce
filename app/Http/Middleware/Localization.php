<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Localization
{
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->header('Accept-Language', 'en');
        $locale = in_array($locale, ['en', 'ar']) ? $locale : 'en';
        app()->setLocale($locale);
        
        return $next($request);
    }
}