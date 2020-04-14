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
    public function singup($email,$password,$getToken=null){
        //Busca un usuario con ese email y contraseña
        $user=User::where([
            'email'=>$email,
            'password'=>$password
        ])->first();
    
        //variable de autenticación
        $singup=false;
  
        //Validar si son correctas su email y password
        if(is_object($user)){
            $singup=true;  
        }
        //Crear el token con los datos del usuario identificado
        if($singup){
            $token=array(
                'sub'=>   $user->id,
                'email'=> $user->email,
                'name'=>  $user->name,
                'surname'=> $user->surname,
                'iat'=>    time(),//creacion del token
                'exp'=>    time()+(60*60)//tiempo de expiracion del token
            );
            
            //HS256 es el algoritmo de cifrado,crea el token
            $jwt=JWT::encode($token,$this->key,'HS256');
            //Saca los datos del user del token
            $decoded=JWT::decode($jwt,$this->key,['HS256']);
            
            if(is_null($getToken)){
                $data=$jwt;        
            }else{
                $data=$decoded;
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

        try{
            //quita las collias que pueeda traer extra el token
            $jwt=str_replace('"','',$jwt);
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

}           