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
        if(count($test->activities)<4)
        {
            if(!empty($params_array)){
                $validate=\Validator::make($params_array,[
                    'sectionA'=>'required|numeric|min:0|max:10',
                    'sectionB'=>'required|numeric|min:0|max:10',
                    'sectionC'=>'required|numeric|min:0|max:10',
                    'sectionD'=>'required|numeric|min:0|max:10'
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
    
    /**
     * Agrega la actividad Hemisphere a un test, con solo dos secciones A y D.
     */
    public function addHemisphereHerrmann(Request $request,$test_id){
        $params_array=$request->all();
        //validar que ese test no tenga ya todas las actividades agregadas
        $test= Test::find($test_id);
        // if(count($test->activities)<4)
        // {
            if(is_object($test)&&count($test->activities)<4){
                $hemisphere=$this->setCrossSum($request);
                $activity= new Activity();
                $activity->name="Hemisferio Cerebral";
                $activity->test_id=$test_id;
                $activity->save();
                $activity->sections()->attach( 1,['score'=>$hemisphere['a']]);//$params->sectionA]);
                $activity->sections()->attach( 4,['score'=>$hemisphere['d']]);//$params->sectionB]);
                return response()->json([
                    'status'=>'success',
                    'messagge'=>'Se agregó la actividad Hemisferio Correctamente',
                    'id_Activiy_Hemisphere'=>$activity->id
                ],200);
            }
            return response()->json([
                'status'=>'error',
                'messagge'=>'No se pudó agregar La Actividad'
            ],400);
    
    }

    public function setCrossSum(Request $request){
        $one=$request->one;
        $two=$request->two;
        $three=$request->three;
        $four=$request->four;
        $five=$request->five;
        $six=$request->six;
    
        $seven=$request->seven;
        $eight=$request->eight;
        $nine=$request->nine;
        $ten=$request->ten;
        $eleven=$request->eleven;
        $twelve=$request->twelve;
       
        $A1_6=0;
        $A7_12=0;
        $D1_6=0;
        $D7_12=0;
        
        if($one=='A'){
            $A1_6+=1;
        }else{
            $D1_6+=1;
        }
        if($two=='A'){
            $A1_6+=1;
        }else{
            $D1_6+=1;
        }
        if($three=='A'){
            $A1_6+=1;
        }else{
            $D1_6+=1;
        }
        if($four=='A'){
            $A1_6+=1;
        }else{
            $D1_6+=1;
        }
        if($five=='A'){
            $A1_6+=1;
        }else{
            $D1_6+=1;
        }
        if($six=='A'){
            $A1_6+=1;
        }else{
            $D1_6+=1;
        }
        ////////////
        if($seven=='A'){
            $A7_12+=1;
        }else{
            $D7_12+=1;
        }
        if($eight=='A'){
            $A7_12+=1;
        }else{
            $D7_12+=1;
        }
        if($nine=='A'){
            $A7_12+=1;
        }else{
            $D7_12+=1;
        }
        if($ten=='A'){
            $A7_12+=1;
        }else{
            $D7_12+=1;
        }
        if($eleven=='A'){
            $A7_12+=1;
        }else{
            $D7_12+=1;
        }
        if($twelve=='A'){
            $A7_12+=1;
        }else{
            $D7_12+=1;
        }
       return $array=$this->setSumA_D($A1_6,$A7_12,$D1_6,$D7_12);

    }

    /**
     * Hace la suma cruzada luego de tener el total por secciones 1-6,7-12.
     */
    public function setSumA_D($A1,$A2,$D1,$D2){
        $a=$A1+$D2;//No
        $d=$D1+$A2;//Si
       
        $cross=array('a'=>$a,
            'd'=>$d
        );
        return $cross;
    }

}
