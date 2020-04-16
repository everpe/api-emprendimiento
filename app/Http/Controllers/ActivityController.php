<?php

namespace App\Http\Controllers;

use App\Activity;
use App\Test;
use Illuminate\Http\Request;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\File;
use Firebase\JWT\JWT;
use App\Helpers\JwtAuth;

class ActivityController extends Controller
{

    /**
     * Crea una actividad con el puntaje de cada sección que viene en el request,
     *  y agrega esa actividad a un test ya creado. 
     */
    public function addActivityHerrmann($id_herrmann,Request $request){
        $json=$request->input('json',null);
        $params= json_decode($json);
        $json=$request->input('json',null);
        $params_array=json_decode($json,true);


        //validar que ese test no tenga ya las actividades agregadas
        $test= Test::find($id_herrmann);
        if(count($test->activities)<3)
        {
            if(!empty($params_array)){
                $validate=\Validator::make($params_array,[
                    'seccionA'=>'required|numeric|min:0|max:9',
                    'seccionB'=>'required|numeric|min:0|max:9',
                    'seccionC'=>'required|numeric|min:0|max:9',
                    'seccionD'=>'required|numeric|min:0|max:9'
                ]);
                if(!$validate->fails()){
                    $activity= new Activity();
                    $activity->name="Seleccionar Palabras";
                    $activity->test_id=$id_herrmann;
                    $activity->save();
                    $activity->sections()->attach( 1,['score'=>$params->seccionA]);
                    $activity->sections()->attach( 2,['score'=>$params->seccionB]);
                    $activity->sections()->attach( 3,['score'=>$params->seccionC]);
                    $activity->sections()->attach( 4,['score'=>$params->seccionD]);
                    $data=[
                        'code'=>200,
                        'status'=>'success',
                        'messagge'=>"se creó una Actividad para el test",];
                }else{
                    $data=[
                        'code'=>400,
                        'status'=>'error',
                        'messagge'=>"Score de Actividades Incorrectos",
                        'errors'=>$validate->fails()];
                }
            }else{
                $data=[
                    'code'=>400,
                    'status'=>'error',
                    'messagge'=>"Valores Vacios",];
            }
        }else{
            $data=[
                'code'=>400,
                'status'=>'error',
                'messagge'=>"Ese Test Ya tiene 3 Actividades",];
        }
        return response()->json($data,$data['code']);
    }
}
