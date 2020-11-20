<?php
namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\User;


class JwtAuth{
    
    public function  __construct(){
        //Esta es la llave que nos sirve para codificar el token
        $this->key="esto_es_una_clave_super_secreta_-99887766";    
    }

    /**
     * Retorna el token del usuario identificado o si se le envia un,
     * segundo parametro true, devuelve los datos de ese usuario logueado. 
     */
    public function singup($email,$password){
        //Busca un usuario con ese email y contraseña
        $user=User::where([
            'email'=>$email,
            'password'=>$password
        ])->first();
    
        //variable de autenticación
        $singup=false;
        //Validar si son correctas su email y password
        if(is_object($user)){

            if($user->state==1){
                $token=array(
                    'sub'=>   $user->id,
                    'email'=> $user->email,
                    'name'=>  $user->name,
                    'surname'=> $user->surname,
                    'image'=>$user->image,
                    'description'=>$user->description,
                    // 'role'=>    $user->role,
                    'iat'=>    time(),//creacion del token
                    'exp'=>    time()+(60*60)//tiempo de expiracion del token
                );
                //HS256 es el algoritmo de cifrado,crea el token
                $jwt=JWT::encode($token,$this->key,'HS256');
                $data=$jwt;
            }else{
                $data=array(
                    'status'=>   'error',
                    'message'=> 'Usuario Inactivo'
                ); 
            }
        }else{
            $data=array(
                'status'=>   'error',
                'message'=> 'login Incorrecto'
            );
        }
        return $data;    
    }

/**
 * Función que verifica si el token enviado por un user es veridico,
 * Al momento de hacer alguna acción se llama.
 * @param jwt el token a verificar
 * @param Si se le envia el parametro true devuelve el usuario.
 * @return  true si es veridico el token, o el objeto con los datos del usuario del token. 
 */
    public function checkToken($jwt, $getIdentity=false){
        $auth=false; 
        // $decoded='';
        try{
            $jwt=str_replace('Bearer ','',$jwt);
            //Decodifca el token recibido del cliente, con la llave y el HS256
            $decoded=JWT::decode($jwt,$this->key,['HS256']);     
        }catch(\UnexpectedValueException $e){
            $auth=false;
        }catch(\DomainException $e){
            $auth=false;
        }
        if(!empty($decoded)&& is_object($decoded)&& isset($decoded->sub)){
            $auth=true;
        }
        //retorna el objeto con los datos del usuario
        if($getIdentity){
            return $decoded;
        }
        return $auth;
    }


    /**
     * Refresca el Token 
     */
    public function refreshToken($jwt){
    
        try{
            $jwt=str_replace('Bearer ','',$jwt);
            //pasa de token a user
            $user=JWT::decode($jwt,$this->key,['HS256']);  
            $user=User::find($user->sub);
            $token=$this->singup($user->email,$user->password);
            return $token;
        }catch(\UnexpectedValueException $e){
            return "error convirtiendo";
        }catch(\DomainException $e){
            return "error convirtiendo";
        }
        return false;
        
    }
}           