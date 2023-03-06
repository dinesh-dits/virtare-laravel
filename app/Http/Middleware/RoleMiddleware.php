<?php

namespace App\Http\Middleware;
//use Illuminate\Routing\Router;
//use Illuminate\Support\Facades\Route;
//use Illuminate\Support\Facades\Request;
//use Illuminate\Routing\Router;
//use Illuminate\Support\Facades\Route;
//use \Laravel\Lumen\Routing\Router as Router;
//use Laravel\Lumen\Application;
//use Illuminate\Support\Facades\Route;
//use Illuminate\Http\Request;

//use Illuminate\Routing\Route;




use Closure;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
   
    public function handle($request, Closure $next,$name=NULL)
    {       
        if (!empty($name)) {
           
            $routes = app()->router->getRoutes();
            $action = '';
            foreach ($routes as $key1 => $value1) {
                if ($value1['action']['as'] == $name) {
                    $action = $value1['action']['uses'];
                    break;
                }
            }
            if (!empty($action)) {
                $action =  explode("\\", $action);
                $action = end($action);
                $action =  explode("@", $action);
                echo $controller = $action[0];
                echo $actionName = $action[1];
                $next($request);  
            }
        } else {
            die('STOP');
            return $next($request);
        }
    }
}
