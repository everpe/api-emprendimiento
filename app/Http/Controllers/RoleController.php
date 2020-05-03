<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use App\User;
use Firebase\JWT\JWT;

class RoleController extends Controller
{
    /**
     * Permite agregar un role a un user- implica sus permisos. 
     */
    public function addRole($id_user,$name_role){
        $user=User::find($id_user);
        if(!empty($user)&&is_object($user)){
            $user->assignRole($name_role);
            return response()->json([
                'code'=>200,
                'message'=>'se asignó el role Corrctamente',
                'nameUser'=>$user,
                'role'=>$name_role
                // 'role'=>$name_role
            ],200);
        }
        return response()->json([
            'code'=>400,
            'message'=>'No se pudó asignar el Role'
        ],400); 
    }
    public function deleteRole($id_user,$name_role){
        $user=User::find($id_user);
        if(!empty($user)&&is_object($user)){
            $user->removeRole($name_role);
            return response()->json([
                'code'=>200,
                'message'=>'se eliminó el role Corrctamente',
                'nameUser'=>$user->name,
                'role'=>$name_role
                // 'role'=>$name_role
            ],200);
        }else{
            return response()->json([
                'code'=>400,
                'message'=>'No se pudó eliminar el role'
            ],400);         
        }
    }
    public function getRolesByUser($id_user){
        $user=User::find($id_user);
        if(!empty($user)&&is_object($user)){
            return $user->roles;
        }
        else{
            return response()->json([
                'code'=>400,
                'message'=>'No se encontró el user'
            ],400); 
        }
    }
}
