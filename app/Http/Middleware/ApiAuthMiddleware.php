<?php

namespace App\Http\Middleware;

use Closure;

class ApiAuthMiddleware
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
        $token=$request->header('Authorization');//Authorization desde el frontend con el token
        $jwtAuth= new \JwtAuth();
        $checkToken=$jwtAuth->checkToken($token);//verifica el token en Jwt
        if($checkToken){
            return $next($request);
        }else{
            $data = array(
                'status' => 'error',
                'code' => 401,
                'message' => 'El token enviado no corresponde a un user Autorizado'
            );
        }
        return response()->json($data,$data['code']);   
    }
}
