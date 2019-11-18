<?php

namespace App\Http\Middleware;

use Closure;
use App\User;

class ApiTokenMiddleware
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
        // if(AppToken::where('api_token', $request->api_token)->count()<=0){
        //     // return abort(401);
        //     return response(['message'=>'Invalid api token'], 401);
        // }

        // \dd(getallheaders());
        if(!isset(getallheaders()['Token'])){
            return response(['message'=>'Api token is empty'], 401);
        }
        // dummy token
        if(getallheaders()['Token']=='AyamGeprekCabe3!'){
            return $next($request);
        }
        if(!User::where(['api_token'=>getallheaders()['Token']])->first()){
        // if(getallheaders()['Token']!='123'){
            return response(['message'=>'Invalid api token'], 401);
        }
        return $next($request);
    }
}
