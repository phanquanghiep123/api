<?php

namespace App\Http\Middleware;
use App\Core\APIBackend;
use Closure;

class Api
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $API = new APIBackend();
        
        if(!$API->checkAuth()){
            return response()->json($API->_DATA,200);
        }
        return $next($request);
         
    }
}
