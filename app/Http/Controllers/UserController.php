<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use App\User;
use Firebase\JWT\JWT;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\File;

//use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
class UserController extends Controller
{
    /**
     * Agregando el Middleware de Autenticación
     */
    public function __construct(){
        $this->middleware('api.auth',
        ['except'=>['login','register']]);
    }

    /**
     * Registra un usuario que envía los parametro por Json formato(x-www-form-urlencoded)
     * @return el json con la respuesta y el codigo.
     */
    public function register(Request $request)
    {       
       //Obtener los datos que envia el usuario por POST del request
       $params_array = $request->all();
       
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
                    'code' => 400,
                    'message' => 'No se ha podido registrar el usuario',
                    'errors' => $validate->errors()
                );
            } else {//Si no hay fallos de validación
                //Cifrando la contraseña
                $pwd=hash('sha256',$params_array['password']);
    
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
                    'user'=> $user
                ); 
            } 
        }else{
            $data = array(
                'status' => 'failed',
                'code' => 400,
                'message' => ' datos enviados incorrectos'
            );
        }
        return response()->json($data, $data['code']);
    }

    public function login(Request $request){
        //clase helper creada con metodo de singup.
        $jwtAuth=new \JwtAuth();
        $params = $request->all();
        //Validar esos datos
        $validate = \Validator::make($params, [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        //Si hay error en los datos
        if ($validate->fails()) {
            $singup = array(
                'status' => 'error','code' => 401,
                'message' => 'No se ha podido Loguear el usuario',
                'errors' => $validate->errors()
            );
            return  response()->json($singup,401);
        }
        else{
            $pwd=hash('sha256',$params['password']);
                $singup=$jwtAuth->singup($params['email'],$pwd);
                return response()->json($singup,200); 
        } 
    }



    /**
     * Subir LA imagen a disco,para uego poder accederla con la ruta que está en la bd.
     */
    public function uploadImage(Request $request){
        //la libreria fileuploader recoge la imagen es en ese campo
        $image=$request->file('file0');
        
        //Validar que lo que se suba sea imagen
        $validate=\Validator::make($request->all(),[
            'file0'=>'required|image|mimes:jpg,jpeg,png,gif'
        ]);
        //Guardar la imagen  
        if(!$image||$validate->fails()){
            $data = array(
                'status' => 'error',
                'code' => '400',
                'message' => 'No hay imagen o Formato Incorrecto',
                'imagen'=>$image
            );
        }else{
            $image_name=time().$image->getClientOriginalName();
            // se crea dicha carpeta /storage/app/avatars
            Storage::disk('avatars')->put($image_name,\File::get($image));   
                $data = array(
                    'status' => 'succes',
                    'code' => '200',
                    'image' => $image_name
                );
        }
        return response()->json($data,$data['code']);
    }

       /**
     * Funcion para obtener la imagen de un Usuario del disco
     */
    public function getImage($filename){
        $isset=Storage::disk('avatars')->exists($filename);
        // $exists = Storage::disk('avatars')->exists($filename);
        if(!empty($isset))
        {
            $file=Storage::disk('avatars')->get($filename);
            return new Response($file,200);
        }else{
            $data = array(
                'status' => 'error',
                'code' => '404',
                'message' => 'imagen no existe en disco'
            );  
            return response()->json($data,$data['code']);  
        }   
    }

    /**
     * Actualiza datos de un usuario previamente loguado por middleware
     */
    public function update(Request $request){
        $params_array=$request->all();
        //Si está autorizado el usuario por token
        if( !empty($params_array)){            
            $user=$this->getUserByToken($request);

            $validate = \Validator::make($params_array, [
                'name' => 'required|alpha',
                'surname' => 'required',
                //unique para que no deje registrar usuarios con el mismo email
                'email'=>'required',Rule::unique('users')->ignore($user->sub),
                'password' => 'required'
            ]);   
            if(!$validate->fails()){
                $params_array['password']=hash('sha256',$params_array['password']);
                //quitar los campos que no quiero actualizar
                unset($params_array['id']);
                unset($params_array['role']);
                unset($params_array['create_at']); 
                unset($params_array['remember_token']);        
                //Actualizar el User en la BD
                $user_update=User::where('id',$user->sub)->update($params_array);
                //retornar el resultado de la actualiazción
                $params_array['password']='******';
                $data=array(
                    'code'=>200,
                    'status'=>'success',
                    'user'=>$user,
                    'changes'=>$params_array
                );
            } else{
                $data=array(
                    'code'=>400,
                    'status'=>'error',
                    'errors'=>$validate->errors()
                );
            } 
        }
        else{
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Datos Vacios',
                'errors'=>$validate->errors()
            );  
        }
        return response()->json($data,$data['code']);
    }

    /**
     * Obtiene el user que está logueado mediante su token.
     */
    public function getUserByToken(Request $request){
        //Obtiene el usuario actualente logueado.
        $token=$request->header('Authorization');
        $jwtAuth= new \JwtAuth();
        $user=$jwtAuth->checkToken($token,true);
        return $user;
    }

    /**
     *Obtiene un usuario de la bd mediante su email y pwd.
     */
    public function getUserByCredentials($email,$pwd){
        $user=User::where([
            ['email', '=', $email],
            ['password', '=', $pwd],
        ])->get()->first();
        if(!empty($user)&& is_object($user)&&$user!=null){
            return $user;
        }
        return null;
    }
}
