<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use App\User;
class UserController extends Controller
{
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
                    'code' => '404',
                    'message' => 'No se ha podido registrar el usuario',
                    'errors' => $validate->errors()
                );
            } else {//Si no hay fallos de validación
                //Cifrando la contraseña
                // $pwd=hash('sha256',$params_array['password']);
                $pwd=hash('sha256',$params->password);
            
                
                $user=new User();
                $user->name=$params_array['name'];
                $user->surname=$params_array['surname'];
                $user->email=$params_array['email'];
                $user->password=$pwd;
                $user->role='ROLE_USER';
                $user->save();
                $data = array(
                    'status' => 'succes',
                    'code' => '200',
                    'message' => ' usuario Creado correctamente',
                    // 'sinCifrar'=>$params->password,
                    // 'cifrada'=>$user->password,
                    'user'=> $user
                ); 
            } 
        }else{
            $data = array(
                'status' => 'failed',
                'code' => '200',
                'message' => ' datos enviados incorrectos'
            );
        }
        return response()->json($data, $data['code']);
    }

}
