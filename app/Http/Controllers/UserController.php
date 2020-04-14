<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use App\User;
use Firebase\JWT\JWT;

class UserController extends Controller
{
    /**
     * Registra un usuario que envía los parametro por Json formato(x-www-form-urlencoded)
     * @return el json con la respuesta y el codigo.
     */
    public function register(Request $request)
    {       
       //Obtener los datos que envia el usuario por POST del request
       $json=$request->input('json',null);
    
       $params= json_decode($json);
       $params_array= json_decode($json,true);
       
       //Si el user si envia datos
       if(!empty($params_array))
       {
           //quita los espacios del array
           $params_array = array_map('trim', $params_array); 
            //Validación de los datos  
            $validate = \Validator::make($params_array, [
                        'name' => 'required|alpha',
                        'surname' => 'required',
                        'email' => 'required|email|unique:users',
                        'password' => 'required'
            ]);
            //si enecuentran problemas al validar
            if ($validate->fails()) {
                //crea respuesta de error
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'No se ha podido registrar el usuario',
                    'errors' => $validate->errors()
                );
            } else {//Si no hay fallos de validación
                //Cifrando la contraseña
                $pwd=hash('sha256',$params_array['password']);
                // $pwd=hash('sha256',$params->password);
        
                $user=new User();
                $user->name=$params_array['name'];
                $user->surname=$params_array['surname'];
                $user->email=$params_array['email'];
                $user->password=$pwd;
                $user->role='ROLE_USER';
                $user->save();
                $data = array(
                    'status' => 'succes',
                    'code' => 200,
                    'message' => ' usuario Creado correctamente',
                    // 'sinCifrar'=>$params->password,
                    // 'cifrada'=>$user->password,
                    'user'=> $user
                ); 
            } 
        }else{
            $data = array(
                'status' => 'failed',
                'code' => 404,
                'message' => ' datos enviados incorrectos'
            );
        }
        return response()->json($data, $data['code']);
    }

    public function login(Request $request){
        //clase helper creada con metodo de singup.
        $jwtAuth=new \JwtAuth();
        //Recibir los datos del user por POST del Json
        $json=$request->input('json',null);
        $params=json_decode($json);
        $params_array=json_decode($json,true);
        //Validar esos datos
        $validate = \Validator::make($params_array, [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        //Si hay error en los datos
        if ($validate->fails()) {
            $singup = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'No se ha podido Loguear el usuario',
                'errors' => $validate->errors()
            );
            // return  response()->json($singup);
        }
        else{

            $pwd=hash('sha256',$params->password);
            //si no hay parametro get token,es null por defecto y envía el token
            $singup=$jwtAuth->singup($params->email,$pwd);
            //Si si getToken entonces devuelve los datos del user codificados
            if(!empty($params->getToken)){
                $singup=$jwtAuth->singup($params->email,$pwd,true);        
            }
        }
        return response()->json($singup,200);    
    
    }

    public function update(Request $request){
        $token=$request->header('Authorization');//Authorization desde el frontend con el token
        $jwtAuth= new \JwtAuth();
        $checkToken=$jwtAuth->checkToken($token);//verifica el token en Jwt
        if($checkToken){
            return "Token Veridico";
        }else{
            return "token Malo, No puedes";
        }
    }
}
