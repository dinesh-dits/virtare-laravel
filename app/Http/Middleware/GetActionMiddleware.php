<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GetActionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
       
        // Get current controller and action name 
        $controllerActionArr = $request->route()->getAction();
        $controllerClass = class_basename($controllerActionArr['controller']);
        list($controllerName, $controllerAction) = explode('@', $controllerClass); 
        return $next($request);
    }
}
