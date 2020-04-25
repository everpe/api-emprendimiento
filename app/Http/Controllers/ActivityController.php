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

    public function __construct(){
        $this->middleware('api.auth');
    }
    /**
     * Crea una actividad con el puntaje de cada sección que viene en el request,
     *  y agrega esa actividad a un test ya creado. 
     */
    public function addActivityHerrmann($id_herrmann,Request $request){
        $params_array=$request->all();

        //validar que ese test no tenga ya todas las actividades agregadas
        $test= Test::find($id_herrmann);
        if(count($test->activities)<3)
        {
            if(!empty($params_array)){
                $validate=\Validator::make($params_array,[
                    'sectionA'=>'required|numeric|min:0|max:9',
                    'sectionB'=>'required|numeric|min:0|max:9',
                    'sectionC'=>'required|numeric|min:0|max:9',
                    'sectionD'=>'required|numeric|min:0|max:9'
                ]);
                if(!$validate->fails()){
                    $activity= new Activity();
                    $activity->name="Seleccionar Palabras";
                    $activity->test_id=$id_herrmann;
                    $activity->save();
                    $activity->sections()->attach( 1,['score'=>$params_array['sectionA']]);//$params->sectionA]);
                    $activity->sections()->attach( 2,['score'=>$params_array['sectionB']]);//$params->sectionB]);
                    $activity->sections()->attach( 3,['score'=>$params_array['sectionC']]);//$params->sectionC]);
                    $activity->sections()->attach( 4,['score'=>$params_array['sectionD']]);//$params->sectionD]);
                    $data=[
                        'code'=>200,
                        'status'=>'success',
                        'messagge'=>"se creó una Actividad para el test",];
                }else{
                    $data=[
                        'code'=>400,
                        'status'=>'error',
                        'messagge'=>"Score de Actividades Incorrectos",
                        'errors'=>$validate->errors()];
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
