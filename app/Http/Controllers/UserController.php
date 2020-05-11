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

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Agregando el Middleware de Autenticación
     */
    public function __construct(){
        $this->middleware('api.auth',
        ['except'=>['login','register','getImage']]);
    }

    /**
     * Obtiene todos los usuarios que están en el sistema,
     * Solo el role admin puede listar todos los users.
     */
    public function index(Request $request){
        $userLogged=$this->getUserByToken($request);
        $userLogged=User::find($userLogged->sub);
       //Estado del usuario que queremos actualizar
  
        if($userLogged->can('list  all tests')){
            //lista todos los usuarios excepto el actual.
            $users=User::all()->where('id', '<>', $userLogged->id)->load('roles');
            return response()->json($users,200);
        }else{
            return response()->json([
                'code'=>401,'status'=>'error',
                'message'=>'No Autorizado para listar todos los tests'
            ],401);
        }
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
                $user->description = "Description Empty";
                $user->save();
                $user->assignRole('student');
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
                $user = $jwtAuth->checkToken($singup, true);
                return response()->json(['token' => $singup, 'user' => $user],200); 
        } 
    }



    /**
     * Subir LA imagen a disco,para luego poder accederla con la ruta que está en la bd.
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
    public function getImage($filename)
    {
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
            // $usAux=$this->getUserByCredentials($user->email,$user->password);
            $validate = \Validator::make($params_array, [
                'name' => 'required|alpha',
                'surname' => 'required',
                //unique para que no deje registrar usuarios con el mismo email
                'email' => [
                    'required',
                    Rule::unique('users')->ignore($user->sub)
                ],
                // 'email'          => 'required|unique:users,email,'.$user->sub,
                // 'password' => 'required'
            ]);   
            if(!$validate->fails()){
                //quitar los campos que no quiero actualizar
                unset($params_array['id']);
                unset($params_array['role']);
                unset($params_array['password']);
                unset($params_array['create_at']); 
                unset($params_array['remember_token']);        
                //Actualizar el User en la BD
                $user_update=User::where('id',$user->sub)->update($params_array);
                //retornar el resultado de la actualiazción
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
        } else{
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
     * Obtiene el user que está logueado mediante su token(sub).
     * @return el obejct user, no responde
     */
    public function getUserByToken(Request $request){
        //Obtiene el usuario actualente logueado.
        $token=$request->header('Authorization');
        $jwtAuth= new \JwtAuth();
        $user=$jwtAuth->checkToken($token,true);
        return $user;
    }
    /**
     * Obtiene el usuario por Token pero responde con Json
     */
    public function getUser(Request $request){
        //Obtiene el usuario actualente logueado.
        $token=$request->header('Authorization');
        $jwtAuth= new \JwtAuth();
        $user=$jwtAuth->checkToken($token,true);
        // $user=User::find($user->sub);
        return response()->json([
            'code'=>200,
            'message'=>'success',
            'user'=>$user
        ],200);
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

    /**
     * Obtiene el usuario que stá logueado para validar sus perimisos.
     * Obtiene el usuario a quien se le desea cambiar su estado, si estaba en 1 pasa a 0,
     * si estab en 0 pasa a 1.
     */
    public function changeStatus(Request $request,$id_user){
        //usuario Logueado
        $userLogged=$this->getUserByToken($request);
        $userLogged=User::find($userLogged->sub);
        //Usuario al que se le  va a actualizar state
        $userEdit=User::find($id_user);
       //Estado del usuario que queremos actualizar
        $status=$userEdit->state;
        if(!empty($userEdit)&&is_object($userEdit)){
            if($userLogged->can('edit status user')){
                if($status==0){
                    $userEdit=User::where('id',$id_user)->update(['state' => 1]);
                    return response()->json([
                        'code'=>200,'status'=>'success',
                        'message'=>'Se cambió el estado correctamente'
                        // 'new_status'=> $userEdit->state
                    ],200);
                }else{
                    $userEdit=User::where('id',$id_user)->update(['state' => 0]);
                    return response()->json([
                        'code'=>200,'status'=>'success',
                        'message'=>'Se cambió el estado correctamente'
                        // 'new_status'=> $userEdit->state
                    ],200);
                }
                
                
            }else{
                return response()->json([
                    'code'=>401,'status'=>'error',
                    'message'=>'No Autorizado para cambiar estados'
                ],401);
            }
          
        }
        return response()->json([
            'code'=>400,'status'=>'error',
            'messagge'=>"El usuario que desea actualizar no existe"
        ],400);
    }

    /**
     * Envía el token viejo a Jwt para que se lo renueve mediante singup.
     */
    public function refreshToken(Request $request){
        $token=$request->header('Authorization');
        $jwtAuth= new \JwtAuth();
        $jwt=$jwtAuth->refreshToken($token);
        $user = $jwtAuth->checkToken($jwt, true);
        return response()->json(['token' => $jwt, 'user' => $user],200);
    } 



}
